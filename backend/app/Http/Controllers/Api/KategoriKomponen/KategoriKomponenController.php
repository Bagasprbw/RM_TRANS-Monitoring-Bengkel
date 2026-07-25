<?php

namespace App\Http\Controllers\Api\KategoriKomponen;

use App\Http\Controllers\Controller;
use App\Models\CategoryComponen;
use Illuminate\Http\Request;

/**
 * @group Kategori Komponen
 *
 * APIs for managing component categories.
 */
class KategoriKomponenController extends Controller
{
    /**
     * List all kategori komponen
     *
     * Get all component categories.
     */
    public function index()
    {
        $data = CategoryComponen::get();

        return response()->json([
            'message' => 'Berhasil ambil data',
            'data' => $data,
        ]);
    }

    /**
     * Create new kategori komponen
     *
     * @bodyParam nama_kategori string required Category name (max 100 characters).
     *
     * @response 201 {
     *   "message": "Kategori berhasil dibuat",
     *   "data": {...}
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100',
        ]);

        $data = CategoryComponen::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json([
            'message' => 'Kategori berhasil dibuat',
            'data' => $data,
        ], 201);
    }

    /**
     * Delete kategori komponen
     *
     * @urlParam id int required The category ID.
     *
     * @response {
     *   "message": "Kategori berhasil dihapus"
     * }
     */
    public function destroy($id)
    {
        $data = CategoryComponen::find($id);

        if (! $data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus',
        ]);
    }
}
