<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Continent;
use Illuminate\Http\Request;
use App\Http\Resources\Continent as ResourcesContinent;

class ContinentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $continents = Continent::orderByDesc('continent_name')->get();

        return $this->handleResponse(ResourcesContinent::collection($continents), __('notifications.find_all_continents_success'));
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
            'continent_name' => $request->continent_name,
            'continent_abbreviation' => $request->continent_abbreviation
        ];
        // Select all continents to check unique constraint
        $continents = Continent::all();

        // Validate required fields
        if ($inputs['continent_name'] == null OR $inputs['continent_name'] == ' ') {
            return $this->handleError($inputs['continent_name'], __('validation.required'), 400);
        }

        // Check if continent name already exists
        foreach ($continents as $another_continent):
            if ($another_continent->continent_name == $inputs['continent_name']) {
                return $this->handleError($inputs['continent_name'], __('validation.custom.continent_name.exists'), 400);
            }
        endforeach;

        $continent = Continent::create($inputs);

        return $this->handleResponse(new ResourcesContinent($continent), __('notifications.create_continent_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $continent = Continent::find($id);

        if (is_null($continent)) {
            return $this->handleError(__('notifications.find_continent_404'));
        }

        return $this->handleResponse(new ResourcesContinent($continent), __('notifications.find_continent_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Continent  $continent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Continent $continent)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'continent_name' => $request->continent_name,
            'continent_abbreviation' => $request->continent_abbreviation,
            'updated_at' => now()
        ];
        // Select all continents and current continent to check unique constraint
        $continents = Continent::all();
        $current_continent = Continent::find($inputs['id']);

        // Validate required fields
        if ($inputs['continent_name'] == null OR $inputs['continent_name'] == ' ') {
            return $this->handleError($inputs['continent_name'], __('validation.required'), 400);
        }

        // Check if continent name already exists
        foreach ($continents as $another_continent):
            if ($current_continent->continent_name != $inputs['continent_name']) {
                if ($another_continent->continent_name == $inputs['continent_name']) {
                    return $this->handleError($inputs['continent_name'], __('validation.custom.continent_name.exists'), 400);
                }
            }
        endforeach;

        $continent->update($inputs);

        return $this->handleResponse(new ResourcesContinent($continent), __('notifications.update_continent_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Continent  $continent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Continent $continent)
    {
        $continent->delete();

        $continents = Continent::all();

        return $this->handleResponse(ResourcesContinent::collection($continents), __('notifications.delete_continent_success'));
    }
}
