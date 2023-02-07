<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Http\Resources\City as ResourcesCity;

class CityController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cities = City::orderByDesc('city_name')->get();

        return $this->handleResponse(ResourcesCity::collection($cities), __('notifications.find_all_cities_success'));
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
            'city_name' => $request->city_name,
            'province_id' => $request->province_id
        ];
        // Select all cities of a same province to check unique constraint
        $cities = City::where('province_id', $inputs['province_id'])->get();

        // Validate required fields
        if ($inputs['city_name'] == null OR $inputs['city_name'] == ' ') {
            return $this->handleError($inputs['city_name'], __('validation.required'), 400);
        }

        if ($inputs['province_id'] == null OR $inputs['province_id'] == ' ') {
            return $this->handleError($inputs['province_id'], __('validation.required'), 400);
        }

        // Check if city name already exists
        foreach ($cities as $another_city):
            if ($another_city->city_name == $inputs['city_name']) {
                return $this->handleError($inputs['city_name'], __('validation.custom.city_name.exists'), 400);
            }
        endforeach;

        $city = City::create($inputs);

        return $this->handleResponse(new ResourcesCity($city), __('notifications.create_city_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $city = City::find($id);

        if (is_null($city)) {
            return $this->handleError(__('notifications.find_city_404'));
        }

        return $this->handleResponse(new ResourcesCity($city), __('notifications.find_city_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, City $city)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'city_name' => $request->city_name,
            'province_id' => $request->province_id,
            'updated_at' => now()
        ];
        // Select all cities of a same province and current city to check unique constraint
        $cities = City::where('province_id', $inputs['province_id'])->get();
        $current_city = City::find($inputs['id']);

        // Validate required fields
        if ($inputs['city_name'] == null OR $inputs['city_name'] == ' ') {
            return $this->handleError($inputs['city_name'], __('validation.required'), 400);
        }

        if ($inputs['province_id'] == null OR $inputs['province_id'] == ' ') {
            return $this->handleError($inputs['province_id'], __('validation.required'), 400);
        }

        // Check if city name already exists
        foreach ($cities as $another_city):
            if ($current_city->city_name != $inputs['city_name']) {
                if ($another_city->city_name == $inputs['city_name']) {
                    return $this->handleError($inputs['city_name'], __('validation.custom.city_name.exists'), 400);
                }
            }
        endforeach;

        $city->update($inputs);

        return $this->handleResponse(new ResourcesCity($city), __('notifications.update_city_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function destroy(City $city)
    {
        $city->delete();

        $cities = City::all();

        return $this->handleResponse(ResourcesCity::collection($cities), __('notifications.delete_city_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a city by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $cities = City::search($data)->get();

        return $this->handleResponse(ResourcesCity::collection($cities), __('notifications.find_all_cities_success'));
    }

    /**
     * Search a city by its name with its province.
     *
     * @param  string $province_name
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchWithProvince($province_name, $data)
    {
        $province = Province::where('province_name', 'like', '%' . $province_name . '%')->first();
        $city = City::where([['city_name', 'like', '%' . $data . '%'], ['province_id', $province->id]])->first();

        return $this->handleResponse(new ResourcesCity($city), __('notifications.find_city_success'));
    }
}
