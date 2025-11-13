<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Present extends Model
{
    protected $table = 'presents';

    // Kolom yang bisa diisi secara massal
   protected $fillable = [
    'acara',
    'tanggal',
    'lokasi', // ← tambahkan ini
    'status',
    'tipe',
    'status_kehadiran',
    'metode_kehadiran',
    'jam_buka',
    'token',
];

    /**
     * Relasi ke model Attendance
     * Satu presensi memiliki banyak kehadiran (absensi)
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'token_present', 'token');
    }

    /**
     * Accessor: ubah nilai 'tipe' dari string menjadi array saat diakses
     */
    public function getTipeAttribute($value)
    {
        // Pastikan hasil selalu array meskipun kosong
        return !empty($value)
            ? array_map('trim', explode(',', $value))
            : [];
    }

    /**
     * Accessor: ubah nilai 'status_kehadiran' dari string menjadi array saat diakses
     */
    public function getStatusKehadiranAttribute($value)
    {
        return !empty($value)
            ? array_map('trim', explode(',', $value))
            : [];
    }

    /**
     * Mutator: simpan 'tipe' sebagai string (dipisah koma)
     */
    public function setTipeAttribute($value)
    {
        // Jika array → ubah jadi string
        $this->attributes['tipe'] = is_array($value)
            ? implode(',', $value)
            : (string) $value;
    }

    /**
     * Mutator: simpan 'status_kehadiran' sebagai string (dipisah koma)
     */
    public function setStatusKehadiranAttribute($value)
    {
        $this->attributes['status_kehadiran'] = is_array($value)
            ? implode(',', $value)
            : (string) $value;
    }
}
