<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\BillingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BillingMethod as ResourcesBillingMethod;

class BillingMethodController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $billing_methods = BillingMethod::all();

        return $this->handleResponse(ResourcesBillingMethod::collection($billing_methods), __('notifications.find_all_billing_methods_success'));
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
            'number_of_kilowatt_hours' => $request->number_of_kilowatt_hours,
            'price' => $request->price,
            'type_id' => $request->type_id,
            'currency_id' => $request->currency_id,
            'company_id' => $request->company_id
        ];
        // Select all billing methods to check unique constraint
        $billing_methods = BillingMethod::where([['type_id', $inputs['type_id']], ['company_id', $inputs['company_id']]])->get();

        // Validate required fields
        $validator = Validator::make($inputs, [
            'number_of_kilowatt_hours' => ['required'],
            'price' => ['required'],
            'type_id' => ['required'],
            'currency_id' => ['required'],
            'company_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        // Check if the billing method already exists
        foreach ($billing_methods as $another_billing_method):
            if ($another_billing_method->number_of_kilowatt_hours == $inputs['number_of_kilowatt_hours']) {
                return $this->handleError($inputs['number_of_kilowatt_hours'], __('validation.custom.billing_method.exists'), 400);
            }
        endforeach;

        $billing_method = BillingMethod::create($inputs);

        return $this->handleResponse(new ResourcesBillingMethod($billing_method), __('notifications.create_billing_method_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $billing_method = BillingMethod::find($id);

        if (is_null($billing_method)) {
            return $this->handleError(__('notifications.find_billing_method_404'));
        }

        return $this->handleResponse(new ResourcesBillingMethod($billing_method), __('notifications.find_billing_method_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BillingMethod  $billing_method
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BillingMethod $billing_method)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'number_of_kilowatt_hours' => $request->number_of_kilowatt_hours,
            'price' => $request->price,
            'type_id' => $request->type_id,
            'currency_id' => $request->currency_id,
            'company_id' => $request->company_id,
            'updated_at' => now()
        ];
        // Select all billing methods and specific billing method to check unique constraint
        $billing_methods = BillingMethod::where([['type_id', $inputs['type_id']], ['company_id', $inputs['company_id']]])->get();
        $current_billing_method = BillingMethod::find($inputs['id']);

        // Validate required fields
        $validator = Validator::make($inputs, [
            'number_of_kilowatt_hours' => ['required'],
            'price' => ['required'],
            'type_id' => ['required'],
            'currency_id' => ['required'],
            'company_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        // Check if the billing method already exists
        foreach ($billing_methods as $another_billing_method):
            if ($current_billing_method->number_of_kilowatt_hours != $inputs['number_of_kilowatt_hours']) {
                if ($another_billing_method->number_of_kilowatt_hours == $inputs['number_of_kilowatt_hours']) {
                    return $this->handleError($inputs['number_of_kilowatt_hours'], __('validation.custom.billing_method.exists'), 400);
                }
            }
        endforeach;

        $billing_method->update($inputs);

        return $this->handleResponse(new ResourcesBillingMethod($billing_method), __('notifications.update_billing_method_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BillingMethod  $billing_method
     * @return \Illuminate\Http\Response
     */
    public function destroy(BillingMethod $billing_method)
    {
        $billing_method->delete();

        $billing_methods = BillingMethod::all();

        return $this->handleResponse(ResourcesBillingMethod::collection($billing_methods), __('notifications.delete_billing_method_success'));
    }
}
