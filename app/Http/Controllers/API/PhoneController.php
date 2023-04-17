<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Phone;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Resources\Phone as ResourcesPhone;

class PhoneController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $phones = Phone::all();

        return $this->handleResponse(ResourcesPhone::collection($phones), __('notifications.find_all_phones_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
		$secondary_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();
        // Get inputs
        $inputs = [
            'phone_code' => $request->phone_code,
            'phone_number' => $request->phone_number,
            'service_id' => $request->service_id,
            'status_id' => $secondary_status->id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'office_id' => $request->office_id
        ];

        // Validate required fields
        if ($inputs['phone_code'] == null OR $inputs['phone_code'] == ' ') {
            return $this->handleError($inputs['phone_code'], __('validation.required'), 400);
        }

        if ($inputs['phone_number'] == null OR $inputs['phone_number'] == ' ') {
            return $this->handleError($inputs['phone_number'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ' AND $inputs['office_id'] == ' ') {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null AND $inputs['office_id'] == ' ') {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == ' ' AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ' AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == null AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

		if ($inputs['user_id'] != null) {
			// Select all user phones to check unique constraint
			$phones = Phone::where('user_id', $inputs['user_id'])->get();

			// Check if phone number already exists
			foreach ($phones as $another_phone):
				if ($another_phone->phone_code == $inputs['phone_code'] AND $another_phone->phone_number == $inputs['phone_number']) {
					return $this->handleError($inputs['phone_number'], __('validation.custom.phone.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all company phones to check unique constraint
			$phones = Phone::where('company_id', $inputs['company_id'])->get();

			// Check if phone number already exists
			foreach ($phones as $another_phone):
				if ($another_phone->phone_code == $inputs['phone_code'] AND $another_phone->phone_number == $inputs['phone_number']) {
					return $this->handleError($inputs['phone_number'], __('validation.custom.phone.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['office_id'] != null) {
			// Select all office phones to check unique constraint
			$phones = Phone::where('office_id', $inputs['office_id'])->get();

			// Check if phone number already exists
			foreach ($phones as $another_phone):
				if ($another_phone->phone_code == $inputs['phone_code'] AND $another_phone->phone_number == $inputs['phone_number']) {
					return $this->handleError($inputs['phone_number'], __('validation.custom.phone.exists'), 400);
				}
			endforeach;
		}

        $phone = Phone::create($inputs);

        return $this->handleResponse(new ResourcesPhone($phone), __('notifications.create_phone_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $phone = Phone::find($id);

        if (is_null($phone)) {
            return $this->handleError(__('notifications.find_phone_404'));
        }

        return $this->handleResponse(new ResourcesPhone($phone), __('notifications.find_phone_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Phone  $phone
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Phone $phone)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'phone_code' => $request->phone_code,
            'phone_number' => $request->phone_number,
            'service_id' => $request->service_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'office_id' => $request->office_id,
            'updated_at' => now()
        ];

        if ($inputs['phone_code'] == null OR $inputs['phone_code'] == ' ') {
            return $this->handleError($inputs['phone_code'], __('validation.required'), 400);
        }

        if ($inputs['phone_number'] == null OR $inputs['phone_number'] == ' ') {
            return $this->handleError($inputs['phone_number'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ' AND $inputs['office_id'] == ' ') {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null AND $inputs['office_id'] == ' ') {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == ' ' AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ' AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == null AND $inputs['office_id'] == null) {
            return $this->handleError(__('validation.custom.phone.user_or_company_or_office.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

		if ($inputs['user_id'] != null) {
			// Select all user phones and specific phone to check unique constraint
			$phones = Phone::where('user_id', $inputs['user_id'])->get();
			$current_phone = Phone::find($inputs['id']);

			foreach ($phones as $another_phone):
				if ($current_phone->phone_number != $inputs['phone_number']) {
					if ($another_phone->phone_code == $inputs['phone_code'] AND $another_phone->phone_number == $inputs['phone_number']) {
						return $this->handleError($inputs['phone_number'], __('validation.custom.phone.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all comapny phones and specific phone to check unique constraint
			$phones = Phone::where('company_id', $inputs['company_id'])->get();
			$current_phone = Phone::find($inputs['id']);

			foreach ($phones as $another_phone):
				if ($current_phone->phone_number != $inputs['phone_number']) {
					if ($another_phone->phone_code == $inputs['phone_code'] AND $another_phone->phone_number == $inputs['phone_number']) {
						return $this->handleError($inputs['phone_number'], __('validation.custom.phone.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['office_id'] != null) {
			// Select all office phones and specific phone to check unique constraint
			$phones = Phone::where('office_id', $inputs['office_id'])->get();
			$current_phone = Phone::find($inputs['id']);

			foreach ($phones as $another_phone):
				if ($current_phone->phone_number != $inputs['phone_number']) {
					if ($another_phone->phone_code == $inputs['phone_code'] AND $another_phone->phone_number == $inputs['phone_number']) {
						return $this->handleError($inputs['phone_number'], __('validation.custom.phone.exists'), 400);
					}
				}
			endforeach;
		}

        $phone->update($inputs);

        return $this->handleResponse(new ResourcesPhone($phone), __('notifications.update_phone_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Phone  $phone
     * @return \Illuminate\Http\Response
     */
    public function destroy(Phone $phone)
    {
        $phone->delete();

        $phones = Phone::all();

        return $this->handleResponse(ResourcesPhone::collection($phones), __('notifications.delete_phone_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Change phone status of entity to "Principal".
     *
     * @param  $id
     * @param  $entity
     * @param  $entity_id
     * @return \Illuminate\Http\Response
     */
    public function markAsMain($id, $entity, $entity_id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
		$secondary_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();
        // find phone by given ID
        $phone = Phone::find($id);
        // find all phones to set status as secondary
        $phones = Phone::where($entity . '_id', $entity_id)->get();

        // Update "status_id" column of other phones according to "$secondary_status" ID
        foreach ($phones as $phone):
            $phone->update([
                'status_id' => $secondary_status->id,
                'updated_at' => now()
            ]);
        endforeach;

        // Update "status_id" column of current phone according to "$main_status" ID
        $phone->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesPhone($phone), __('notifications.update_phone_success'));
    }

    /**
     * Change phone status to "Secondaire".
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsSecondary($id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
        // find phone by given ID
        $phone = Phone::find($id);

        // update "status_id" column according "$main_status" ID
        $phone->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesPhone($phone), __('notifications.update_phone_success'));
    }
}
