<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Country extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['country_name'];

    protected $fillable = ['country_name', 'country_abbreviation', 'country_phone_code', 'country_lang_code', 'updated_at', 'region_id'];

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
     * One region for several countries
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * MANY-TO-ONE
     * Several provinces for a country
     */
    public function provinces()
    {
        return $this->hasMany(Province::class);
    }
}
