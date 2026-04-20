<?php

namespace Database\Seeders;

use App\Models\JenisArmada;
use Illuminate\Database\Seeder;

class JenisArmadaSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [
            'Jenis-A',
            'Jenis-B',
            'Jenis-C',
            'Jenis-D',
            'Jenis-E',
        ];

        foreach ($jenis as $nama) {
            JenisArmada::create(['nama_jenis' => $nama]);
        }
    }
}
