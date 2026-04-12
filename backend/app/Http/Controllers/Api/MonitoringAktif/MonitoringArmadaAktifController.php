<?php

namespace App\Http\Controllers\Api\MonitoringAktif;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\MonitoringArmadaAktif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MonitoringArmadaAktifController extends Controller
{
    public function index()
    {
        $monitoring = MonitoringArmadaAktif::with(['armada.jenis'])->get();
        return response()->json([
            'status' => 'success',
            'data'   => $monitoring
        ]);
    }

    public function reminders()
    {
        $monitoring = MonitoringArmadaAktif::with(['armada.jenis', 'komponen.kategori'])
            ->where('status', 'aktif')
            ->get();

        $reminders = [];

        foreach ($monitoring as $mon) {
            foreach ($mon->komponen as $komp) {
                $healthData = $komp->calculateHealth($mon->last_recorded_km);
                
                if ($healthData['health'] <= 15) {
                    $reminders[] = [
                        'id' => $komp->id,
                        'monitoring_id' => $mon->id,
                        'nopol' => $mon->armada->nopol ?? '-',
                        'merk' => $mon->armada->jenis->nama_jenis ?? '-',
                        'nama_komponen' => $komp->nama_komponen,
                        'kategori' => $komp->kategori->nama_kategori ?? '-',
                        'health' => $healthData['health'],
                        'status' => $healthData['remaining'],
                        'badgeClass' => $healthData['health'] <= 5 ? 'badge-red' : ($healthData['health'] <= 10 ? 'badge-orange' : 'badge-yellow'),
                        'dotClass' => $healthData['health'] <= 5 ? 'dot-red' : ($healthData['health'] <= 10 ? 'dot-orange' : 'dot-yellow')
                    ];
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $reminders
        ]);
    }

    public function availableArmada()
    {
        // Get all armadas that do NOT have active monitoring
        $armadas = Armada::with('jenis')
            ->whereDoesntHave('monitoring', function ($query) {
                $query->where('status', 'aktif');
            })
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $armadas
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'armada_id'        => 'required|exists:armada,id|unique:monitoring_armada_aktif,armada_id,NULL,id,status,aktif',
            'last_recorded_km' => 'required|numeric|min:0',
            'status'           => 'nullable|in:aktif,nonaktif',
            'spedo_status'     => 'nullable|in:HIDUP,MATI',
            'keterangan'       => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $monitoring = MonitoringArmadaAktif::create([
                'armada_id'                => $request->armada_id,
                'last_recorded_km'         => $request->last_recorded_km,
                'status'                   => $request->status ?? 'aktif',
                'spedo_status'             => $request->spedo_status ?? 'HIDUP',
                'tanggal_mulai_monitoring' => Carbon::now()->toDateString(),
                'keterangan'               => $request->keterangan ?? '-'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Monitoring kendaraan berhasil ditambahkan',
                'data'    => $monitoring
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menambahkan monitoring kendaraan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $monitoring = MonitoringArmadaAktif::with(['armada.jenis'])->find($id);
        if (!$monitoring) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Monitoring kendaraan tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data'   => $monitoring
        ]);
    }

    public function update(Request $request, $id)
    {
        $monitoring = MonitoringArmadaAktif::find($id);
        if (!$monitoring) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Monitoring kendaraan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:aktif,nonaktif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $monitoring->status = $request->status;
        $monitoring->save();

        $monitoring->load('armada.jenis');

        return response()->json([
            'status'  => 'success',
            'message' => 'Status monitoring berhasil diupdate',
            'data'    => $monitoring
        ]);
    }

    public function destroy($id)
    {
        $monitoring = MonitoringArmadaAktif::find($id);

        if (!$monitoring) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Monitoring kendaraan tidak ditemukan'
            ], 404);
        }

        try {
            // Bisa hapus langsung di sini, atau ubah status jadi non-aktif. Karena ada method "removeVehicle", kita ganti delete.
            $monitoring->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Monitoring kendaraan berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus monitoring kendaraan: ' . $e->getMessage()
            ], 500);
        }
    }
}
