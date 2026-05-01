<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnmpData extends Model
{
    protected $fillable = [
        'device_id',
        'system_info',
        'cpu_usage',
        'memory_usage',
        'memory_total',
        'memory_used',
        'interface_count',
        'interfaces_data',
        'raw_data',
        'collected_at',
    ];

    protected $casts = [
        'system_info' => 'json',
        'interfaces_data' => 'json',
        'raw_data' => 'json',
        'collected_at' => 'datetime',
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}