<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Area;
use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Resources\Area as ResourcesArea;

class AreaController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $areas = Area::orderByDesc('area_name')->get();

        return $this->handleResponse(ResourcesArea::collection($areas), __('notifications.find_all_areas_success'));
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
            'area_name' => $request->area_name,
            'city_id' => $request->city_id
        ];
        // Select all areas of a same city to check unique constraint
        $areas = Area::where('city_id', $inputs['city_id'])->get();

        // Validate required fields
        if ($inputs['area_name'] == null OR $inputs['area_name'] == ' ') {
            return $this->handleError($inputs['area_name'], __('validation.required'), 400);
        }

        if ($inputs['city_id'] == null OR $inputs['city_id'] == ' ') {
            return $this->handleError($inputs['city_id'], __('validation.required'), 400);
        }

        // Check if area name already exists
        foreach ($areas as $another_area):
            if ($another_area->area_name == $inputs['area_name']) {
                return $this->handleError($inputs['area_name'], __('validation.custom.area_name.exists'), 400);
            }
        endforeach;

        $area = Area::create($inputs);

        return $this->handleResponse(new ResourcesArea($area), __('notifications.create_area_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $area = Area::find($id);

        if (is_null($area)) {
            return $this->handleError(__('notifications.find_area_404'));
        }

        return $this->handleResponse(new ResourcesArea($area), __('notifications.find_area_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Area $area)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'area_name' => $request->area_name,
            'city_id' => $request->city_id,
            'updated_at' => now()
        ];
        // Select all areas of a same city and current area to check unique constraint
        $areas = Area::where('city_id', $inputs['city_id'])->get();
        $current_area = Area::find($inputs['id']);

        if ($inputs['area_name'] == null OR $inputs['area_name'] == ' ') {
            return $this->handleError($inputs['area_name'], __('validation.required'), 400);
        }

        if ($inputs['city_id'] == null OR $inputs['city_id'] == ' ') {
            return $this->handleError($inputs['city_id'], __('validation.required'), 400);
        }

        // Check if area name already exists
        foreach ($areas as $another_area):
            if ($current_area->area_name != $inputs['area_name']) {
                if ($another_area->area_name == $inputs['area_name']) {
                    return $this->handleError($inputs['area_name'], __('validation.custom.area_name.exists'), 400);
                }
            }
        endforeach;

        $area->update($inputs);

        return $this->handleResponse(new ResourcesArea($area), __('notifications.update_area_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Area $area)
    {
        $area->delete();

        $areas = Area::all();

        return $this->handleResponse(ResourcesArea::collection($areas), __('notifications.delete_area_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search an area by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $areas = Area::search($data)->get();

        return $this->handleResponse(ResourcesArea::collection($areas), __('notifications.find_all_areas_success'));
    }

    /**
     * Search an area by its name with its city.
     *
     * @param  string $city_name
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchWithCity($city_name, $data)
    {
        $city = City::where('city_name', 'like', '%' . $city_name . '%')->first();
        $area = Area::where([['area_name', 'like', '%' . $data . '%'], ['city_id', $city->id]])->first();

        return $this->handleResponse(new ResourcesArea($area), __('notifications.find_area_success'));
    }
}
