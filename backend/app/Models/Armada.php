<?php

namespace App\Models;

use App\Traits\GeneratesCustomId;
use Illuminate\Database\Eloquent\Model;

class Armada extends Model
{
    use GeneratesCustomId;

    protected static function idPrefix(): string
    {
        return 'ARM';
    }

    protected $table = 'armada';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nopol',
        'merk_armada_id',
        'jenis_armada_id'
    ];

    public function merk()
    {
        return $this->belongsTo(MerkArmada::class, 'merk_armada_id');
    }

    public function jenis()
    {
        return $this->belongsTo(JenisArmada::class, 'jenis_armada_id');
    }

    public function monitoring()
    {
        return $this->hasOne(MonitoringArmadaAktif::class, 'armada_id');
    }

    public function logs()
    {
        return $this->hasMany(LogKilometer::class, 'armada_id');
    }
}
