<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Neighborhood extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['neighborhood_name'];

    protected $fillable = ['neighborhood_name', 'updated_at', 'area_id', 'office_id'];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->only(self::SEARCHABLE_FIELDS);
    }

    /**
     * ONE-TO-MANY
     * One area for several neighborhoods
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * ONE-TO-MANY
     * One office for several neighborhoods
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * MANY-TO-ONE
     * Several addresses for a neighborhood
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
