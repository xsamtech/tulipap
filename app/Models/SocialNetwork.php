<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialNetwork extends Model
{
    use HasFactory;

    protected $fillable = ['network_name', 'network_url', 'updated_at', 'user_id', 'company_id'];

    /**
     * ONE-TO-MANY
     * One user for several social networks
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several social networks
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
