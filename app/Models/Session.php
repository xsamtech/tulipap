<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['ip_address', 'user_agent', 'payload', 'last_activity', 'updated_at', 'user_id'];

    /**
     * ONE-TO-MANY
     * One user for several sessions
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
