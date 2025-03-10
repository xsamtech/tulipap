<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Role extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['role_name'];

    protected $fillable = ['role_name', 'role_description', 'updated_at'];

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
     * Several role_users for a role
     */
    public function role_users()
    {
        return $this->hasMany(RoleUser::class);
    }

    /**
     * MANY-TO-ONE
     * Several icons for a role
     */
    public function icons()
    {
        return $this->hasMany(Icon::class);
    }
}
