<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkTopology extends Model
{
    protected $table = 'network_topology';

    protected $fillable = [
        'device_id',
        'neighbor_device_id',
        'local_interface',
        'remote_interface',
        'connection_type',
        'bandwidth',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function neighbor()
    {
        return $this->belongsTo(Device::class, 'neighbor_device_id');
    }
}