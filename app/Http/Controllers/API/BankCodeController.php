<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\BankCode;
use Illuminate\Http\Request;
use App\Http\Resources\BankCode as ResourcesBankCode;

class BankCodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bank_codes = BankCode::all();

        return $this->handleResponse(ResourcesBankCode::collection($bank_codes), __('notifications.find_all_bank_codes_success'));
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
            'card_name' => $request->card_name,
            'card_number' => $request->card_number,
            'account_number' => $request->account_number,
            'expiration' => $request->expiration,
            'service_id' => $request->service_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id
        ];

        // Validate required fields
        if ($inputs['card_number'] == null OR $inputs['card_number'] == ' ') {
            return $this->handleError($inputs['card_number'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['user_id'] == ' ' OR $inputs['company_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['service_id'] == null OR $inputs['service_id'] == ' ') {
            return $this->handleError($inputs['service_id'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] != null) {
			// Select all bank codes to check unique constraint
			$bank_codes = BankCode::where('user_id', $inputs['user_id'])->get();

			// Check if card number already exists
            foreach ($bank_codes as $another_bank_code):
                if ($another_bank_code->card_number == $inputs['card_number']) {
                    return $this->handleError($inputs['card_number'], __('validation.custom.card_number.exists'), 400);
                }
            endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all bank codes to check unique constraint
			$bank_codes = BankCode::where('company_id', $inputs['company_id'])->get();

			// Check if card number already exists
            foreach ($bank_codes as $another_bank_code):
                if ($another_bank_code->card_number == $inputs['card_number']) {
                    return $this->handleError($inputs['card_number'], __('validation.custom.card_number.exists'), 400);
                }
            endforeach;
		}

        $bank_code = BankCode::create($inputs);

        return $this->handleResponse(new ResourcesBankCode($bank_code), __('notifications.create_bank_code_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $bank_code = BankCode::find($id);

        if (is_null($bank_code)) {
            return $this->handleError(__('notifications.find_bank_code_404'));
        }

        return $this->handleResponse(new ResourcesBankCode($bank_code), __('notifications.find_bank_code_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankCode  $bank_code
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankCode $bank_code)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'card_name' => $request->card_name,
            'card_number' => $request->card_number,
            'account_number' => $request->account_number,
            'expiration' => $request->expiration,
            'service_id' => $request->service_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'updated_at' => now()
        ];

        if ($inputs['card_number'] == null OR $inputs['card_number'] == ' ') {
            return $this->handleError($inputs['card_number'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['user_id'] == ' ' OR $inputs['company_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_company.required'));
        }

        if ($inputs['service_id'] == null OR $inputs['service_id'] == ' ') {
            return $this->handleError($inputs['service_id'], __('validation.required'), 400);
        }

		if ($inputs['user_id'] != null) {
			// Select all bank codes and specific bank code to check unique constraint
			$bank_codes = BankCode::where('user_id', $inputs['user_id'])->get();
			$current_bank_code = BankCode::find($inputs['id']);

			foreach ($bank_codes as $another_bank_code):
                if ($current_bank_code->card_number != $inputs['card_number']) {
                    if ($another_bank_code->card_number == $inputs['card_number']) {
                        return $this->handleError($inputs['card_number'], __('validation.custom.card_number.exists'), 400);
                    }
                }
            endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all bank codes and specific bank code to check unique constraint
			$bank_codes = BankCode::where('company_id', $inputs['company_id'])->get();
			$current_bank_code = BankCode::find($inputs['id']);

			foreach ($bank_codes as $another_bank_code):
                if ($current_bank_code->card_number != $inputs['card_number']) {
                    if ($another_bank_code->card_number == $inputs['card_number']) {
                        return $this->handleError($inputs['card_number'], __('validation.custom.card_number.exists'), 400);
                    }
                }
            endforeach;
		}

        $bank_code->update($inputs);

        return $this->handleResponse(new ResourcesBankCode($bank_code), __('notifications.update_bank_code_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankCode  $bank_code
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankCode $bank_code)
    {
        $bank_code->delete();

        $bank_codes = BankCode::all();

        return $this->handleResponse(ResourcesBankCode::collection($bank_codes), __('notifications.delete_bank_code_success'));
    }
}
