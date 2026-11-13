<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = ['name', 'emoji', 'star_cost', 'is_active'];

    protected $casts = [
        'star_cost' => 'integer',
        'is_active' => 'boolean',
    ];
}