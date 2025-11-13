<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jabasn extends Model
{
    protected $table = 'jabasn';
    protected $fillable = ['kelompok', 'nama', 'jabatan'];

    public function users()
    {
        return $this->hasMany(User::class, 'jabasn_id');
    }
}

