<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandwidthData extends Model
{
    protected $fillable = [
        'device_id',
        'port_number',
        'in_octets',
        'out_octets',
        'in_bandwidth_bps',
        'out_bandwidth_bps',
        'in_utilization_percent',
        'out_utilization_percent',
        'port_speed',
        'collected_at',
    ];

    protected $casts = [
        'in_octets' => 'integer',
        'out_octets' => 'integer',
        'in_bandwidth_bps' => 'integer',
        'out_bandwidth_bps' => 'integer',
        'in_utilization_percent' => 'float',
        'out_utilization_percent' => 'float',
        'collected_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}