<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepaidCard extends Model
{
    use HasFactory;

    protected $fillable = ['card_number', 'number_of_kilowatt_hours', 'price', 'updated_at', 'status_id', 'company_id', 'cart_id'];

    /**
     * ONE-TO-MANY
     * One status for several prepaid cards
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several prepaid cards
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ONE-TO-MANY
     * One cart for several prepaid cards
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
