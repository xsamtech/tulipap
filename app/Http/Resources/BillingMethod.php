<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillingMethod extends JsonResource
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
            'number_of_kilowatt_hours' => $this->number_of_kilowatt_hours,
            'price' => $this->price,
            'type' => Type::make($this->type),
            'currency' => Currency::make($this->currency),
            'icons' => Icon::collection($this->icons),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'company_id' => $this->company_id
        ];
    }
}
