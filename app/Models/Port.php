<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'port_number',
        'type',
        'status',
        'service_name',
        'connected_device',
        'vlan_id',
        'speed_mbps',
        'description'
    ];

    protected $casts = [
        'vlan_id' => 'integer',
        'speed_mbps' => 'integer',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
