<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Company extends Model
{
    use HasFactory, Searchable;

    const SEARCHABLE_FIELDS = ['company_name'];

    protected $fillable = ['company_name', 'company_acronym', 'website_url', 'updated_at', 'status_id'];

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
     * One status for several companies
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * MANY-TO-ONE
     * Several users for a company
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several addresses for a company
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * MANY-TO-ONE
     * Several emails for a company
     */
    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    /**
     * MANY-TO-ONE
     * Several phones for a company
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * MANY-TO-ONE
     * Several bank codes for a company
     */
    public function bank_codes()
    {
        return $this->hasMany(BankCode::class);
    }

    /**
     * MANY-TO-ONE
     * Several social networks for a company
     */
    public function social_networks()
    {
        return $this->hasMany(SocialNetwork::class);
    }

    /**
     * MANY-TO-ONE
     * Several billing methods for a company
     */
    public function billing_methods()
    {
        return $this->hasMany(BillingMethod::class);
    }

    /**
     * MANY-TO-ONE
     * Several invoices for a company
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * MANY-TO-ONE
     * Several prepaid cards for a company
     */
    public function prepaid_cards()
    {
        return $this->hasMany(PrepaidCard::class);
    }

    /**
     * MANY-TO-ONE
     * Several offices for a company
     */
    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    /**
     * MANY-TO-ONE
     * Several albums for a company
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
