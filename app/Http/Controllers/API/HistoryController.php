<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\History as ResourcesHistory;

class HistoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $histories = History::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesHistory::collection($histories), __('notifications.find_all_histories_success'));
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
            'history_url' => $request->history_url,
            'history_content' => $request->history_content,
            'user_id' => $request->user_id,
            'seller_id' => $request->seller_id,
            'third_party_app_id' => $request->third_party_app_id,
            'type_id' => $request->type_id
        ];

        // Validate required fields
        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        $validator = Validator::make($inputs, [
            'type_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $history = History::create($inputs);

        return $this->handleResponse(new ResourcesHistory($history), __('notifications.create_history_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $history = History::find($id);

        if (is_null($history)) {
            return $this->handleError(__('notifications.find_history_404'));
        }

        return $this->handleResponse(new ResourcesHistory($history), __('notifications.find_history_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, History $history)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'history_url' => $request->history_url,
            'history_content' => $request->history_content,
            'user_id' => $request->user_id,
            'seller_id' => $request->seller_id,
            'third_party_app_id' => $request->third_party_app_id,
            'type_id' => $request->type_id,
            'updated_at' => now()
        ];

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['third_party_app_id'] == null) {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['third_party_app_id'] == ' ') {
            return $this->handleError(__('validation.custom.user_or_seller_or_third_party_app.required'), 400);
        }

        $validator = Validator::make($inputs, [
            'type_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $history->update($inputs);

        return $this->handleResponse(new ResourcesHistory($history), __('notifications.update_history_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function destroy(History $history)
    {
        $history->delete();

        $histories = History::all();

        return $this->handleResponse(ResourcesHistory::collection($histories), __('notifications.delete_history_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select all histories given for a specific entity.
     *
     * @param  $entity
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function selectByEntity($entity, $id)
    {
        $histories = History::where($entity . '_id', $id)->get();

        return $this->handleResponse(ResourcesHistory::collection($histories), __('notifications.find_all_histories_success'));
    }

    /**
     * Select all histories with a specific type given for a specific entity.
     *
     * @param  $entity
     * @param  $id
     * @param  $type_id
     * @return \Illuminate\Http\Response
     */
    public function selectByEntityWithType($entity, $id, $type_id)
    {
        $histories = History::where($entity . '_id', $id)->where('type_id', $type_id)->get();

        return $this->handleResponse(ResourcesHistory::collection($histories), __('notifications.find_all_histories_success'));
    }
}
