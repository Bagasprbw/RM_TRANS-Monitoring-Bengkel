<?php

namespace App\Http\Controllers\Api\KomponenArmada;

use App\Http\Controllers\Controller;
use App\Models\DetailKomponenArmada;
use App\Models\KomponenArmada;
use App\Models\MonitoringArmadaAktif;
use App\Models\RiwayatPerawatanKomponen;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @group Komponen Armada
 *
 * APIs for managing components of monitored vehicles.
 */
class KomponenArmadaController extends Controller
{
    /**
     * List all komponen for a monitoring
     *
     * Get all components for a specific monitoring record with health calculation.
     *
     * @urlParam monitoringId int required The monitoring ID.
     */
    public function index($monitoringId)
    {
        $components = KomponenArmada::with(['kategori', 'detail' => function ($q) {
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
            'data' => $data,
        ]);
    }

    /**
     * Add component to monitoring
     *
     * @urlParam monitoringId int required The monitoring ID.
     *
     * @bodyParam kategori_komponen_id int required The category ID.
     * @bodyParam nama_komponen string required Component name (max 100 chars).
     * @bodyParam tipe_pelacakan string required Tracking type. Options: km, date, days.
     * @bodyParam target_km numeric Required if tipe_pelacakan is 'km'.
     * @bodyParam target_tanggal date Required if tipe_pelacakan is 'date'.
     * @bodyParam target_hari int Required if tipe_pelacakan is 'days'.
     * @bodyParam has_identity boolean Optional Whether component has identity detail.
     * @bodyParam detail array Optional Identity detail data.
     *
     * @response 201 {
     *   "status": "success",
     *   "data": {...}
     * }
     */
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
            'detail' => 'nullable|array',
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
                    'komponen_armada_id' => $komponen->id,
                ]));
            }

            DB::commit();

            return response()->json(['status' => 'success', 'data' => $komponen], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reset component
     *
     * Reset component tracking baseline after maintenance.
     *
     * @urlParam id int required The component ID.
     *
     * @bodyParam target_km numeric Optional Override target KM after reset.
     * @bodyParam target_tanggal date Optional Override target date after reset.
     * @bodyParam jumlah_liter numeric Optional Fluid volume (liters).
     * @bodyParam catatan string Optional Maintenance notes.
     * @bodyParam tanggal_pelepasan date Optional Release date. Defaults to today.
     * @bodyParam status_ban_bekas string Optional Used tire status. Options: ORI, VULK PEMBELIAN, VULK JASA.
     * @bodyParam new_detail array Optional New identity detail data.
     */
    public function reset(Request $request, $id)
    {
        $komponen = KomponenArmada::with('monitoring')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jumlah_liter' => 'nullable|numeric',
            'catatan' => 'nullable|string',
            'tanggal_pelepasan' => 'nullable|date',
            'status_ban_bekas' => 'nullable|in:ORI,VULK PEMBELIAN,VULK JASA',
            'new_detail' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $monitoring = $komponen->monitoring;

            $currentDetail = $komponen->detail()->latest('id')->first();

            $tanggal_pelepasan = $request->input('tanggal_pelepasan') ?: Carbon::now()->toDateString();

            // 1. Update Old Detail with Release Date & Status Ban Bekas
            if ($currentDetail) {
                $currentDetail->update([
                    'tanggal_pelepasan' => $tanggal_pelepasan,
                    'status_ban_bekas' => $request->status_ban_bekas ?? null,
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
                'status' => 'active',
            ];

            // If it's date based or days based we want to advance the target_tanggal/baseline correctly
            if ($komponen->tipe_pelacakan === 'date' && $komponen->target_hari > 0) {
                $baseDate = Carbon::parse($tanggal_pelepasan);
                $updateData['target_tanggal'] = $baseDate->addDays($komponen->target_hari)->toDateString();
            }

            // Also allow manual override from request if provided
            if ($request->has('target_km')) {
                $updateData['target_km'] = $request->target_km;
            }
            if ($request->has('target_tanggal')) {
                $updateData['target_tanggal'] = $request->target_tanggal;
            }

            $komponen->update($updateData);

            // 4. Handle Identity Detail change (opsional - tidak harus isi nomor_seri)
            if ($request->has('new_detail') && is_array($request->new_detail)) {
                $detail = $request->new_detail;

                // Cek apakah ada field yang bermakna diisi
                $hasMeaningfulData = ! empty($detail['nomor_seri'])
                    || ! empty($detail['nomor_stamp'])
                    || ! empty($detail['merk_tipe'])
                    || ! empty($detail['pemasok'])
                    || ! empty($detail['ukuran'])
                    || (! empty($detail['harga']) && $detail['harga'] > 0);

                if ($hasMeaningfulData) {
                    $komponen->update(['has_identity' => true]);

                    $detailData = $detail;
                    $detailData['komponen_armada_id'] = $komponen->id;

                    if (empty($detailData['tanggal_pemasangan'])) {
                        $detailData['tanggal_pemasangan'] = Carbon::now()->toDateString();
                    }

                    DetailKomponenArmada::create($detailData);
                }
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Komponen berhasil direset']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('RESET COMPONENT FAILED', [
                'id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mereset: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update component
     *
     * @urlParam id int required The component ID.
     *
     * @bodyParam kategori_komponen_id int required The category ID.
     * @bodyParam nama_komponen string required Component name.
     * @bodyParam tipe_pelacakan string required Tracking type. Options: km, date, days.
     * @bodyParam target_km numeric Required if tipe_pelacakan is 'km'.
     * @bodyParam target_tanggal date Required if tipe_pelacakan is 'date'.
     * @bodyParam target_hari int Required if tipe_pelacakan is 'days'.
     * @bodyParam has_identity boolean Optional Whether component has identity detail.
     * @bodyParam detail array Optional Identity detail data.
     */
    public function update(Request $request, $id)
    {
        $komponen = KomponenArmada::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kategori_komponen_id' => 'required|exists:category_componen,id',
            'nama_komponen' => 'required|string|max:100',
            'tipe_pelacakan' => 'required|in:km,date,days',
            'target_km' => 'required_if:tipe_pelacakan,km|nullable|numeric',
            'target_tanggal' => 'required_if:tipe_pelacakan,date|nullable|date',
            'target_hari' => 'required_if:tipe_pelacakan,days|nullable|numeric',
            'has_identity' => 'boolean',
            'detail' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $komponen->update([
                'kategori_komponen_id' => $request->kategori_komponen_id,
                'nama_komponen' => $request->nama_komponen,
                'tipe_pelacakan' => $request->tipe_pelacakan,
                'target_km' => $request->target_km,
                'target_tanggal' => $request->target_tanggal,
                'target_hari' => $request->target_hari,
                'has_identity' => $request->has_identity ?? false,
            ]);

            // For now, if the identity fields change, just update the currently active detail record if it exists
            if ($request->has_identity && $request->has('detail')) {
                $latestDetail = DetailKomponenArmada::where('komponen_armada_id', $komponen->id)
                    ->latest('id')
                    ->first();

                if ($latestDetail) {
                    $latestDetail->update($request->detail);
                } else {
                    DetailKomponenArmada::create(array_merge($request->detail, [
                        'komponen_armada_id' => $komponen->id,
                    ]));
                }
            } elseif (! $request->has_identity) {
                // If it is toggled off, we don't necessarily delete the history, but it won't be editable here anymore.
            }

            DB::commit();

            return response()->json(['status' => 'success', 'data' => $komponen], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete component
     *
     * @urlParam id int required The component ID.
     *
     * @response {
     *   "status": "success",
     *   "message": "Komponen berhasil dihapus"
     * }
     */
    public function destroy($id)
    {
        $komponen = KomponenArmada::findOrFail($id);
        $komponen->delete();

        return response()->json(['status' => 'success', 'message' => 'Komponen berhasil dihapus']);
    }
}
