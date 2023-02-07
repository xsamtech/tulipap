<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Continent extends Model
{
    use HasFactory;

    protected $fillable = ['continent_name', 'continent_abbreviation', 'updated_at'];

    /**
     * MANY-TO-ONE
     * Several regions for a continent
     */
    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
