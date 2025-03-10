<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingMethod extends Model
{
    use HasFactory;

    protected $fillable = ['number_of_kilowatt_hours', 'price', 'updated_at', 'type_id', 'currency_id', 'company_id'];

    /**
     * ONE-TO-MANY
     * One type for several billing methods
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One currency for several billing methods
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several billing methods
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
