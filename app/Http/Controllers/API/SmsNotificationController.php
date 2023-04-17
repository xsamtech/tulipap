<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\SmsNotification;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SmsNotification as ResourcesSmsNotification;

class SmsNotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sms_notifications = SmsNotification::all();

        return $this->handleResponse(ResourcesSmsNotification::collection($sms_notifications), __('notifications.find_all_sms_notifications_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'update' => $request->update,
            'advertising' => $request->advertising,
            'communique' => $request->communique,
            'tips_tricks' => $request->tips_tricks,
            'preference_id' => $request->preference_id,
            'status_id' => $request->status_id
        ];
        // Validate required fields
        $validator = Validator::make($inputs, [
            'update' => ['required'],
            'advertising' => ['required'],
            'communique' => ['required'],
            'tips_tricks' => ['required'],
            'preference_id' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $sms_notification = SmsNotification::create($inputs);

        return $this->handleResponse(new ResourcesSmsNotification($sms_notification), __('notifications.create_sms_notification_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sms_notification = SmsNotification::find($id);

        if (is_null($sms_notification)) {
            return $this->handleError(__('notifications.find_sms_notification_404'));
        }

        return $this->handleResponse(new ResourcesSmsNotification($sms_notification), __('notifications.find_sms_notification_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SmsNotification  $sms_notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SmsNotification $sms_notification)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'update' => $request->update,
            'advertising' => $request->advertising,
            'communique' => $request->communique,
            'tips_tricks' => $request->tips_tricks,
            'preference_id' => $request->preference_id,
            'status_id' => $request->status_id,
            'updated_at' => now()
        ];
        // Validate required fields
        $validator = Validator::make($inputs, [
            'update' => ['required'],
            'advertising' => ['required'],
            'communique' => ['required'],
            'tips_tricks' => ['required'],
            'preference_id' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $sms_notification->update($inputs);

        return $this->handleResponse(new ResourcesSmsNotification($sms_notification), __('notifications.update_sms_notification_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SmsNotification  $sms_notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(SmsNotification $sms_notification)
    {
        $sms_notification->delete();

        $sms_notifications = SmsNotification::all();

        return $this->handleResponse(ResourcesSmsNotification::collection($sms_notifications), __('notifications.delete_sms_notification_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Switch between statuses for SMS notifications.
     *
     * @param  $id
     * @param  $data
     * @return \Illuminate\Http\Response
     */
    public function switchStatus($id, $data)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $status = Status::where([['status_name', 'like', '%' . $data . '%'], ['group_id', $functioning_group->id]])->first();
        $sms_notifications = SmsNotification::find($id);

        // update "status_id" column
        $sms_notifications->update([
            'status_id' => $status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesSmsNotification($sms_notifications), __('notifications.find_sms_notifications_success'));
    }
}
