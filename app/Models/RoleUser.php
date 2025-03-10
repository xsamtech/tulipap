<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleUser extends Model
{
    use HasFactory;

    protected $fillable = ['role_id', 'user_id', 'selected', 'updated_at'];

    /**
     * ONE-TO-MANY
     * One role for several role_users
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several role_users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
