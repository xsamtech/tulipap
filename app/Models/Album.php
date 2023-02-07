<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Album extends Model
{
    use HasFactory;

    protected $fillable = ['album_name', 'updated_at', 'user_id', 'company_id', 'message_id', 'about_content_id', 'service_id'];

    /**
     * ONE-TO-MANY
     * One user for several albums
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several albums
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ONE-TO-MANY
     * One message for several albums
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * ONE-TO-MANY
     * One about content for several albums
     */
    public function about_content()
    {
        return $this->belongsTo(AboutContent::class);
    }

    /**
     * ONE-TO-MANY
     * One service for several albums
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * MANY-TO-ONE
     * Several files for an album
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }
}
