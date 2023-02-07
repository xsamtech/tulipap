<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Notification as ResourcesNotification;

class NotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = Notification::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.find_all_notifications_success'));
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
            'notification_url' => $request->notification_url,
            'notification_content' => $request->notification_content,
            'user_id' => $request->user_id,
            'seller_id' => $request->seller_id,
            'third_party_app_id' => $request->third_party_app_id,
            'status_id' => $request->status_id
        ];

        // Validate required fields
        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        $validator = Validator::make($inputs, [
            'notification_url' => ['required'],
            'notification_content' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $notification = Notification::create($inputs);

        return $this->handleResponse(new ResourcesNotification($notification), __('notifications.create_notification_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notification = Notification::find($id);

        if (is_null($notification)) {
            return $this->handleError(__('notifications.find_notification_404'));
        }

        return $this->handleResponse(new ResourcesNotification($notification), __('notifications.find_notification_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notification $notification)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'notification_url' => $request->notification_url,
            'notification_content' => $request->notification_content,
            'user_id' => $request->user_id,
            'seller_id' => $request->seller_id,
            'third_party_app_id' => $request->third_party_app_id,
            'status_id' => $request->status_id,
            'updated_at' => now()
        ];

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        $validator = Validator::make($inputs, [
            'notification_url' => ['required'],
            'notification_content' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $notification->update($inputs);

        return $this->handleResponse(new ResourcesNotification($notification), __('notifications.update_notification_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        $notifications = Notification::all();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.delete_notification_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select all notifications given for a specific entity.
     *
     * @param  $entity
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function selectByEntity($entity, $id)
    {
        $notifications = Notification::where($entity . '_id', $id)->get();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.find_all_notifications_success'));
    }

    /**
     * Select all notifications with a specific status given for a specific entity.
     *
     * @param  $entity
     * @param  $id
     * @param  $status_id
     * @return \Illuminate\Http\Response
     */
    public function selectByEntityWithStatus($entity, $id, $status_id)
    {
        $notifications = Notification::where($entity . '_id', $id)->where('status_id', $status_id)->get();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.find_all_notifications_success'));
    }
}
