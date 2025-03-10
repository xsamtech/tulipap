<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Province extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['province_name'];

    protected $fillable = ['province_name', 'updated_at', 'country_id'];

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
     * One country for several provinces
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * MANY-TO-ONE
     * Several cities for a province
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
