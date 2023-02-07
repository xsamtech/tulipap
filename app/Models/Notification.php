<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['notification_url', 'notification_content', 'updated_at', 'status_id', 'user_id'];

    /**
     * ONE-TO-MANY
     * One status for several notifications
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several notifications
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
