<?php

namespace Database\Seeders;

use App\Models\MerkArmada;
use Illuminate\Database\Seeder;

class MerkArmadaSeeder extends Seeder
{
    public function run(): void
    {
        $merks = [
            'Hino',
            'Isuzu',
            'Mitsubishi',
            'Fuso',
            'Scania',
            'Volvo',
        ];

        foreach ($merks as $nama) {
            MerkArmada::create(['nama_merk' => $nama]);
        }
    }
}
