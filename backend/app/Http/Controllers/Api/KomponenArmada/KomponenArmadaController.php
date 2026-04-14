<?php

namespace App\Http\Controllers\Api\KomponenArmada;

use App\Http\Controllers\Controller;
use App\Models\KomponenArmada;
use App\Models\DetailKomponenArmada;
use App\Models\MonitoringArmadaAktif;
use App\Models\RiwayatPerawatanKomponen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KomponenArmadaController extends Controller
{
    public function index($monitoringId)
    {
        $components = KomponenArmada::with(['kategori', 'detail' => function($q) {
                $q->orderBy('id', 'desc'); // Assuming higher ID is newer if non-timestamped, or use timestamps if needed
            }])
            ->where('monitoring_armada_id', $monitoringId)
            ->get();

        $monitoring = MonitoringArmadaAktif::find($monitoringId);
        $currentKm = $monitoring ? $monitoring->last_recorded_km : 0;

        $data = $components->map(function ($comp) use ($currentKm) {
            $healthData = $comp->calculateHealth($currentKm);
            
            $compArray = $comp->toArray();
            $compArray['health'] = $healthData['health'];
            $compArray['remaining'] = $healthData['remaining'];

            return $compArray;
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request, $monitoringId)
    {
        $validator = Validator::make($request->all(), [
            'kategori_komponen_id' => 'required|exists:category_componen,id',
            'nama_komponen' => 'required|string|max:100',
            'tipe_pelacakan' => 'required|in:km,date,days',
            'target_km' => 'required_if:tipe_pelacakan,km|nullable|numeric',
            'target_tanggal' => 'required_if:tipe_pelacakan,date|nullable|date',
            'target_hari' => 'required_if:tipe_pelacakan,days|nullable|numeric',
            'has_identity' => 'boolean',
            'detail' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $monitoring = MonitoringArmadaAktif::findOrFail($monitoringId);

            $komponen = KomponenArmada::create([
                'monitoring_armada_id' => $monitoringId,
                'kategori_komponen_id' => $request->kategori_komponen_id,
                'nama_komponen' => $request->nama_komponen,
                'tipe_pelacakan' => $request->tipe_pelacakan,
                'target_km' => $request->target_km,
                'target_tanggal' => $request->target_tanggal,
                'target_hari' => $request->target_hari,
                'km_terakhir_perawatan' => $monitoring->last_recorded_km,
                'tanggal_terakhir_perawatan' => null,
                'status' => 'active',
                'has_identity' => $request->has_identity ?? false,
            ]);

            if ($request->has_identity && $request->has('detail')) {
                DetailKomponenArmada::create(array_merge($request->detail, [
                    'komponen_armada_id' => $komponen->id
                ]));
            }

            DB::commit();
            return response()->json(['status' => 'success', 'data' => $komponen], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function reset(Request $request, $id)
    {
        $komponen = KomponenArmada::with('monitoring')->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'jumlah_liter' => 'nullable|numeric',
            'catatan' => 'nullable|string',
            'new_detail' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $monitoring = $komponen->monitoring;
            
            $currentDetail = $komponen->detail()->latest('id')->first();

            $tanggal_pelepasan = $request->input('tanggal_pelepasan') ?: Carbon::now()->toDateString();

            // 1. Update Old Detail with Release Date
            if ($currentDetail) {
                $currentDetail->update([
                    'tanggal_pelepasan' => $tanggal_pelepasan,
                    'status_ban_bekas' => null 
                ]);
            }

            // 2. Record History
            RiwayatPerawatanKomponen::create([
                'komponen_armada_id' => $komponen->id,
                'detail_komponen_armada_id' => $currentDetail ? $currentDetail->id : null,
                'km_saat_selesai' => $monitoring ? $monitoring->last_recorded_km : 0,
                'tanggal_selesai' => $tanggal_pelepasan,
                'jumlah_liter' => $request->jumlah_liter ?? null,
                'catatan' => $request->catatan,
            ]);

            // 3. Update Komponen Baseline
            $updateData = [
                'km_terakhir_perawatan' => $monitoring ? $monitoring->last_recorded_km : 0,
                'tanggal_terakhir_perawatan' => $tanggal_pelepasan,
                'status' => 'active'
            ];

            // If it's date based or days based we want to advance the target_tanggal/baseline correctly
            if ($komponen->tipe_pelacakan === 'date' && $komponen->target_hari > 0) {
                $baseDate = Carbon::parse($tanggal_pelepasan);
                $updateData['target_tanggal'] = $baseDate->addDays($komponen->target_hari)->toDateString();
            }

            // Also allow manual override from request if provided
            if ($request->has('target_km')) $updateData['target_km'] = $request->target_km;
            if ($request->has('target_tanggal')) $updateData['target_tanggal'] = $request->target_tanggal;

            $komponen->update($updateData);

            // 4. Handle Identity Detail change (soalnya ada no seri baru)
            if ($request->has('new_detail') && !empty($request->new_detail['nomor_seri'])) {
                $komponen->update(['has_identity' => true]);
                
                $detailData = $request->new_detail;
                $detailData['komponen_armada_id'] = $komponen->id;
                
                if (empty($detailData['tanggal_pemasangan'])) {
                    $detailData['tanggal_pemasangan'] = Carbon::now()->toDateString();
                }

                DetailKomponenArmada::create($detailData);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Komponen berhasil direset']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('RESET COMPONENT FAILED', [
                'id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal mereset: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $komponen = KomponenArmada::findOrFail($id);
        $komponen->delete();
        return response()->json(['status' => 'success', 'message' => 'Komponen berhasil dihapus']);
    }
}
