<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Resources\Status as ResourcesStatus;

class StatusController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $statuses = Status::all();

        return $this->handleResponse(ResourcesStatus::collection($statuses), __('notifications.find_all_statuses_success'));
    }

    /**
     * Store a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'status_name' => $request->status_name,
            'status_description' => $request->status_description,
            'group_id' => $request->group_id
        ];
        // Select all statuses belonging to a group to check unique constraint
        $statuses = Status::where('group_id', $inputs['group_id'])->get();

        // Validate required fields
        if ($inputs['status_name'] == null OR $inputs['status_name'] == ' ') {
            return $this->handleError($inputs['status_name'], __('validation.required'), 400);
        }

        if ($inputs['group_id'] == null OR $inputs['group_id'] == ' ') {
            return $this->handleError($inputs['group_id'], __('validation.required'), 400);
        }

        // Check if status name already exists
        foreach ($statuses as $another_status):
            if ($another_status->status_name == $inputs['status_name']) {
                return $this->handleError($inputs['status_name'], __('validation.custom.status_name.exists'), 400);
            }
        endforeach;

        $status = Status::create($inputs);

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.create_status_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $status = Status::find($id);

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.find_status_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Status $status)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'status_name' => $request->status_name,
            'status_description' => $request->status_description,
            'group_id' => $request->group_id,
            'updated_at' => now()
        ];
        // Select all statuses and specific status to check unique constraint
        $statuses = Status::where('group_id', $inputs['group_id'])->get();
        $current_status = Status::find($inputs['id']);

        if ($inputs['status_name'] == null OR $inputs['status_name'] == ' ') {
            return $this->handleError($inputs['status_name'], __('validation.required'), 400);
        }

        if ($inputs['group_id'] == null OR $inputs['group_id'] == ' ') {
            return $this->handleError($inputs['group_id'], __('validation.required'), 400);
        }

        foreach ($statuses as $another_status):
            if ($current_status->status_name != $inputs['status_name']) {
                if ($another_status->status_name == $inputs['status_name']) {
                    return $this->handleError($inputs['status_name'], __('validation.custom.status_name.exists'), 400);
                }
            }
        endforeach;

        $status->update($inputs);

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.update_status_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function destroy(Status $status)
    {
        $status->delete();

        $statuses = Status::all();

        return $this->handleResponse(ResourcesStatus::collection($statuses), __('notifications.delete_status_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a status by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $statuses = Status::search($data)->get();

        return $this->handleResponse(ResourcesStatus::collection($statuses), __('notifications.find_all_statuses_success'));
    }
}
