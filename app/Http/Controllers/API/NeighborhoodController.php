<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Area;
use App\Models\City;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use App\Http\Resources\Neighborhood as ResourcesNeighborhood;

class NeighborhoodController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $neighborhoods = Neighborhood::orderByDesc('neighborhood_name')->get();

        return $this->handleResponse(ResourcesNeighborhood::collection($neighborhoods), __('notifications.find_all_neighborhoods_success'));
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
            'neighborhood_name' => $request->neighborhood_name,
            'area_id' => $request->area_id,
            'office_id' => $request->office_id
        ];
        // Select all neighborhoods of a same area to check unique constraint
        $neighborhoods = Neighborhood::where('area_id', $inputs['area_id'])->get();

        // Validate required fields
        if ($inputs['neighborhood_name'] == null OR $inputs['neighborhood_name'] == ' ') {
            return $this->handleError($inputs['neighborhood_name'], __('validation.required'), 400);
        }

        if ($inputs['area_id'] == null OR $inputs['area_id'] == ' ') {
            return $this->handleError($inputs['area_id'], __('validation.required'), 400);
        }

        // Check if neighborhood name already exists
        foreach ($neighborhoods as $another_neighborhood):
            if ($another_neighborhood->neighborhood_name == $inputs['neighborhood_name']) {
                return $this->handleError($inputs['neighborhood_name'], __('validation.custom.neighborhood_name.exists'), 400);
            }
        endforeach;

        $neighborhood = Neighborhood::create($inputs);

        return $this->handleResponse(new ResourcesNeighborhood($neighborhood), __('notifications.create_neighborhood_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $neighborhood = Neighborhood::find($id);

        if (is_null($neighborhood)) {
            return $this->handleError(__('notifications.find_neighborhood_404'));
        }

        return $this->handleResponse(new ResourcesNeighborhood($neighborhood), __('notifications.find_neighborhood_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Neighborhood  $neighborhood
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Neighborhood $neighborhood)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'neighborhood_name' => $request->neighborhood_name,
            'area_id' => $request->area_id,
            'office_id' => $request->office_id,
            'updated_at' => now()
        ];
        // Select all neighborhoods of a same area and current neighborhood to check unique constraint
        $neighborhoods = Neighborhood::where('area_id', $inputs['area_id'])->get();
        $current_neighborhood = Neighborhood::find($inputs['id']);

        // Validate required fields
        if ($inputs['neighborhood_name'] == null OR $inputs['neighborhood_name'] == ' ') {
            return $this->handleError($inputs['neighborhood_name'], __('validation.required'), 400);
        }

        if ($inputs['area_id'] == null OR $inputs['area_id'] == ' ') {
            return $this->handleError($inputs['area_id'], __('validation.required'), 400);
        }

        // Check if neighborhood name already exists
        foreach ($neighborhoods as $another_neighborhood):
            if ($current_neighborhood->neighborhood_name != $inputs['neighborhood_name']) {
                if ($another_neighborhood->neighborhood_name == $inputs['neighborhood_name']) {
                    return $this->handleError($inputs['neighborhood_name'], __('validation.custom.neighborhood_name.exists'), 400);
                }
            }
        endforeach;

        $neighborhood->update($inputs);

        return $this->handleResponse(new ResourcesNeighborhood($neighborhood), __('notifications.update_neighborhood_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Neighborhood  $neighborhood
     * @return \Illuminate\Http\Response
     */
    public function destroy(Neighborhood $neighborhood)
    {
        $neighborhood->delete();

        $neighborhoods = City::all();

        return $this->handleResponse(ResourcesNeighborhood::collection($neighborhoods), __('notifications.delete_neighborhood_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a neighborhood by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $neighborhoods = Neighborhood::search($data)->get();

        return $this->handleResponse(ResourcesNeighborhood::collection($neighborhoods), __('notifications.find_all_neighborhoods_success'));
    }

    /**
     * Search a neighborhood by its name with the area and the city.
     *
     * @param  string $city_name
     * @param  string $area_name
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchWithAreaAndCity($city_name, $area_name, $data)
    {
        $city = City::where('city_name', 'like', '%' . $city_name . '%')->first();
        $area = Area::where([['area_name', 'like', '%' . $area_name . '%'], ['city_id', $city->id]])->first();
        $neighborhood = Neighborhood::where([['neighborhood_name', 'like', '%' . $data . '%'], ['area_id', $area->id]])->first();

        return $this->handleResponse(new ResourcesNeighborhood($neighborhood), __('notifications.find_neighborhood_success'));
    }
}
