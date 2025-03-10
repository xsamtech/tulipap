<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_number', 'invoiced_period', 'tolerated_delay', 'publishing_date', 'used_quantity', 'updated_at', 'company_id', 'status_id', 'user_id'];

    /**
     * ONE-TO-MANY
     * One company for several invoices
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several invoices
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several invoices
     */
    public function user()
    {
        return $this->belongsTo(Status::class);
    }
}
