<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = ['update', 'advertising', 'communique', 'tips_tricks', 'updated_at', 'preference_id', 'status_id'];

    /**
     * ONE-TO-ONE
     * One preference for a E-mail notification
     */
    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several E-mail notifications
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
