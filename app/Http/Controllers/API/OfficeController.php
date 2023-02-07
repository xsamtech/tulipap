<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Resources\Office as ResourcesOffice;
use Illuminate\Support\Facades\Validator;

class OfficeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $offices = Office::all();

        return $this->handleResponse(ResourcesOffice::collection($offices), __('notifications.find_all_offices_success'));
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
            'office_code' => $request->office_code,
            'office_name' => $request->office_name,
            'company_id' => $request->company_id
        ];
        // Select all offices belonging to a group to check unique constraint
        $offices = Office::where('company_id', $inputs['company_id'])->get();

        // Validate required fields
        $validator = Validator::make($inputs, [
            'office_code' => ['required'],
            'office_name' => ['required'],
            'company_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        // Check if office code already exists
        foreach ($offices as $another_office):
            if ($another_office->office_code == $inputs['office_code']) {
                return $this->handleError($inputs['office_code'], __('validation.custom.code.exists'), 400);
            }
        endforeach;

        $office = Office::create($inputs);

        return $this->handleResponse(new ResourcesOffice($office), __('notifications.create_office_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $office = Office::find($id);

        if (is_null($office)) {
            return $this->handleError(__('notifications.find_office_404'));
        }

        return $this->handleResponse(new ResourcesOffice($office), __('notifications.find_office_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Office  $office
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Office $office)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'office_code' => $request->office_code,
            'office_name' => $request->office_name,
            'company_id' => $request->company_id,
            'updated_at' => now()
        ];
        // Select all statuses and specific status to check unique constraint
        $offices = Office::where('company_id', $inputs['company_id'])->get();
        $current_office = Office::find($inputs['id']);

        // Validate required fields
        $validator = Validator::make($inputs, [
            'office_code' => ['required'],
            'office_name' => ['required'],
            'company_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        foreach ($offices as $another_office):
            if ($current_office->office_code != $inputs['office_code']) {
                if ($another_office->office_code == $inputs['office_code']) {
                    return $this->handleError($inputs['office_code'], __('validation.custom.code.exists'), 400);
                }
            }
        endforeach;

        $office->update($inputs);

        return $this->handleResponse(new ResourcesOffice($office), __('notifications.update_office_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Office  $office
     * @return \Illuminate\Http\Response
     */
    public function destroy(Office $office)
    {
        $office->delete();

        $offices = Office::all();

        return $this->handleResponse(ResourcesOffice::collection($offices), __('notifications.delete_office_success'));
    }
}
