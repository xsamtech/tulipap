<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ExchangeRate as ResourcesExchangeRate;

class ExchangeRateController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $exchange_rates = ExchangeRate::all();

        return $this->handleResponse(ResourcesExchangeRate::collection($exchange_rates), __('notifications.find_all_exchange_rates_success'));
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
            'rate' => $request->rate,
            'currency1_id' => $request->currency1_id,
            'currency2_id' => $request->currency2_id
        ];

        // Validate required fields
        $validator = Validator::make($inputs, [
            'rate' => ['required', 'numeric'],
            'currency1_id' => ['required'],
            'currency2_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $exchange_rate = ExchangeRate::create($inputs);

        return $this->handleResponse(new ResourcesExchangeRate($exchange_rate), __('notifications.create_exchange_rate_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $exchange_rate = ExchangeRate::find($id);

        if (is_null($exchange_rate)) {
            return $this->handleError(__('notifications.find_exchange_rate_404'));
        }

        return $this->handleResponse(new ResourcesExchangeRate($exchange_rate), __('notifications.find_exchange_rate_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExchangeRate  $exchange_rate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExchangeRate $exchange_rate)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'rate' => $request->rate,
            'currency1_id' => $request->currency1_id,
            'currency2_id' => $request->currency2_id,
            'updated_at' => now()
        ];

        $validator = Validator::make($inputs, [
            'rate' => ['required', 'numeric'],
            'currency1_id' => ['required'],
            'currency2_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $exchange_rate->update($inputs);

        return $this->handleResponse(new ResourcesExchangeRate($exchange_rate), __('notifications.update_exchange_rate_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExchangeRate  $exchange_rate
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExchangeRate $exchange_rate)
    {
        $exchange_rate->delete();

        $exchange_rates = ExchangeRate::all();

        return $this->handleResponse(ResourcesExchangeRate::collection($exchange_rates), __('notifications.delete_exchange_rate_success'));
    }
}
