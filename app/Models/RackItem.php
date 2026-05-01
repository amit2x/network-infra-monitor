<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RackItem extends Model
{
    protected $fillable = [
        'rack_id',
        'device_id',
        'unit_start',
        'unit_height',
        'position',
        'side',
    ];

    protected $casts = [
        'unit_start' => 'integer',
        ];
        
}