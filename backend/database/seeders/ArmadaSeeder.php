<?php

namespace Database\Seeders;

use App\Models\Armada;
use App\Models\JenisArmada;
use App\Models\MerkArmada;
use Illuminate\Database\Seeder;

class ArmadaSeeder extends Seeder
{
    public function run(): void
    {
        $merks = MerkArmada::all();
        $jenis = JenisArmada::all();

        // 5 armada — masing-masing relasi ke jenis dan merk
        $armadas = [
            ['nopol' => 'B 1234 ABC'],
            ['nopol' => 'B 5678 DEF'],
            ['nopol' => 'D 9012 GHI'],
            ['nopol' => 'D 3456 JKL'],
            ['nopol' => 'E 7890 MNO'],
        ];

        foreach ($armadas as $index => $data) {
            Armada::create([
                'nopol'           => $data['nopol'],
                'merk_armada_id'  => $merks->random()->id,
                'jenis_armada_id' => $jenis->random()->id,
            ]);
        }
    }
}
