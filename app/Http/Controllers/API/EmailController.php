<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Email;
use App\Models\Group;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Resources\Email as ResourcesEmail;

class EmailController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emails = Email::all();

        return $this->handleResponse(ResourcesEmail::collection($emails), __('notifications.find_all_emails_success'));
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
            'email_content' => $request->email_content,
            'status_id' => $secondary_status->id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id
        ];

        // Validate required fields
        if ($inputs['email_content'] == null OR $inputs['email_content'] == ' ') {
            return $this->handleError($inputs['email_content'], __('validation.required'), 400);
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

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

		if ($inputs['user_id'] != null) {
			// Select all user e-mails to check unique constraint
			$emails = Email::where('user_id', $inputs['user_id'])->get();

			// Check if e-mail already exists
			foreach ($emails as $another_email):
				if ($another_email->email_content == $inputs['email_content']) {
					return $this->handleError($inputs['email_content'], __('validation.custom.email.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all company e-mails to check unique constraint
			$emails = Email::where('company_id', $inputs['company_id'])->get();

			// Check if e-mail already exists
			foreach ($emails as $another_email):
				if ($another_email->email_content == $inputs['email_content']) {
					return $this->handleError($inputs['email_content'], __('validation.custom.email.exists'), 400);
				}
			endforeach;
		}

        $email = Email::create($inputs);

        return $this->handleResponse(new ResourcesEmail($email), __('notifications.create_email_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $email = Email::find($id);

        if (is_null($email)) {
            return $this->handleError(__('notifications.find_email_404'));
        }

        return $this->handleResponse(new ResourcesEmail($email), __('notifications.find_email_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Email $email)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'email_content' => $request->email_content,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'updated_at' => now()
        ];

        if ($inputs['email_content'] == null OR $inputs['email_content'] == ' ') {
            return $this->handleError($inputs['email_content'], __('validation.required'), 400);
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

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

		if ($inputs['user_id'] != null) {
			// Select all user e-mails and specific e-mail to check unique constraint
			$emails = Email::where('user_id', $inputs['user_id'])->get();
			$current_email = Email::find($inputs['id']);

			foreach ($emails as $another_email):
				if ($current_email->email_content != $inputs['email_content']) {
					if ($another_email->email_content == $inputs['email_content']) {
						return $this->handleError($inputs['email_content'], __('validation.custom.email.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all comapny e-mails and specific e-mail to check unique constraint
			$emails = Email::where('company_id', $inputs['company_id'])->get();
			$current_email = Email::find($inputs['id']);

			foreach ($emails as $another_email):
				if ($current_email->email_content != $inputs['email_content']) {
					if ($another_email->email_content == $inputs['email_content']) {
						return $this->handleError($inputs['email_content'], __('validation.custom.email.exists'), 400);
					}
				}
			endforeach;
		}

        $email->update($inputs);

        return $this->handleResponse(new ResourcesEmail($email), __('notifications.update_email_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function destroy(Email $email)
    {
        $email->delete();

        $emails = Email::all();

        return $this->handleResponse(ResourcesEmail::collection($emails), __('notifications.delete_email_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Change email status of entity to "Principal".
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
        // find email by given ID
        $email = Email::find($id);
        // find all emails to set status as secondary
        $emails = Email::where($entity . '_id', $entity_id)->get();

        // Update "status_id" column of other emails according to "$secondary_status" ID
        foreach ($emails as $email):
            $email->update([
                'status_id' => $secondary_status->id,
                'updated_at' => now()
            ]);
        endforeach;

        // Update "status_id" column of current email according to "$main_status" ID
        $email->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesEmail($email), __('notifications.update_email_success'));
    }

    /**
     * Change email status to "Secondaire".
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsSecondary($id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
        // find email by given ID
        $email = Email::find($id);

        // update "status_id" column according "$main_status" ID
        $email->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesEmail($email), __('notifications.update_email_success'));
    }
}
