<?php

namespace App\Http\Controllers\Api\KomponenArmada;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPerawatanKomponen;
use Illuminate\Http\Request;

/**
 * @group Riwayat Perawatan
 *
 * APIs for viewing maintenance history records.
 */
class RiwayatPerawatanController extends Controller
{
    /**
     * List maintenance history
     *
     * Get paginated list of maintenance history with optional filters.
     *
     * @queryParam search string Optional Search by NOPOL, component name, or notes.
     * @queryParam category_id int Optional Filter by category ID.
     * @queryParam date_from date Optional Filter start date.
     * @queryParam date_to date Optional Filter end date.
     * @queryParam limit int Optional Results per page. Default 15.
     * @queryParam export string Optional Set to 'true' to export all results without pagination.
     */
    public function index(Request $request)
    {
        $query = RiwayatPerawatanKomponen::with([
            'komponen.kategori',
            'komponen.monitoring.armada.jenis',
            'komponen.monitoring.armada.merk',
            'detailKomponen',
        ]);

        if ($request->has('category_id') && $request->category_id != '') {
            $query->whereHas('komponen', function ($q) use ($request) {
                $q->where('kategori_komponen_id', $request->category_id);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('komponen.monitoring.armada', function ($sq) use ($search) {
                    $sq->where('nopol', 'like', "%$search%");
                })->orWhereHas('komponen', function ($sq) use ($search) {
                    $sq->where('nama_komponen', 'like', "%$search%");
                })->orWhere('catatan', 'like', "%$search%");
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            if ($request->date_from === $request->date_to) {
                $query->whereDate('tanggal_selesai', $request->date_from);
            } else {
                $query->whereDate('tanggal_selesai', '>=', $request->date_from);
                $query->whereDate('tanggal_selesai', '<=', $request->date_to);
            }
        } elseif ($request->filled('date_from')) {
            $query->whereDate('tanggal_selesai', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('tanggal_selesai', '<=', $request->date_to);
        }

        $query->orderBy('tanggal_selesai', 'desc')
            ->orderBy('id', 'desc');

        if ($request->has('export') && $request->export == 'true') {
            $history = $query->get();
        } else {
            $history = $query->paginate($request->limit ?? 15);
        }

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }
}
