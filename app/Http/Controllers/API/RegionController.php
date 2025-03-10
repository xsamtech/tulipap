<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Resources\Region as ResourcesRegion;

class RegionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $regions = Region::all();

        return $this->handleResponse(ResourcesRegion::collection($regions), __('notifications.find_all_regions_success'));
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
            'region_name' => $request->region_name,
            'region_description' => $request->region_description,
            'continent_id' => $request->continent_id
        ];
        // Select all regions of a same continent to check unique constraint
        $regions = Region::where('continent_id', $inputs['continent_id'])->get();

        // Validate required fields
        if ($inputs['region_name'] == null OR $inputs['region_name'] == ' ') {
            return $this->handleError($inputs['region_name'], __('validation.required'), 400);
        }

        if ($inputs['continent_id'] == null OR $inputs['continent_id'] == ' ') {
            return $this->handleError($inputs['continent_id'], __('validation.required'), 400);
        }

        // Check if region name already exists
        foreach ($regions as $another_region):
            if ($another_region->region_name == $inputs['region_name']) {
                return $this->handleError($inputs['region_name'], __('validation.custom.region_name.exists'), 400);
            }
        endforeach;

        $region = Region::create($inputs);

        return $this->handleResponse(new ResourcesRegion($region), __('notifications.create_region_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $region = Region::find($id);

        if (is_null($region)) {
            return $this->handleError(__('notifications.find_region_404'));
        }

        return $this->handleResponse(new ResourcesRegion($region), __('notifications.find_region_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Region $region)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'region_name' => $request->region_name,
            'region_description' => $request->region_description,
            'continent_id' => $request->continent_id,
            'updated_at' => now()
        ];
        // Select all regions of a same continent and current region to check unique constraint
        $regions = Region::where('continent_id', $inputs['continent_id'])->get();
        $current_region = Region::find($inputs['id']);

        // Validate required fields
        if ($inputs['region_name'] == null OR $inputs['region_name'] == ' ') {
            return $this->handleError($inputs['region_name'], __('validation.required'), 400);
        }

        if ($inputs['continent_id'] == null OR $inputs['continent_id'] == ' ') {
            return $this->handleError($inputs['continent_id'], __('validation.required'), 400);
        }

        // Check if region name already exists
        foreach ($regions as $another_region):
            if ($current_region->region_name != $inputs['region_name']) {
                if ($another_region->region_name == $inputs['region_name']) {
                    return $this->handleError($inputs['region_name'], __('validation.custom.region_name.exists'), 400);
                }
            }
        endforeach;

        $region->update($inputs);

        return $this->handleResponse(new ResourcesRegion($region), __('notifications.update_region_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function destroy(Region $region)
    {
        $region->delete();

        $regions = Region::all();

        return $this->handleResponse(ResourcesRegion::collection($regions), __('notifications.delete_region_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a region by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $regions = Region::search($data)->get();

        return $this->handleResponse(ResourcesRegion::collection($regions), __('notifications.find_all_regions_success'));
    }
}
