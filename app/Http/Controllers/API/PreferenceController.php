<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\History;
use App\Models\Preference;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\Preference as ResourcesPreference;

class PreferenceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $preferences = Preference::all();

        return $this->handleResponse(ResourcesPreference::collection($preferences), __('notifications.find_all_preferences_success'));
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
            'dark_theme' => $request->dark_theme,
            'prefered_language' => $request->prefered_language,
            'login_verify' => $request->login_verify,
            'gps_location' => $request->gps_location,
            'email_confirmed' => $request->email_confirmed,
            'phone_confirmed' => $request->phone_confirmed,
            'user_id' => $request->user_id
        ];

        // Validate required fields
        if ($inputs['user_id'] == null OR $inputs['user_id'] == ' ') {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        $preference = Preference::create($inputs);

        return $this->handleResponse(new ResourcesPreference($preference), __('notifications.create_preference_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $preference = Preference::find($id);

        if (is_null($preference)) {
            return $this->handleError(__('notifications.find_preference_404'));
        }

        return $this->handleResponse(new ResourcesPreference($preference), __('notifications.find_preference_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Preference  $preference
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Preference $preference)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'dark_theme' => $request->dark_theme,
            'prefered_language' => $request->prefered_language,
            'login_verify' => $request->login_verify,
            'gps_location' => $request->gps_location,
            'email_confirmed' => $request->email_confirmed,
            'phone_confirmed' => $request->phone_confirmed,
            'user_id' => $request->user_id,
            'updated_at' => now()
        ];

        if ($inputs['user_id'] == null OR $inputs['user_id'] == ' ') {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        $preference->update($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $user = User::find($inputs['user_id']);

        History::create([
            'history_url' => 'account',
            'history_content' => __('notifications.you_updated_preference'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesPreference($preference), __('notifications.update_preference_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Preference  $preference
     * @return \Illuminate\Http\Response
     */
    public function destroy(Preference $preference)
    {
        $preference->delete();

        $preferences = Preference::all();

        return $this->handleResponse(ResourcesPreference::collection($preferences), __('notifications.delete_preference_success'));
    }
}
