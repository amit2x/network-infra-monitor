<?php

namespace App\Services;

use App\Models\AuditActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Jenssegers\Agent\Agent;

class AuditService
{
    protected $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * Log an audit activity.
     */
    public function log(
        string $action,
        string $module,
        $moduleId = null,
        $moduleName = null,
        array $oldValues = null,
        array $newValues = null,
        string $description = null,
        array $metadata = null,
        string $status = 'success',
        string $errorMessage = null
    ): AuditActivity {
        $user = Auth::user();
        
        return AuditActivity::create([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : 'System',
            'user_email' => $user ? $user->email : null,
            'user_role' => $user ? ($user->roles->first()->name ?? null) : null,
            'action' => $action,
            'module' => $module,
            'module_id' => $moduleId,
            'module_name' => $moduleName,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?? $this->generateDescription($action, $module, $moduleName),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'browser' => $this->agent->browser(),
            'platform' => $this->agent->platform(),
            'status' => $status,
            'error_message' => $errorMessage,
            'metadata' => $metadata,
            'performed_at' => now(),
        ]);
    }

    /**
     * Log a create action.
     */
    public function logCreated($model, string $moduleName = null): AuditActivity
    {
        $module = class_basename($model);
        $moduleName = $moduleName ?? ($model->name ?? $model->title ?? $model->id ?? null);
        
        return $this->log(
            'created',
            $module,
            $model->id,
            $moduleName,
            null,
            $model->toArray(),
            "{$module} '{$moduleName}' was created"
        );
    }

    /**
     * Log an update action.
     */
    public function logUpdated($model, array $oldValues, string $moduleName = null): AuditActivity
    {
        $module = class_basename($model);
        $moduleName = $moduleName ?? ($model->name ?? $model->title ?? $model->id ?? null);
        
        // Get only changed values
        $changes = [];
        foreach ($model->getChanges() as $key => $value) {
            if (!in_array($key, ['updated_at', 'created_at'])) {
                $changes[$key] = [
                    'old' => $oldValues[$key] ?? null,
                    'new' => $value
                ];
            }
        }
        
        return $this->log(
            'updated',
            $module,
            $model->id,
            $moduleName,
            $oldValues,
            $model->fresh()->toArray(),
            "{$module} '{$moduleName}' was updated",
            ['changes' => $changes]
        );
    }

    /**
     * Log a delete action.
     */
    public function logDeleted($model, string $moduleName = null): AuditActivity
    {
        $module = class_basename($model);
        $moduleName = $moduleName ?? ($model->name ?? $model->title ?? $model->id ?? null);
        
        return $this->log(
            'deleted',
            $module,
            $model->id,
            $moduleName,
            $model->toArray(),
            null,
            "{$module} '{$moduleName}' was deleted"
        );
    }

    /**
     * Log a login action.
     */
    public function logLogin($user): AuditActivity
    {
        return $this->log(
            'logged_in',
            'User',
            $user->id,
            $user->name,
            null,
            null,
            "User '{$user->name}' logged in"
        );
    }

    /**
     * Log a logout action.
     */
    public function logLogout($user): AuditActivity
    {
        return $this->log(
            'logged_out',
            'User',
            $user->id,
            $user->name,
            null,
            null,
            "User '{$user->name}' logged out"
        );
    }

    /**
     * Log a failed action.
     */
    public function logFailed(
        string $action,
        string $module,
        string $errorMessage,
        $moduleId = null,
        $moduleName = null
    ): AuditActivity {
        return $this->log(
            $action,
            $module,
            $moduleId,
            $moduleName,
            null,
            null,
            "Failed to {$action} {$module}",
            null,
            'failed',
            $errorMessage
        );
    }

    /**
     * Generate description.
     */
    private function generateDescription(string $action, string $module, $moduleName): string
    {
        $actionText = match($action) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'restored' => 'restored',
            default => $action . 'd'
        };

        $name = $moduleName ?? 'Unknown';
        
        return "{$module} '{$name}' was {$actionText}";
    }
}