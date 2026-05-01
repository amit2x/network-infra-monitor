<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;


class Alert extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'device_id',
        'type',
        'severity',
        'title',
        'message',
        'additional_data',
        'is_read',
        'is_resolved',
        'resolved_at',
        'resolved_by'
    ];

    protected $casts = [
        'additional_data' => 'json',
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
