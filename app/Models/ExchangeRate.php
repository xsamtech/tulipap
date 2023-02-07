<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = ['rate', 'updated_at', 'currency1_id', 'currency2_id'];

    /**
     * ONE-TO-MANY
     * First currency for several exchange rates
     */
    public function currency1()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * ONE-TO-MANY
     * Second currency for several exchange rates
     */
    public function currency2()
    {
        return $this->belongsTo(Currency::class);
    }
}
