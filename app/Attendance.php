<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'token_present',
        'nama',
        'nip',
        'jabatan',
        'divisi',
        'unit_kerja',
        'metode_kehadiran',
        'jenis_kelamin',
        'kehadiran_status',
        'bukti_path',
        'submitted_at',
        'signature',
        'status',
    ];

    protected $dates = ['submitted_at'];

    // Relasi ke Present berdasarkan token
    public function present()
    {
        return $this->belongsTo(Present::class, 'token_present', 'token');
    }

    // Relasi ke User berdasarkan NIP
    public function user()
    {
        return $this->belongsTo(User::class, 'nip', 'no_pegawai');
    }

    // Atribut untuk menampilkan nama lengkap pegawai
    public function getDisplayNameAttribute()
    {
        return optional($this->user)->namanogelar ?? $this->nama ?? '-';
    }

    // Atribut untuk nama divisi dari relasi user
    public function getDivisiNameAttribute()
    {
        return optional(optional($this->user)->divisi)->nama ?? '-';
    }

    // Atribut untuk nama jabatan dari relasi user
    public function getJabatanNameAttribute()
    {
        return optional(optional($this->user)->jabasn)->nama ?? '-';
    }

    /**
     * Scope untuk filter tidak hadir
     * Mengambil user yang belum memiliki attendance record
     */
    public function scopeNotAttended($query, $token)
    {
        $attendedNips = static::where('token_present', $token)
            ->pluck('nip')
            ->toArray();

        return User::where('aktif', 'Y')
            ->whereNull('deleted_at')
            ->whereNotIn('no_pegawai', $attendedNips);
    }
}