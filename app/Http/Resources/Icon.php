<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Icon extends JsonResource
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
            'icon_name' => $this->icon_name,
            'icon_color' => $this->icon_color,
            'icon_status' => Status::make($this->icon_status),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'about_subject_id' => $this->about_subject_id,
            'status_id' => $this->status_id,
            'type_id' => $this->type_id,
            'service_id' => $this->service_id,
            'role_id' => $this->role_id,
            'currency_id' => $this->currency_id
        ];
    }
}
