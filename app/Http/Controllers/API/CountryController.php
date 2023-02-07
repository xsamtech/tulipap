<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Resources\Country as ResourcesCountry;

class CountryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $countries = Country::orderByDesc('country_name')->get();

        return $this->handleResponse(ResourcesCountry::collection($countries), __('notifications.find_all_countries_success'));
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
            'country_name' => $request->country_name,
            'country_abbreviation' => $request->country_abbreviation,
            'country_phone_code' => $request->country_phone_code,
            'region_id' => $request->region_id
        ];
        // Select all countries of a same region to check unique constraint
        $countries = Country::where('region_id', $inputs['region_id'])->get();

        // Validate required fields
        if ($inputs['country_name'] == null OR $inputs['country_name'] == ' ') {
            return $this->handleError($inputs['country_name'], __('validation.required'), 400);
        }

        if ($inputs['region_id'] == null OR $inputs['region_id'] == ' ') {
            return $this->handleError($inputs['region_id'], __('validation.required'), 400);
        }

        // Check if country name already exists
        foreach ($countries as $another_country):
            if ($another_country->country_name == $inputs['country_name']) {
                return $this->handleError($inputs['country_name'], __('validation.custom.country_name.exists'), 400);
            }
        endforeach;

        $country = Country::create($inputs);

        return $this->handleResponse(new ResourcesCountry($country), __('notifications.create_country_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $country = Country::find($id);

        if (is_null($country)) {
            return $this->handleError(__('notifications.find_country_404'));
        }

        return $this->handleResponse(new ResourcesCountry($country), __('notifications.find_country_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Country $country)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'country_name' => $request->country_name,
            'country_abbreviation' => $request->country_abbreviation,
            'country_phone_code' => $request->country_phone_code,
            'region_id' => $request->region_id,
            'updated_at' => now()
        ];
        // Select all countries of a same region and current country to check unique constraint
        $countries = Country::where('region_id', $inputs['region_id'])->get();
        $current_country = Country::find($inputs['id']);

        if ($inputs['country_name'] == null OR $inputs['country_name'] == ' ') {
            return $this->handleError($inputs['country_name'], __('validation.required'), 400);
        }

        if ($inputs['region_id'] == null OR $inputs['region_id'] == ' ') {
            return $this->handleError($inputs['region_id'], __('validation.required'), 400);
        }

        foreach ($countries as $another_country):
            if ($current_country->country_name != $inputs['country_name']) {
                if ($another_country->country_name == $inputs['country_name']) {
                    return $this->handleError($inputs['country_name'], __('validation.custom.country_name.exists'), 400);
                }
            }
        endforeach;

        $country->update($inputs);

        return $this->handleResponse(new ResourcesCountry($country), __('notifications.update_country_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
        $country->delete();

        $countries = Country::all();

        return $this->handleResponse(ResourcesCountry::collection($countries), __('notifications.delete_country_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a country by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $countries = Country::search($data)->get();

        return $this->handleResponse(ResourcesCountry::collection($countries), __('notifications.find_all_countries_success'));
    }
}
