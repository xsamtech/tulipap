<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Resources\Currency as ResourcesCurrency;

class CurrencyController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies = Currency::orderByDesc('name')->get();

        return $this->handleResponse(ResourcesCurrency::collection($currencies), __('notifications.find_all_currencies_success'));
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
            'name' => $request->name,
            'abbreviation' => $request->abbreviation,
            'country_id' => $request->country_id
        ];
        // Select all currencies to check unique constraint
        $currencies = Currency::all();

        // Validate required fields
        if ($inputs['name'] == null OR $inputs['name'] == ' ') {
            return $this->handleError($inputs['name'], __('validation.required'), 400);
        }

        if ($inputs['country_id'] == null OR $inputs['country_id'] == ' ') {
            return $this->handleError($inputs['country_id'], __('validation.required'), 400);
        }

        // Check if currency name already exists
        foreach ($currencies as $another_currency):
            if ($another_currency->name == $inputs['name']) {
                return $this->handleError($inputs['name'], __('validation.custom.currency_name.exists'), 400);
            }
        endforeach;

        $currency = Currency::create($inputs);

        return $this->handleResponse(new ResourcesCurrency($currency), __('notifications.create_currency_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $currency = Currency::find($id);

        if (is_null($currency)) {
            return $this->handleError(__('notifications.find_currency_404'));
        }

        return $this->handleResponse(new ResourcesCurrency($currency), __('notifications.find_currency_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Currency $currency)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'name' => $request->name,
            'abbreviation' => $request->abbreviation,
            'country_id' => $request->country_id,
            'updated_at' => now()
        ];
        // Select all currencies and specific currency to check unique constraint
        $currencies = Currency::all();
        $current_currency = Currency::find($inputs['id']);

        if ($inputs['name'] == null OR $inputs['name'] == ' ') {
            return $this->handleError($inputs['name'], __('validation.required'), 400);
        }

        if ($inputs['country_id'] == null OR $inputs['country_id'] == ' ') {
            return $this->handleError($inputs['country_id'], __('validation.required'), 400);
        }

        foreach ($currencies as $another_currency):
            if ($current_currency->name != $inputs['name']) {
                if ($another_currency->name == $inputs['name']) {
                    return $this->handleError($inputs['name'], __('validation.custom.currency_name.exists'), 400);
                }
            }
        endforeach;

        $currency->update($inputs);

        return $this->handleResponse(new ResourcesCurrency($currency), __('notifications.update_currency_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency)
    {
        $currency->delete();

        $currencies = Currency::all();

        return $this->handleResponse(ResourcesCurrency::collection($currencies), __('notifications.delete_currency_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a currency by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $currencies = Currency::search($data)->get();

        return $this->handleResponse(ResourcesCurrency::collection($currencies), __('notifications.find_all_currencies_success'));
    }
}
