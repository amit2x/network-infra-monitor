<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'json',
    ];

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer of the activity.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include activities for a specific user.
     */
    public function scopeCausedBy($query, $user)
    {
        return $query->where('causer_type', get_class($user))
                     ->where('causer_id', $user->id);
    }

    /**
     * Scope a query to only include activities of a specific type.
     */
    public function scopeInLog($query, $logName)
    {
        return $query->where('log_name', $logName);
    }
}
