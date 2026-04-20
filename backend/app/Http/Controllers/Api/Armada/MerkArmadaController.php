<?php

namespace App\Http\Controllers\Api\Armada;

use App\Http\Controllers\Controller;
use App\Models\MerkArmada;
use Illuminate\Http\Request;

class MerkArmadaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MerkArmada::all();
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_merk' => 'required|unique:merk_armada,nama_merk'
        ]);

        $merk = MerkArmada::create([
            'nama_merk' => $request->nama_merk
        ]);

        return response()->json([
            'message' => 'Merk armada berhasil ditambahkan',
            'data' => $merk
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $merk = MerkArmada::find($id);

        if (!$merk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($merk);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $merk = MerkArmada::find($id);

        if (!$merk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_merk' => 'required|unique:merk_armada,nama_merk,' . $id
        ]);

        $merk->update([
            'nama_merk' => $request->nama_merk
        ]);

        return response()->json([
            'message' => 'Merk armada berhasil diupdate',
            'data' => $merk
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $merk = MerkArmada::withCount('armada')->find($id);

        if (!$merk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($merk->armada_count > 0) {
            return response()->json([
                'message' => 'Merk tidak dapat dihapus karena sudah digunakan oleh ' . $merk->armada_count . ' armada'
            ], 422);
        }

        $merk->delete();

        return response()->json([
            'message' => 'Merk armada berhasil dihapus'
        ]);
    }
}
