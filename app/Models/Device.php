<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'device_code',
        'type',
        'vendor',
        'model',
        'serial_number',
        'ip_address',
        'mac_address',
        'firmware_version',
        'status',
        'location_id',
        'procurement_date',
        'installation_date',
        'warranty_expiry',
        'amc_expiry',
        'eol_date',
        'remarks',
        'is_critical',
        'monitoring_enabled'
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'monitoring_enabled' => 'boolean',
        'procurement_date' => 'date',
        'installation_date' => 'date',
        'warranty_expiry' => 'date',
        'amc_expiry' => 'date',
        'eol_date' => 'date',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function monitoringLogs()
    {
        return $this->hasMany(MonitoringLog::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function getActivePortsCountAttribute()
    {
        return $this->ports()->where('status', 'active')->count();
    }

    public function getTotalPortsCountAttribute()
    {
        return $this->ports()->count();
    }

    public function getPortUtilizationPercentAttribute()
    {
        if ($this->total_ports_count === 0) {
            return 0;
        }

        return round(($this->active_ports_count / $this->total_ports_count) * 100, 2);
    }
}
