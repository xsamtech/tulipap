<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\History;
use App\Models\Keyword;
use App\Models\Preference;
use App\Models\Seller;
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
            'prefered_theme' => $request->prefered_theme,
            'prefered_language' => $request->prefered_language,
            'login_verify' => $request->login_verify,
            'video_autoplay' => $request->video_autoplay,
            'gps_location' => $request->gps_location,
            'findable' => $request->findable,
            'sensitive_content' => $request->sensitive_content,
            'age_limit_for_visibility' => $request->age_limit_for_visibility,
            'email_confirmed' => $request->email_confirmed,
            'phone_confirmed' => $request->phone_confirmed,
            'user_id' => $request->user_id,
            'seller_id' => $request->seller_id,
            'third_party_app_id' => $request->third_party_app_id
        ];

        // Validate required fields
        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
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
            'prefered_theme' => $request->prefered_theme,
            'prefered_language' => $request->prefered_language,
            'login_verify' => $request->login_verify,
            'video_autoplay' => $request->video_autoplay,
            'gps_location' => $request->gps_location,
            'findable' => $request->findable,
            'sensitive_content' => $request->sensitive_content,
            'age_limit_for_visibility' => $request->age_limit_for_visibility,
            'email_confirmed' => $request->email_confirmed,
            'phone_confirmed' => $request->phone_confirmed,
            'user_id' => $request->user_id,
            'seller_id' => $request->seller_id,
            'third_party_app_id' => $request->third_party_app_id,
            'updated_at' => now()
        ];

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        $preference->update($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        if ($inputs['user_id'] != null) {
            $user = User::find($inputs['user_id']);

            History::create([
                'history_url' => $user->username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        if ($inputs['seller_id'] != null) {
            $seller = Seller::find($inputs['seller_id']);

            History::create([
                'history_url' => $seller->seller_username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'seller_id' => $seller->id,
                'type_id' => $activities_history_type->id
            ]);
        }

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

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select preference datas given for a specific entity.
     *
     * @param  $entity
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function selectByEntity($entity, $id)
    {
        $preference = Preference::where($entity . '_id', $id)->first();

        return $this->handleResponse(new ResourcesPreference($preference), __('notifications.find_preference_success'));
    }

    /**
     * Switch between user or seller account.
     *
     * @param  $id
     * @param  $data
     * @return \Illuminate\Http\Response
     */
    public function switchTheme($id, $data)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $preference = Preference::find($id);

        // update "prefered_theme" column
        $preference->update([
            'prefered_theme' => $data,
            'updated_at' => now()
        ]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        if ($preference->user_id != null) {
            $user = User::find($preference->user_id);

            History::create([
                'history_url' => $user->username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        if ($preference->seller_id != null) {
            $seller = Seller::find($preference->seller_id);

            History::create([
                'history_url' => $seller->seller_username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'seller_id' => $seller->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        return $this->handleResponse(new ResourcesPreference($preference), __('notifications.find_preference_success'));
    }

    /**
     * Associate keywords to preference in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function associateKeywords(Request $request, $id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $preference = Preference::find($id);

        if ($request->word != null) {
            Keyword::create([
                'word' => $request->word,
                'preference_id' => $preference->id
            ]);
        }

        if ($request->keywords_ids != null) {
            foreach ($request->keywords_ids as $keyword_id):
                $keyword = Keyword::find($keyword_id);

                $keyword->update([
                    'preference_id' => $preference->id,
                    'updated_at' => now()
                ]);
            endforeach;
        }

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        if ($preference->user_id != null) {
            $user = User::find($preference->user_id);

            History::create([
                'history_url' => $user->username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        if ($preference->seller_id != null) {
            $seller = Seller::find($preference->seller_id);

            History::create([
                'history_url' => $seller->seller_username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'seller_id' => $seller->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        return $this->handleResponse(new ResourcesPreference($preference), __('notifications.update_preference_success'));
    }

    /**
     * Withdraw keywords from preference in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function withdrawKeywords(Request $request, $id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $preference = Preference::find($id);

        foreach ($request->keywords_ids as $keyword_id):
            $keyword = Keyword::find($keyword_id);

                $keyword->update([
                    'preference_id' => null,
                    'updated_at' => now()
                ]);
        endforeach;

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        if ($preference->user_id != null) {
            $user = User::find($preference->user_id);

            History::create([
                'history_url' => $user->username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        if ($preference->seller_id != null) {
            $seller = Seller::find($preference->seller_id);

            History::create([
                'history_url' => $seller->seller_username . '/preferences',
                'history_content' => __('notifications.you_updated_preference'),
                'seller_id' => $seller->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        return $this->handleResponse(new ResourcesPreference($preference), __('notifications.update_preference_success'));
    }
}
