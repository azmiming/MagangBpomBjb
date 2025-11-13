<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisi';
    protected $fillable = ['nama', 'lokasi', 'kode_sppd'];

    public function users()
    {
        return $this->hasMany(User::class, 'divisi_id');
    }
}
