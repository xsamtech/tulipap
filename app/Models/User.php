<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Searchable;

    const SEARCHABLE_FIELDS = ['customer_name'];

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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['registration_number', 'firstname', 'lastname', 'surname', 'gender', 'birthdate', 'customer_name', 'password_salt', 'password', 'password_visible', 'api_token', 'remember_token', 'last_connection', 'updated_at', 'status_id', 'type_id', 'company_id', 'office_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'password_salt', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_connection' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * ONE-TO-MANY
     * One status for several users
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One type for several users
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One company for several users
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ONE-TO-MANY
     * One office for several users
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * MANY-TO-ONE
     * Several role_users for a user
     */
    public function role_users()
    {
        return $this->hasMany(RoleUser::class);
    }

    /**
     * MANY-TO-ONE
     * Several addresses for a user
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * MANY-TO-ONE
     * Several emails for a user
     */
    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    /**
     * MANY-TO-ONE
     * Several phones for a user
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * MANY-TO-ONE
     * Several bank codes for a user
     */
    public function bank_codes()
    {
        return $this->hasMany(BankCode::class);
    }

    /**
     * MANY-TO-ONE
     * Several social_networks for a user
     */
    public function social_networks()
    {
        return $this->hasMany(SocialNetwork::class);
    }

    /**
     * MANY-TO-ONE
     * Several sessions for a user
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * ONE-TO-ONE
     * One preference for a user
     */
    public function preference()
    {
        return $this->hasOne(Preference::class);
    }

    /**
     * MANY-TO-ONE
     * Several messages for a user
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * MANY-TO-ONE
     * Several albums for a user
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    /**
     * MANY-TO-ONE
     * Several invoices for a user
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * MANY-TO-ONE
     * Several carts for a user
     */
    public function carts()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * MANY-TO-ONE
     * Several histories for a user
     */
    public function histories()
    {
        return $this->hasMany(History::class);
    }

    /**
     * MANY-TO-ONE
     * Several notifications for a user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
