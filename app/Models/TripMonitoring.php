<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'heart_rate',
        'latitude',
        'longitude',
        'kecepatan',
        'rpm',
        'thurttle',
        'sudut_postural',
        'kecepatan_postural',
        'durasi_postural',
        'status',
        'trip_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
