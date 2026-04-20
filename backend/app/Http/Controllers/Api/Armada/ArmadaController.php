<?php

namespace App\Http\Controllers\Api\Armada;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use Illuminate\Http\Request;

class ArmadaController extends Controller
{
    // GET semua data
    public function index()
    {
        $data = Armada::with(['merk', 'jenis', 'monitoring'])->get();

        return response()->json($data);
    }

    // POST create
    public function store(Request $request)
    {
        $request->validate([
            'nopol' => 'required|unique:armada,nopol',
            'merk_armada_id' => 'required|exists:merk_armada,id',
            'jenis_armada_id' => 'required|exists:jenis_armada,id'
        ]);

        $armada = Armada::create([
            'nopol' => $request->nopol,
            'merk_armada_id' => $request->merk_armada_id,
            'jenis_armada_id' => $request->jenis_armada_id
        ]);

        $armada->load(['merk', 'jenis']);

        return response()->json([
            'message' => 'Armada berhasil ditambahkan',
            'data' => $armada
        ], 201);
    }

    // GET detail
    public function show($id)
    {
        $armada = Armada::with(['merk', 'jenis'])->find($id);

        if (!$armada) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($armada);
    }

    // PUT update
    public function update(Request $request, $id)
    {
        $armada = Armada::find($id);

        if (!$armada) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'nopol' => 'required|unique:armada,nopol,' . $id,
            'merk_armada_id' => 'required|exists:merk_armada,id',
            'jenis_armada_id' => 'required|exists:jenis_armada,id'
        ]);

        $armada->update([
            'nopol' => $request->nopol,
            'merk_armada_id' => $request->merk_armada_id,
            'jenis_armada_id' => $request->jenis_armada_id
        ]);

        $armada->load(['merk', 'jenis']);

        return response()->json([
            'message' => 'Armada berhasil diupdate',
            'data' => $armada
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $armada = Armada::find($id);

        if (!$armada) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $armada->delete();

        return response()->json([
            'message' => 'Armada berhasil dihapus'
        ]);
    }
}
