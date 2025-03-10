<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Icon extends Model
{
    use HasFactory;

    protected $fillable = ['icon_name', 'icon_color', 'updated_at', 'about_subject_id', 'status_id', 'type_id', 'service_id', 'role_id', 'currency_id', 'icon_status_id'];

    /**
     * ONE-TO-MANY
     * One about subject for several icons
     */
    public function about_subject()
    {
        return $this->belongsTo(AboutSubject::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several icons
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One type for several icons
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One service for several icons
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * ONE-TO-MANY
     * One role for several icons
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * ONE-TO-MANY
     * One currency for several icons
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several icons
     */
    public function icon_status()
    {
        return $this->belongsTo(Status::class);
    }
}
