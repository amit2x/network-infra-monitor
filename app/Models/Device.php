<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, SoftDeletes, Auditable;

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
        'monitoring_enabled',
        // SNMP fields
        'snmp_enabled',
        'snmp_community',
        'snmp_version',
        'snmp_port',
        'snmp_timeout',
        'snmp_polling_enabled',
        'snmp_polling_interval',
        //v3
            'snmp_v3_security_level',
            'snmp_v3_auth_protocol',
            'snmp_v3_auth_username',
            'snmp_v3_auth_password',
            'snmp_v3_priv_protocol',
            'snmp_v3_priv_password',
            'snmp_v3_context_name',
    ];
    
    
 

    protected $casts = [
        'is_critical' => 'boolean',
        'monitoring_enabled' => 'boolean',
        'procurement_date' => 'date',
        'installation_date' => 'date',
        'warranty_expiry' => 'date',
        'amc_expiry' => 'date',
        'eol_date' => 'date',
        // SNMP casts
        'snmp_enabled' => 'boolean',
        'snmp_polling_enabled' => 'boolean',
        'snmp_port' => 'integer',
        'snmp_timeout' => 'integer',
        'snmp_polling_interval' => 'integer',
        
        'snmp_v3_auth_password' => 'encrypted',
        'snmp_v3_priv_password' => 'encrypted',
    ];

    // Relationships
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

    public function snmpData()
    {
        return $this->hasMany(SnmpData::class);
    }

    public function latestSnmpData()
    {
        return $this->hasOne(SnmpData::class)->latestOfMany('collected_at');
    }

    // Accessors
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

    /**
     * Get the SNMP community with fallback to default.
     */
    public function getSnmpCommunityAttribute($value)
    {
        return $value ?: config('snmp.defaults.community', 'public');
    }

    /**
     * Get the SNMP timeout with fallback to default.
     */
    public function getSnmpTimeoutAttribute($value)
    {
        return $value ?: config('snmp.defaults.timeout', 1);
    }

    /**
     * Get the latest CPU usage.
     */
    public function getLatestCpuUsageAttribute()
    {
        return $this->latestSnmpData->cpu_usage ?? null;
    }

    /**
     * Get the latest memory usage.
     */
    public function getLatestMemoryUsageAttribute()
    {
        return $this->latestSnmpData->memory_usage ?? null;
    }

    /**
     * Check if SNMP is properly configured.
     */
    public function isSnmpConfigured(): bool
    {
        return $this->snmp_enabled && !empty($this->snmp_community);
    }
    
    
    
    /**
     * Get SNMP data for a specific time range.
     */
    public function snmpDataInRange($hours = 24)
    {
        return $this->hasMany(SnmpData::class)
            ->where('collected_at', '>=', now()->subHours($hours))
            ->orderBy('collected_at');
    }
    
    /**
     * Get average CPU usage for a period.
     */
    public function getAverageCpuUsage($hours = 24): float
    {
        return round($this->snmpData()
            ->where('collected_at', '>=', now()->subHours($hours))
            ->avg('cpu_usage') ?? 0, 2);
    }
    
    /**
     * Get average memory usage for a period.
     */
    public function getAverageMemoryUsage($hours = 24): float
    {
        return round($this->snmpData()
            ->where('collected_at', '>=', now()->subHours($hours))
            ->avg('memory_usage') ?? 0, 2);
    }
    
    /**
     * Get SNMP v3 configuration array
     */
    public function getSnmpV3ConfigAttribute(): array
    {
        return [
            'security_level' => $this->snmp_v3_security_level ?? 'authPriv',
            'auth_protocol' => $this->snmp_v3_auth_protocol ?? 'SHA',
            'auth_username' => $this->snmp_v3_auth_username ?? '',
            'auth_password' => $this->snmp_v3_auth_password ?? '',
            'priv_protocol' => $this->snmp_v3_priv_protocol ?? 'AES',
            'priv_password' => $this->snmp_v3_priv_password ?? '',
            'context_name' => $this->snmp_v3_context_name ?? '',
        ];
    }

}
