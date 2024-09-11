<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    // If the primary key is not 'id', specify it here
    protected $primaryKey = 'trip_id';

    protected $fillable = [
        'jadwal_perjalanan',
        'alamat_awal',
        'latitude_awal',
        'longitude_awal',
        'alamat_tujuan',
        'latitude_tujuan',
        'longitude_tujuan',
        'nama_kendaraan',
        'no_polisi',
        'status',
        'trip_token',
        'group_id',
        'driver_id',
        'dimulai_pada',
        'diakhiri_pada',
        'durasi_perjalanan',
        'tinggi_badan_driver',
        'berat_badan_driver',
        'tekanan_darah_driver',
        'riwayat_penyakit_driver',
    ];

    /**
     * The group associated with the trip.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * The driver associated with the trip.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
