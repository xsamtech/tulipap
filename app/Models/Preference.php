<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Preference extends Model
{
    use HasFactory;

    protected $fillable = ['prefered_theme', 'prefered_language', 'login_verify', 'gps_location', 'email_confirmed', 'phone_confirmed', 'updated_at', 'user_id'];

    /**
     * ONE-TO-ONE
     * One user for a preference
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-ONE
     * One E-mail notification for a preference
     */
    public function email_notification()
    {
        return $this->hasOne(EmailNotification::class);
    }

    /**
     * ONE-TO-ONE
     * One SMS notification for a preference
     */
    public function sms_notification()
    {
        return $this->hasOne(SmsNotification::class);
    }
}
