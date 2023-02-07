<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Email extends Model
{
    use HasFactory;

    protected $fillable = ['email_content', 'updated_at', 'status_id', 'user_id', 'company_id'];

    /**
     * ONE-TO-MANY
     * One status for several e-mails
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several e-mails
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several e-mails
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
