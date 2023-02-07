<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class AboutContent extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['subtitle'];

    protected $fillable = ['subtitle', 'content', 'updated_at', 'about_title_id'];

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
     * One about title for several about contents
     */
    public function about_title()
    {
        return $this->belongsTo(AboutTitle::class);
    }

    /**
     * MANY-TO-ONE
     * Several albums for a about content
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
