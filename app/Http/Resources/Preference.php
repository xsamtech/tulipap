<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Preference extends JsonResource
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
            'prefered_theme' => $this->prefered_theme,
            'prefered_language' => $this->prefered_language,
            'login_verify' => $this->login_verify,
            'gps_location' => $this->gps_location,
            'email_confirmed' => $this->email_confirmed,
            'phone_confirmed' => $this->phone_confirmed,
            'email_notification' => EmailNotification::make($this->email_notification),
            'sms_notification' => SmsNotification::make($this->sms_notification),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user_id' => $this->user_id
        ];
    }
}
