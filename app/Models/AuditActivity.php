<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditActivity extends Model
{
    protected $table = 'audit_activities';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'action',
        'module',
        'module_id',
        'module_name',
        'old_values',
        'new_values',
        'description',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'status',
        'error_message',
        'metadata',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'metadata' => 'json',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for specific module.
     */
    public function scopeForModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope for specific action.
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for today's activities.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('performed_at', today());
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('performed_at', [$from, $to]);
    }

    /**
     * Get the action icon.
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'fa-plus-circle',
            'updated' => 'fa-edit',
            'deleted' => 'fa-trash',
            'restored' => 'fa-undo',
            'logged_in' => 'fa-sign-in-alt',
            'logged_out' => 'fa-sign-out-alt',
            'ping' => 'fa-broadcast-tower',
            'resolved' => 'fa-check-circle',
            'exported' => 'fa-download',
            default => 'fa-circle'
        };
    }

    /**
     * Get the action color.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            'logged_in' => 'primary',
            'logged_out' => 'secondary',
            'ping' => 'info',
            'resolved' => 'success',
            'exported' => 'dark',
            default => 'light'
        };
    }

    /**
     * Get the module icon.
     */
    public function getModuleIconAttribute(): string
    {
        return match($this->module) {
            'Device' => 'fa-server',
            'Port' => 'fa-plug',
            'Location' => 'fa-map-marker-alt',
            'User' => 'fa-user',
            'Alert' => 'fa-bell',
            'Report' => 'fa-file-alt',
            'Profile' => 'fa-user-circle',
            'Settings' => 'fa-cog',
            'Monitoring' => 'fa-chart-line',
            default => 'fa-cube'
        };
    }
}