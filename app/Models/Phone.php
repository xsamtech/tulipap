<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Phone extends Model
{
    use HasFactory;

    protected $fillable = ['phone_code', 'phone_number', 'updated_at', 'service_id', 'status_id', 'user_id', 'company_id', 'office_id'];

    /**
     * ONE-TO-MANY
     * One service for several phones
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several phones
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several phones
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several phones
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ONE-TO-MANY
     * One office for several phones
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
