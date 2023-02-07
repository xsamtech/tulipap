<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\AboutTitle;
use Illuminate\Http\Request;
use App\Http\Resources\AboutTitle as ResourcesAboutTitle;

class AboutTitleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $about_titles = AboutTitle::all();

        return $this->handleResponse(ResourcesAboutTitle::collection($about_titles), __('notifications.find_all_about_titles_success'));
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
            'title' => $request->title,
            'about_subject_id' => $request->about_subject_id
        ];
        // Select all titles of a same subject to check unique constraint
        $about_titles = AboutTitle::where('about_subject_id', $inputs['about_subject_id'])->get();

        // Validate required fields
        if ($inputs['title'] == null OR $inputs['title'] == ' ') {
            return $this->handleError($inputs['title'], __('validation.required'), 400);
        }

        if ($inputs['about_subject_id'] == null OR $inputs['about_subject_id'] == ' ') {
            return $this->handleError($inputs['about_subject_id'], __('validation.required'), 400);
        }

        // Check if title already exists
        foreach ($about_titles as $another_about_title):
            if ($another_about_title->title == $inputs['title']) {
                return $this->handleError($inputs['title'], __('validation.custom.title.exists'), 400);
            }
        endforeach;

        $about_title = AboutTitle::create($inputs);

        return $this->handleResponse(new ResourcesAboutTitle($about_title), __('notifications.create_about_title_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $about_title = AboutTitle::find($id);

        if (is_null($about_title)) {
            return $this->handleError(__('notifications.find_about_title_404'));
        }

        return $this->handleResponse(new ResourcesAboutTitle($about_title), __('notifications.find_about_title_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AboutTitle  $about_title
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AboutTitle $about_title)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'title' => $request->title,
            'about_subject_id' => $request->about_subject_id,
            'updated_at' => now()
        ];
        // Select all titles of a same subject and current title to check unique constraint
        $about_titles = AboutTitle::where('about_subject_id', $inputs['about_subject_id'])->get();
        $current_about_title = AboutTitle::find($inputs['id']);

        // Validate required fields
        if ($inputs['title'] == null OR $inputs['title'] == ' ') {
            return $this->handleError($inputs['title'], __('validation.required'), 400);
        }

        if ($inputs['about_title_id'] == null OR $inputs['about_title_id'] == ' ') {
            return $this->handleError($inputs['about_title_id'], __('validation.required'), 400);
        }

        foreach ($about_titles as $another_about_title):
            if ($current_about_title->title != $inputs['title']) {
                if ($another_about_title->title == $inputs['title']) {
                    return $this->handleError($inputs['title'], __('validation.custom.title.exists'), 400);
                }
            }
        endforeach;

        $about_title->update($inputs);

        return $this->handleResponse(new ResourcesAboutTitle($about_title), __('notifications.update_about_title_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AboutTitle  $about_title
     * @return \Illuminate\Http\Response
     */
    public function destroy(AboutTitle $about_title)
    {
        $about_title->delete();

        $about_titles = AboutTitle::all();

        return $this->handleResponse(ResourcesAboutTitle::collection($about_titles), __('notifications.delete_about_title_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a title of subject about the app operation by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $about_titles = AboutTitle::search($data)->get();

        return $this->handleResponse(ResourcesAboutTitle::collection($about_titles), __('notifications.find_all_about_titles_success'));
    }
}
