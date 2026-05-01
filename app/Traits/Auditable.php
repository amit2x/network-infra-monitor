<?php

namespace App\Traits;

use App\Models\AuditActivity;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        // Log when a model is created
        static::created(function ($model) {
            $model->auditCreated();
        });

        // Log when a model is updated
        static::updated(function ($model) {
            $model->auditUpdated();
        });

        // Log when a model is deleted
        static::deleted(function ($model) {
            $model->auditDeleted();
        });

        // Log when a model is restored (soft delete)
        if (method_exists(static::class, 'bootSoftDeletes')) {
            static::restored(function ($model) {
                $model->auditRestored();
            });
        }
    }

    /**
     * Audit a created event.
     */
    public function auditCreated()
    {
        $this->audit('created', null, $this->getAuditAttributes());
    }

    /**
     * Audit an updated event.
     */
    public function auditUpdated()
    {
        $oldValues = [];
        $newValues = [];
        
        foreach ($this->getChanges() as $attribute => $newValue) {
            // Skip timestamps and hidden fields
            if (in_array($attribute, $this->getAuditExcludedAttributes())) {
                continue;
            }
            
            $oldValues[$attribute] = $this->getOriginal($attribute);
            $newValues[$attribute] = $newValue;
        }
        
        if (!empty($oldValues)) {
            $this->audit('updated', $oldValues, $newValues);
        }
    }

    /**
     * Audit a deleted event.
     */
    public function auditDeleted()
    {
        $this->audit('deleted', $this->getAuditAttributes(), null);
    }

    /**
     * Audit a restored event.
     */
    public function auditRestored()
    {
        $this->audit('restored', null, $this->getAuditAttributes());
    }

    /**
     * Create an audit record.
     */
    public function audit($action, $oldValues = null, $newValues = null)
    {
        $user = Auth::user();
        
        $data = [
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : 'System',
            'user_email' => $user ? $user->email : null,
            'user_role' => $user ? ($user->roles->first()->name ?? null) : null,
            'action' => $action,
            'module' => class_basename($this),
            'module_id' => $this->getKey(),
            'module_name' => $this->getAuditName(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $this->generateAuditDescription($action),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'success',
            'performed_at' => now(),
        ];
        
        return AuditActivity::create($data);
    }

    /**
     * Get the name to use for audit.
     */
    public function getAuditName()
    {
        return $this->name ?? $this->title ?? $this->id ?? 'Unknown';
    }

    /**
     * Get attributes to audit.
     */
    public function getAuditAttributes()
    {
        $attributes = $this->attributesToArray();
        
        // Remove excluded attributes
        foreach ($this->getAuditExcludedAttributes() as $excluded) {
            unset($attributes[$excluded]);
        }
        
        return $attributes;
    }

    /**
     * Get excluded attributes from audit.
     */
    public function getAuditExcludedAttributes()
    {
        return [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'password',
            'remember_token',
            'email_verified_at',
            'last_login_at',
            'last_login_ip',
        ];
    }

    /**
     * Generate audit description.
     */
    public function generateAuditDescription($action)
    {
        $module = class_basename($this);
        $name = $this->getAuditName();
        
        $actionText = match($action) {
            'created' => 'was created',
            'updated' => 'was updated',
            'deleted' => 'was deleted',
            'restored' => 'was restored',
            default => "was {$action}",
        };
        
        return "{$module} '{$name}' {$actionText}";
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return AuditActivity::where('module', class_basename($this))
            ->where('module_id', $this->getKey())
            ->orderBy('performed_at', 'desc');
    }

    /**
     * Get latest audit log.
     */
    public function latestAudit()
    {
        return $this->auditLogs()->first();
    }

    /**
     * Get audit count.
     */
    public function auditCount()
    {
        return $this->auditLogs()->count();
    }
}