<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Company extends JsonResource
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
            'company_name' => $this->company_name,
            'company_acronym' => $this->company_acronym,
            'website_url' => $this->website_url,
            'status' => Status::make($this->status),
            'users' => User::collection($this->users),
            'addresses' => Address::collection($this->addresses),
            'emails' => Email::collection($this->emails),
            'phones' => Phone::collection($this->phones),
            'bank_codes' => BankCode::collection($this->bank_codes),
            'social_networks' => SocialNetwork::collection($this->social_networks),
            'billing_methods' => BillingMethod::collection($this->billing_methods),
            'invoices' => Invoice::collection($this->invoices),
            'prepaid_cards' => PrepaidCard::collection($this->prepaid_cards),
            'offices' => Office::collection($this->offices),
            'albums' => Album::collection($this->albums),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
