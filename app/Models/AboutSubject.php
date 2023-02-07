<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class AboutSubject extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['subject'];

    protected $fillable = ['subject', 'subject_description', 'updated_at', 'status_id'];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->only(self::SEARCHABLE_FIELDS);
    }

    /**
     * ONE-TO-MANY
     * One status for several about subjects
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * MANY-TO-ONE
     * Several about titles for a about subject
     */
    public function about_titles()
    {
        return $this->hasMany(AboutTitle::class);
    }

    /**
     * MANY-TO-ONE
     * Several icons for a about subject
     */
    public function icons()
    {
        return $this->hasMany(Icon::class);
    }
}
