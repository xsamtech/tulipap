<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Type extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['type_name'];

    protected $fillable = ['type_name', 'type_description', 'updated_at', 'group_id'];

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
     * One group for several types
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * MANY-TO-ONE
     * Several users for a type
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several files for a type
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * MANY-TO-ONE
     * Several billing_methods for a type
     */
    public function billing_methods()
    {
        return $this->hasMany(BillingMethod::class);
    }

    /**
     * MANY-TO-ONE
     * Several icons for a type
     */
    public function icons()
    {
        return $this->hasMany(Icon::class);
    }

    /**
     * MANY-TO-ONE
     * Several histories for a type
     */
    public function histories()
    {
        return $this->hasMany(History::class);
    }
}
