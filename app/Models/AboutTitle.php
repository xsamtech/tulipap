<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class AboutTitle extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['title'];

    protected $fillable = ['title', 'updated_at', 'about_subject_id'];

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
     * One about_subject for several about_titles
     */
    public function about_subject()
    {
        return $this->belongsTo(AboutSubject::class);
    }

    /**
     * MANY-TO-ONE
     * Several about_contents for a about_title
     */
    public function about_contents()
    {
        return $this->hasMany(AboutContent::class);
    }
}
