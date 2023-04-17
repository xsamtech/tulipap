<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['payment_code', 'updated_at', 'status_id', 'user_id'];

    /**
     * ONE-TO-MANY
     * One status for several carts
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several carts
     */
    public function user()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * MANY-TO-ONE
     * Several prepaid cards for a cart
     */
    public function prepaid_cards()
    {
        return $this->hasMany(PrepaidCard::class);
    }

    /**
     * MANY-TO-ONE
     * Several albums for a cart
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
