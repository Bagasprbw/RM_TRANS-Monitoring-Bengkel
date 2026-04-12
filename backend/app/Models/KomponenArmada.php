<?php

namespace App\Models;

use App\Traits\GeneratesCustomId;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KomponenArmada extends Model
{
    use GeneratesCustomId;

    protected static function idPrefix(): string
    {
        return 'KMP';
    }

    protected $table = 'komponen_armada';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'monitoring_armada_id',
        'kategori_komponen_id',
        'nama_komponen',
        'tipe_pelacakan',
        'target_km',
        'target_tanggal',
        'target_hari',
        'km_terakhir_perawatan',
        'tanggal_terakhir_perawatan',
        'status',
        'has_identity',
    ];

    public function monitoring()
    {
        return $this->belongsTo(MonitoringArmadaAktif::class, 'monitoring_armada_id');
    }

    public function kategori()
    {
        return $this->belongsTo(CategoryComponen::class, 'kategori_komponen_id');
    }

    public function detail()
    {
        return $this->hasMany(DetailKomponenArmada::class, 'komponen_armada_id');
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatPerawatanKomponen::class, 'komponen_armada_id');
    }

    /**
     * Calculate health of the component
     * 
     * @param float $currentKm Current kilometer of the vehicle
     * @return array [health, remaining]
     */
    public function calculateHealth($currentKm = 0)
    {
        $health = 100;
        $remaining = '-';

        if ($this->tipe_pelacakan === 'km') {
            $usedKm = $currentKm - ($this->km_terakhir_perawatan ?? 0);
            $target = $this->target_km ?? 1;
            $health = max(0, min(100, 100 - ($usedKm / $target * 100)));
            $remaining = max(0, $target - $usedKm) . ' km lagi';
        } elseif ($this->tipe_pelacakan === 'days') {
            $lastService = Carbon::parse($this->tanggal_terakhir_perawatan ?? $this->created_at);
            $daysUsed = $lastService->diffInDays(Carbon::now());
            $target = $this->target_hari ?? 1;
            $health = max(0, min(100, 100 - ($daysUsed / $target * 100)));
            $remaining = max(0, $target - $daysUsed) . ' hari lagi';
        } elseif ($this->tipe_pelacakan === 'date') {
            $targetDate = Carbon::parse($this->target_tanggal);
            $daysTotal = Carbon::parse($this->tanggal_terakhir_perawatan ?? $this->created_at)->diffInDays($targetDate);
            $daysRemaining = Carbon::now()->diffInDays($targetDate, false);
            
            if ($daysTotal > 0) {
                $health = max(0, min(100, ($daysRemaining / $daysTotal * 100)));
            } else {
                $health = $daysRemaining > 0 ? 100 : 0;
            }
            $remaining = $daysRemaining . ' hari tersisa';
        }

        return [
            'health' => round($health, 1),
            'remaining' => $remaining
        ];
    }
}
