<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\PasswordReset;
use Illuminate\Http\Request;
use App\Http\Resources\PasswordReset as ResourcesPasswordReset;
use Nette\Utils\Random;

class PasswordResetController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $password_resets = PasswordReset::orderByDesc('updated_at')->get();

        return $this->handleResponse(ResourcesPasswordReset::collection($password_resets), __('notifications.find_all_password_resets_success'));
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
            'email' => $request->email,
            'phone_code' => $request->phone_code,
            'phone_number' => $request->phone_number,
            'token' => Random::generate(7, '0-9'),
            'former_password' => $request->former_password
        ];

        // Validate required fields
        if ($inputs['email'] == null AND $inputs['phone_number'] == null) {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] == ' ' AND $inputs['phone_number'] == ' ') {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] == null AND $inputs['phone_number'] == ' ') {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] == ' ' AND $inputs['phone_number'] == null) {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] != null) {
            $existing_password_resets = PasswordReset::where('email', $inputs['email'])->get();

            if ($existing_password_resets != null) {
                $password_reset = PasswordReset::create($inputs);

                return $this->handleResponse([new ResourcesPasswordReset($password_reset), ResourcesPasswordReset::collection($existing_password_resets)], __('notifications.create_password_reset_success'));

            } else {
                $password_reset = PasswordReset::create($inputs);

                return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.create_password_reset_success'));
            }
        }

        if ($inputs['phone_number'] != null) {
            $existing_password_resets = PasswordReset::where([['phone_code', $inputs['phone_code']], ['phone_number', $inputs['phone_number']]])->get();

            if ($existing_password_resets != null) {
                $password_reset = PasswordReset::create($inputs);

                return $this->handleResponse([new ResourcesPasswordReset($password_reset), ResourcesPasswordReset::collection($existing_password_resets)], __('notifications.create_password_reset_success'));

            } else {
                $password_reset = PasswordReset::create($inputs);

                return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.create_password_reset_success'));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $password_reset = PasswordReset::find($id);

        if (is_null($password_reset)) {
            return $this->handleError(__('notifications.find_password_reset_404'));
        }

        return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.find_password_reset_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PasswordReset  $password_reset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PasswordReset $password_reset)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'email' => $request->email,
            'phone_code' => $request->phone_code,
            'phone_number' => $request->phone_number,
            'token' => Random::generate(7, '0-9'),
            'former_password' => $request->former_password,
            'updated_at' => now()
        ];

        if ($inputs['email'] == null AND $inputs['phone_number'] == null) {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] == ' ' AND $inputs['phone_number'] == ' ') {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] == null AND $inputs['phone_number'] == ' ') {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] == ' ' AND $inputs['phone_number'] == null) {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        $password_reset->update($inputs);

        return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.update_password_reset_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PasswordReset  $password_reset
     * @return \Illuminate\Http\Response
     */
    public function destroy(PasswordReset $password_reset)
    {
        $password_reset->delete();

        $password_resets = PasswordReset::all();

        return $this->handleResponse(ResourcesPasswordReset::collection($password_resets), __('notifications.delete_password_reset_success'));
    }
}
