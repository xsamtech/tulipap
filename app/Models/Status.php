<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Status extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['status_name'];

    protected $fillable = ['status_name', 'status_description', 'updated_at', 'group_id'];

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
     * One group for several statuses
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * MANY-TO-ONE
     * Several companies for a status
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    /**
     * MANY-TO-ONE
     * Several users for a status
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several phones for a status
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * MANY-TO-ONE
     * Several bank codes for a status
     */
    public function bank_codes()
    {
        return $this->hasMany(BankCode::class);
    }

    /**
     * MANY-TO-ONE
     * Several addresses for a status
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * MANY-TO-ONE
     * Several emails for a status
     */
    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    /**
     * MANY-TO-ONE
     * Several messages for a status
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * MANY-TO-ONE
     * Several E-mail notifications for a status
     */
    public function email_notifications()
    {
        return $this->hasMany(EmailNotification::class);
    }

    /**
     * MANY-TO-ONE
     * Several SMS notifications for a status
     */
    public function sms_notifications()
    {
        return $this->hasMany(SmsNotification::class);
    }

    /**
     * MANY-TO-ONE
     * Several about subjects for a status
     */
    public function about_subjects()
    {
        return $this->hasMany(AboutSubject::class);
    }

    /**
     * MANY-TO-ONE
     * Several files for a status
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * MANY-TO-ONE
     * Several invoices for a status
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * MANY-TO-ONE
     * Several carts for a status
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * MANY-TO-ONE
     * Several prepaid_cards for a status
     */
    public function prepaid_cards()
    {
        return $this->hasMany(PrepaidCard::class);
    }

    /**
     * MANY-TO-ONE
     * Several icons for a status
     */
    public function icons()
    {
        return $this->hasMany(Icon::class);
    }
}
