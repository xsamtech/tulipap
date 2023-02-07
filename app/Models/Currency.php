<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Currency extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['currency_name'];

    protected $fillable = ['currency_name', 'currency_abbreviation', 'updated_at'];

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
     * MANY-TO-ONE
     * Several exchange rates for a currency
     */
    public function exchange_rates()
    {
        return $this->hasMany(ExchangeRate::class);
    }

    /**
     * MANY-TO-ONE
     * Several billing methods for a currency
     */
    public function billing_methods()
    {
        return $this->hasMany(BillingMethod::class);
    }

    /**
     * MANY-TO-ONE
     * Several icons for a currency
     */
    public function icons()
    {
        return $this->hasMany(Icon::class);
    }
}
