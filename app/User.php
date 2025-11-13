<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $table = 'users';
    protected $fillable = [
        'no_pegawai', 'name', 'jabasn_id', 'divisi_id', 'status', 'jkel'
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function jabasn()
    {
        return $this->belongsTo(Jabasn::class, 'jabasn_id');
    }
}
