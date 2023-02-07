<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'surname' => $this->surname,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate,
            'customer_name' => $this->customer_name,
            'password_visible' => $this->password_visible,
            'api_token' => $this->api_token,
            'remember_token' => $this->remember_token,
            'last_connection' => $this->last_connection,
            'addresses' => Address::collection($this->addresses),
            'emails' => Email::collection($this->emails),
            'phones' => Phone::collection($this->phones),
            'bank_codes' => BankCode::collection($this->bank_codes),
            'social_networks' => SocialNetwork::collection($this->social_networks),
            'role_users' => RoleUser::collection($this->role_users),
            'status' => Status::make($this->status),
            'type' => Type::make($this->status),
            'invoices' => Invoice::collection($this->invoices),
            'carts' => Cart::collection($this->carts),
            'albums' => Album::collection($this->albums),
            'preference' => Preference::make($this->preference),
            'histories' => History::collection($this->histories),
            'notifications' => Notification::collection($this->notifications),
            'sessions' => Session::collection($this->sessions),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'status_id' => $this->status_id,
            'type_id' => $this->type_id,
            'company_id' => $this->company_id,
            'office_id' => $this->office_id
        ];
    }
}
