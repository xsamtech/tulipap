<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankCode extends Model
{
    use HasFactory;

    protected $fillable = ['card_name', 'card_number', 'account_number', 'expiration', 'updated_at', 'service_id', 'status_id', 'user_id', 'company_id'];

    /**
     * ONE-TO-MANY
     * One service for several banks codes
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several banks codes
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several banks codes
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several banks codes
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
