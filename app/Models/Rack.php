<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'rack_code',
        'total_units',
        'position_x',
        'position_y',
        'layout_data',
    ];

    protected $casts = [
        'total_units' => 'integer',
        'layout_data' => 'json',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function rackItems()
    {
        return $this->hasMany(RackItem::class);
    }
}