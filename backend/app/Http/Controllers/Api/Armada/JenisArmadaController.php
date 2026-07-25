<?php

namespace App\Http\Controllers\Api\Armada;

use App\Http\Controllers\Controller;
use App\Models\JenisArmada;
use Illuminate\Http\Request;

/**
 * @group Jenis Armada
 *
 * APIs for managing vehicle types (Jenis Armada).
 */
class JenisArmadaController extends Controller
{
    /**
     * List all jenis armada
     *
     * Get all vehicle types.
     */
    public function index()
    {
        $data = JenisArmada::all();

        return response()->json($data);
    }

    /**
     * Create new jenis armada
     *
     * @bodyParam nama_jenis string required The vehicle type name (unique).
     *
     * @response 201 {
     *   "message": "Jenis armada berhasil ditambahkan",
     *   "data": {...}
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|unique:jenis_armada,nama_jenis',
        ]);

        $jenis = JenisArmada::create([
            'nama_jenis' => $request->nama_jenis,
        ]);

        return response()->json([
            'message' => 'Jenis armada berhasil ditambahkan',
            'data' => $jenis,
        ], 201);
    }

    /**
     * Get jenis armada detail
     *
     * @urlParam id int required The jenis armada ID.
     */
    public function show($id)
    {
        $jenis = JenisArmada::find($id);

        if (! $jenis) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($jenis);
    }

    /**
     * Update jenis armada
     *
     * @urlParam id int required The jenis armada ID.
     *
     * @bodyParam nama_jenis string required The vehicle type name (unique).
     *
     * @response {
     *   "message": "Jenis armada berhasil diupdate",
     *   "data": {...}
     * }
     */
    public function update(Request $request, $id)
    {
        $jenis = JenisArmada::find($id);

        if (! $jenis) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_jenis' => 'required|unique:jenis_armada,nama_jenis,'.$id,
        ]);

        $jenis->update([
            'nama_jenis' => $request->nama_jenis,
        ]);

        return response()->json([
            'message' => 'Jenis armada berhasil diupdate',
            'data' => $jenis,
        ]);
    }

    /**
     * Delete jenis armada
     *
     * @urlParam id int required The jenis armada ID.
     *
     * @response {
     *   "message": "Jenis armada berhasil dihapus"
     * }
     * @response 422 {
     *   "message": "Jenis tidak dapat dihapus karena sudah digunakan oleh ... armada"
     * }
     */
    public function destroy($id)
    {
        $jenis = JenisArmada::withCount('armada')->find($id);

        if (! $jenis) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($jenis->armada_count > 0) {
            return response()->json([
                'message' => 'Jenis tidak dapat dihapus karena sudah digunakan oleh '.$jenis->armada_count.' armada',
            ], 422);
        }

        $jenis->delete();

        return response()->json([
            'message' => 'Jenis armada berhasil dihapus',
        ]);
    }
}
