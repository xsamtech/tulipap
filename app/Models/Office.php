<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Model
{
    use HasFactory;

    protected $fillable = ['office_code', 'office_name', 'updated_at', 'company_id'];

    /**
     * ONE-TO-MANY
     * One company for several offices
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * MANY-TO-ONE
     * Several users for an office
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several phones for an office
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * MANY-TO-ONE
     * Several neighborhoods for an office
     */
    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }

    /**
     * ONE-TO-ONE
     * One address for an office
     */
    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
