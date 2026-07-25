<?php

namespace App\Http\Controllers\Api\Armada;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use Illuminate\Http\Request;

/**
 * @group Armada
 *
 * APIs for managing vehicle fleet (Armada).
 */
class ArmadaController extends Controller
{
    /**
     * List all armada
     *
     * Get all armada with related merk, jenis, and monitoring data.
     */
    public function index()
    {
        $data = Armada::with(['merk', 'jenis', 'monitoring'])->get();

        return response()->json($data);
    }

    /**
     * Create new armada
     *
     * @bodyParam nopol string required The vehicle plate number (unique).
     * @bodyParam merk_armada_id int required The brand/merk ID.
     * @bodyParam jenis_armada_id int required The vehicle type ID.
     *
     * @response 201 {
     *   "message": "Armada berhasil ditambahkan",
     *   "data": {...}
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'nopol' => 'required|unique:armada,nopol',
            'merk_armada_id' => 'required|exists:merk_armada,id',
            'jenis_armada_id' => 'required|exists:jenis_armada,id',
        ]);

        $armada = Armada::create([
            'nopol' => $request->nopol,
            'merk_armada_id' => $request->merk_armada_id,
            'jenis_armada_id' => $request->jenis_armada_id,
        ]);

        $armada->load(['merk', 'jenis']);

        return response()->json([
            'message' => 'Armada berhasil ditambahkan',
            'data' => $armada,
        ], 201);
    }

    /**
     * Get armada detail
     *
     * @urlParam id int required The armada ID.
     *
     * @response {
     *   "id": 1,
     *   "nopol": "...",
     *   "merk": {...},
     *   "jenis": {...}
     * }
     */
    public function show($id)
    {
        $armada = Armada::with(['merk', 'jenis'])->find($id);

        if (! $armada) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($armada);
    }

    /**
     * Update armada
     *
     * @urlParam id int required The armada ID.
     *
     * @bodyParam nopol string required The vehicle plate number.
     * @bodyParam merk_armada_id int required The brand/merk ID.
     * @bodyParam jenis_armada_id int required The vehicle type ID.
     *
     * @response {
     *   "message": "Armada berhasil diupdate",
     *   "data": {...}
     * }
     */
    public function update(Request $request, $id)
    {
        $armada = Armada::find($id);

        if (! $armada) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'nopol' => 'required|unique:armada,nopol,'.$id,
            'merk_armada_id' => 'required|exists:merk_armada,id',
            'jenis_armada_id' => 'required|exists:jenis_armada,id',
        ]);

        $armada->update([
            'nopol' => $request->nopol,
            'merk_armada_id' => $request->merk_armada_id,
            'jenis_armada_id' => $request->jenis_armada_id,
        ]);

        $armada->load(['merk', 'jenis']);

        return response()->json([
            'message' => 'Armada berhasil diupdate',
            'data' => $armada,
        ]);
    }

    /**
     * Delete armada
     *
     * @urlParam id int required The armada ID.
     *
     * @response {
     *   "message": "Armada berhasil dihapus"
     * }
     */
    public function destroy($id)
    {
        $armada = Armada::find($id);

        if (! $armada) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $armada->delete();

        return response()->json([
            'message' => 'Armada berhasil dihapus',
        ]);
    }
}
