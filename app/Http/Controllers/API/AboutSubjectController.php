<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\AboutSubject;
use App\Models\Group;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Resources\AboutSubject as ResourcesAboutSubject;

class AboutSubjectController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $about_subjects = AboutSubject::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesAboutSubject::collection($about_subjects), __('notifications.find_all_about_subjects_success'));
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
            'subject' => $request->subject,
            'subject_description' => $request->subject_description,
            'status_id' => $request->status_id
        ];

        // Validate required fields
        if ($inputs['subject'] == null OR $inputs['subject'] == ' ') {
            return $this->handleError($inputs['subject'], __('validation.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        $about_subject = AboutSubject::create($inputs);

        return $this->handleResponse(new ResourcesAboutSubject($about_subject), __('notifications.create_about_subject_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $about_subject = AboutSubject::find($id);

        if (is_null($about_subject)) {
            return $this->handleError(__('notifications.find_about_subject_404'));
        }

        return $this->handleResponse(new ResourcesAboutSubject($about_subject), __('notifications.find_about_subject_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AboutSubject  $about_subject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AboutSubject $about_subject)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'subject' => $request->subject,
            'subject_description' => $request->subject_description,
            'status_id' => $request->status_id,
            'updated_at' => now()
        ];

        if ($inputs['subject'] == null OR $inputs['subject'] == ' ') {
            return $this->handleError($inputs['subject'], __('validation.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        $about_subject->update($inputs);

        return $this->handleResponse(new ResourcesAboutSubject($about_subject), __('notifications.update_about_subject_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AboutSubject  $about_subject
     * @return \Illuminate\Http\Response
     */
    public function destroy(AboutSubject $about_subject)
    {
        $about_subject->delete();

        $about_subjects = AboutSubject::all();

        return $this->handleResponse(ResourcesAboutSubject::collection($about_subjects), __('notifications.delete_about_subject_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a subject by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $about_subject = AboutSubject::where('subject', $data)->first();

        return $this->handleResponse(new ResourcesAboutSubject($about_subject), __('notifications.find_about_subject_success'));
    }

    /**
     * Switch between about subject statuses.
     *
     * @param  $id
     * @param  $data
     * @return \Illuminate\Http\Response
     */
    public function switchStatus($id, $data)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $activated_status = Status::where([['status_name', 'Activé'], ['group_id', $functioning_group->id]])->first();
		$deactivated_status = Status::where([['status_name', 'Désactivé'], ['group_id', $functioning_group->id]])->first();
        // find about subject by ID
        $about_subject = AboutSubject::find($id);

        if ($about_subject->status_id == $deactivated_status->id) {
            // find all about subjects with the same name as current about subject
            $about_subjects = AboutSubject::where('subject', $data)->get();

            // If status already exists
            if ($deactivated_status != null) {
                foreach ($about_subjects as $other_about_subject):
                    $other_about_subject->update([
                        'status_id' => $deactivated_status->id,
                        'updated_at' => now()
                    ]);
                endforeach;

            // Otherwhise, create status with necessary name
            } else {
                $status = Status::create([
                    'status_name' => 'Désactivé',
                    'status_description' => 'Visibilité restreinte dans la plateforme ; et donc, impossibilité de faire des opérations.',
                    'group_id' => 1
                ]);

                foreach ($about_subjects as $other_about_subject):
                    $other_about_subject->update([
                        'status_id' => $status->id,
                        'updated_at' => now()
                    ]);
                endforeach;
            }

            // update "status_id" column according to "$activated_status" ID
            $about_subject->update([
                'status_id' => $activated_status->id,
                'updated_at' => now()
            ]);

        } else {
            // update "status_id" column according to "$deactivated_status" ID
            $about_subject->update([
                'status_id' => $deactivated_status->id,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesAboutSubject($about_subject), __('notifications.find_about_subject_success'));
    }
}
