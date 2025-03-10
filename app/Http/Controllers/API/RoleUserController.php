<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\RoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\RoleUser as ResourcesRoleUser;

class RoleUserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role_users = RoleUser::all();

        return $this->handleResponse(ResourcesRoleUser::collection($role_users), __('notifications.find_all_role_users_success'));
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
            'role_id' => $request->role_id,
            'user_id' => $request->user_id,
            'selected' => $request->selected
        ];
        // Validate required fields
        $validator = Validator::make($inputs, [
            'role_id' => ['required'],
            'user_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $role_user = RoleUser::create($inputs);

        return $this->handleResponse(new ResourcesRoleUser($role_user), __('notifications.create_role_user_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role_user = RoleUser::find($id);

        if (is_null($role_user)) {
            return $this->handleError(__('notifications.find_role_user_404'));
        }

        return $this->handleResponse(new ResourcesRoleUser($role_user), __('notifications.find_role_user_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RoleUser  $role_user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RoleUser $role_user)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'role_id' => $request->role_id,
            'user_id' => $request->user_id,
            'selected' => $request->selected,
            'updated_at' => now()
        ];
        // Validate required fields
        $validator = Validator::make($inputs, [
            'role_id' => ['required'],
            'user_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $role_user->update($inputs);

        return $this->handleResponse(new ResourcesRoleUser($role_user), __('notifications.update_role_user_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RoleUser  $role_user
     * @return \Illuminate\Http\Response
     */
    public function destroy(RoleUser $role_user)
    {
        $role_user->delete();

        $role_users = RoleUser::all();

        return $this->handleResponse(ResourcesRoleUser::collection($role_users), __('notifications.delete_role_user_success'));
    }
}
