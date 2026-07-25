<?php

namespace App\Http\Controllers\Api\Armada;

use App\Http\Controllers\Controller;
use App\Models\MerkArmada;
use Illuminate\Http\Request;

/**
 * @group Merk Armada
 *
 * APIs for managing vehicle brands (Merk Armada).
 */
class MerkArmadaController extends Controller
{
    /**
     * List all merk armada
     *
     * Get all vehicle brands.
     */
    public function index()
    {
        $data = MerkArmada::all();

        return response()->json($data);
    }

    /**
     * Create new merk armada
     *
     * @bodyParam nama_merk string required The brand name (unique).
     *
     * @response 201 {
     *   "message": "Merk armada berhasil ditambahkan",
     *   "data": {...}
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_merk' => 'required|unique:merk_armada,nama_merk',
        ]);

        $merk = MerkArmada::create([
            'nama_merk' => $request->nama_merk,
        ]);

        return response()->json([
            'message' => 'Merk armada berhasil ditambahkan',
            'data' => $merk,
        ], 201);
    }

    /**
     * Get merk armada detail
     *
     * @urlParam id int required The merk armada ID.
     */
    public function show($id)
    {
        $merk = MerkArmada::find($id);

        if (! $merk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($merk);
    }

    /**
     * Update merk armada
     *
     * @urlParam id int required The merk armada ID.
     *
     * @bodyParam nama_merk string required The brand name (unique).
     *
     * @response {
     *   "message": "Merk armada berhasil diupdate",
     *   "data": {...}
     * }
     */
    public function update(Request $request, $id)
    {
        $merk = MerkArmada::find($id);

        if (! $merk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_merk' => 'required|unique:merk_armada,nama_merk,'.$id,
        ]);

        $merk->update([
            'nama_merk' => $request->nama_merk,
        ]);

        return response()->json([
            'message' => 'Merk armada berhasil diupdate',
            'data' => $merk,
        ]);
    }

    /**
     * Delete merk armada
     *
     * @urlParam id int required The merk armada ID.
     *
     * @response {
     *   "message": "Merk armada berhasil dihapus"
     * }
     * @response 422 {
     *   "message": "Merk tidak dapat dihapus karena sudah digunakan oleh ... armada"
     * }
     */
    public function destroy($id)
    {
        $merk = MerkArmada::withCount('armada')->find($id);

        if (! $merk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($merk->armada_count > 0) {
            return response()->json([
                'message' => 'Merk tidak dapat dihapus karena sudah digunakan oleh '.$merk->armada_count.' armada',
            ], 422);
        }

        $merk->delete();

        return response()->json([
            'message' => 'Merk armada berhasil dihapus',
        ]);
    }
}
