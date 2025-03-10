<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'street', 'updated_at', 'area_id', 'neighborhood_id', 'status_id', 'user_id', 'company_id', 'office_id'];

    /**
     * ONE-TO-MANY
     * One area for several addresses
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * ONE-TO-MANY
     * One neighborhood for several addresses
     */
    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several addresses
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several addresses
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several addresses
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ONE-TO-ONE
     * One office for an address
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
