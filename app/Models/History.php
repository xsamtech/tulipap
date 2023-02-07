<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class History extends Model
{
    use HasFactory;

    protected $fillable = ['history_url', 'history_content', 'updated_at', 'type_id', 'user_id'];

    /**
     * ONE-TO-MANY
     * One type for several histories
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several histories
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
