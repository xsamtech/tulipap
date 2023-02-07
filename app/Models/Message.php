<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Message extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['message_content'];

    protected $fillable = ['message_subject', 'message_content', 'sent_to', 'answered_for', 'last_status', 'status_given_by', 'updated_at', 'status_id', 'type_id', 'user_id'];

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
     * One status for several messages
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One type for several messages
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several messages
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several albums for a message
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
