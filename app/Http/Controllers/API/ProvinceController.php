<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Country;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Http\Resources\Province as ResourcesProvince;

class ProvinceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $provinces = Province::orderByDesc('province_name')->get();

        return $this->handleResponse(ResourcesProvince::collection($provinces), __('notifications.find_all_provinces_success'));
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
            'province_name' => $request->province_name,
            'country_id' => $request->country_id
        ];
        // Select all provinces of a same country to check unique constraint
        $provinces = Province::where('country_id', $inputs['country_id'])->get();

        // Validate required fields
        if ($inputs['province_name'] == null OR $inputs['province_name'] == ' ') {
            return $this->handleError($inputs['province_name'], __('validation.required'), 400);
        }

        if ($inputs['country_id'] == null OR $inputs['country_id'] == ' ') {
            return $this->handleError($inputs['country_id'], __('validation.required'), 400);
        }

        // Check if province name already exists
        foreach ($provinces as $another_province):
            if ($another_province->province_name == $inputs['province_name']) {
                return $this->handleError($inputs['province_name'], __('validation.custom.province_name.exists'), 400);
            }
        endforeach;

        $province = Province::create($inputs);

        return $this->handleResponse(new ResourcesProvince($province), __('notifications.create_province_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $province = Province::find($id);

        if (is_null($province)) {
            return $this->handleError(__('notifications.find_province_404'));
        }

        return $this->handleResponse(new ResourcesProvince($province), __('notifications.find_province_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Province  $province
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Province $province)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'province_name' => $request->province_name,
            'country_id' => $request->country_id,
            'updated_at' => now()
        ];
        // Select all provinces of a same country and current province to check unique constraint
        $provinces = Province::where('country_id', $inputs['country_id'])->get();
        $current_province = Province::find($inputs['id']);

        // Validate required fields
        if ($inputs['province_name'] == null OR $inputs['province_name'] == ' ') {
            return $this->handleError($inputs['province_name'], __('validation.required'), 400);
        }

        if ($inputs['country_id'] == null OR $inputs['country_id'] == ' ') {
            return $this->handleError($inputs['country_id'], __('validation.required'), 400);
        }

        // Check if province name already exists
        foreach ($provinces as $another_province):
            if ($current_province->province_name != $inputs['province_name']) {
                if ($another_province->province_name == $inputs['province_name']) {
                    return $this->handleError($inputs['province_name'], __('validation.custom.province_name.exists'), 400);
                }
            }
        endforeach;

        $province->update($inputs);

        return $this->handleResponse(new ResourcesProvince($province), __('notifications.update_province_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Province  $province
     * @return \Illuminate\Http\Response
     */
    public function destroy(Province $province)
    {
        $province->delete();

        $provinces = Province::all();

        return $this->handleResponse(ResourcesProvince::collection($provinces), __('notifications.delete_province_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a province by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $provinces = Province::search($data)->get();

        return $this->handleResponse(ResourcesProvince::collection($provinces), __('notifications.find_all_provinces_success'));
    }

    /**
     * Search a city by its name with its country.
     *
     * @param  string $country_name
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchWithCountry($country_name, $data)
    {
        $group = Country::where('country_name', 'like', '%' . $country_name . '%')->first();
        $province = Province::where([['city_name', 'like', '%' . $data . '%'], ['group_id', $group->id]])->first();

        return $this->handleResponse(new ResourcesProvince($province), __('notifications.find_province_success'));
    }
}
