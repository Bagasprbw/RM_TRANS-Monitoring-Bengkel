<?php

namespace App\Models;

use App\Traits\GeneratesCustomId;
use Illuminate\Database\Eloquent\Model;

class MerkArmada extends Model
{
    use GeneratesCustomId;

    protected static function idPrefix(): string
    {
        return 'MRK';
    }

    protected $table = 'merk_armada';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nama_merk'
    ];

    public function armada()
    {
        return $this->hasMany(Armada::class, 'merk_armada_id');
    }
}
