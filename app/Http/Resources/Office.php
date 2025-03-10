<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Office extends JsonResource
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
            'office_code' => $this->office_code,
            'office_name' => $this->office_name,
            'users' => User::collection($this->users),
            'phones' => Phone::collection($this->phones),
            'neighborhoods' => Neighborhood::collection($this->neighborhoods),
            'address' => Address::make($this->address),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'company_id' => $this->company_id
        ];
    }
}
