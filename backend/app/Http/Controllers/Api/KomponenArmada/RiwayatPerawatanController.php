<?php

namespace App\Http\Controllers\Api\KomponenArmada;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPerawatanKomponen;
use Illuminate\Http\Request;

class RiwayatPerawatanController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatPerawatanKomponen::with([
            'komponen.kategori',
            'komponen.monitoring.armada',
            'detailKomponen'
        ]);

        if ($request->has('category_id') && $request->category_id != '') {
            $query->whereHas('komponen', function($q) use ($request) {
                $q->where('kategori_komponen_id', $request->category_id);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('komponen.monitoring.armada', function($sq) use ($search) {
                    $sq->where('nopol', 'like', "%$search%");
                })->orWhereHas('komponen', function($sq) use ($search) {
                    $sq->where('nama_komponen', 'like', "%$search%");
                })->orWhere('catatan', 'like', "%$search%");
            });
        }

        $history = $query->orderBy('tanggal_selesai', 'desc')
                        ->orderBy('id', 'desc')
                        ->paginate($request->limit ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
