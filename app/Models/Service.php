<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Service extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['service_name'];

    protected $fillable = ['service_name', 'provider', 'updated_at', 'group_id'];

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
     * One group for several notifications
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * MANY-TO-ONE
     * Several phones for a service
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * MANY-TO-ONE
     * Several bank codes for a service
     */
    public function bank_codes()
    {
        return $this->hasMany(BankCode::class);
    }

    /**
     * MANY-TO-ONE
     * Several icons for a service
     */
    public function icons()
    {
        return $this->hasMany(Icon::class);
    }

    /**
     * MANY-TO-ONE
     * Several albums for a service
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
