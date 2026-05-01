<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityService
{
    /**
     * Log an activity.
     */
    public function log(string $description, string $logName = 'default', $subject = null, array $properties = []): Activity
    {
        return Activity::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id' => Auth::id(),
            'properties' => $properties,
        ]);
    }

    /**
     * Log device creation.
     */
    public function logDeviceCreated($device): Activity
    {
        return $this->log(
            "Device '{$device->name}' was created",
            'device',
            $device,
            ['device_code' => $device->device_code, 'ip_address' => $device->ip_address]
        );
    }

    /**
     * Log device update.
     */
    public function logDeviceUpdated($device, array $changes = []): Activity
    {
        return $this->log(
            "Device '{$device->name}' was updated",
            'device',
            $device,
            ['changes' => $changes]
        );
    }

    /**
     * Log device deletion.
     */
    public function logDeviceDeleted($device): Activity
    {
        return $this->log(
            "Device '{$device->name}' was deleted",
            'device',
            null,
            ['device_code' => $device->device_code, 'ip_address' => $device->ip_address]
        );
    }

    /**
     * Log user login.
     */
    public function logLogin($user): Activity
    {
        return $this->log(
            "User '{$user->name}' logged in",
            'auth',
            $user,
            ['email' => $user->email, 'ip' => request()->ip()]
        );
    }

    /**
     * Log user logout.
     */
    public function logLogout($user): Activity
    {
        return $this->log(
            "User '{$user->name}' logged out",
            'auth',
            $user,
            ['email' => $user->email]
        );
    }

    /**
     * Log profile update.
     */
    public function logProfileUpdated($user): Activity
    {
        return $this->log(
            "User '{$user->name}' updated their profile",
            'profile',
            $user
        );
    }

    /**
     * Log password change.
     */
    public function logPasswordChanged($user): Activity
    {
        return $this->log(
            "User '{$user->name}' changed their password",
            'profile',
            $user
        );
    }
}
