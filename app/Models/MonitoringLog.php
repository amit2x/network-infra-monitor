<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'event_type',
        'status',
        'message',
        'details',
        'response_time_ms'
    ];

    protected $casts = [
        'details' => 'json',
        'response_time_ms' => 'decimal:2',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
