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
            'type_id' => $request->type_id,
            'user_id' => $request->user_id
        ];

        // Validate required fields
        $validator = Validator::make($inputs, [
            'type_id' => ['required'],
            'user_id' => ['required']
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
            'type_id' => $request->type_id,
            'user_id' => $request->user_id,
            'updated_at' => now()
        ];

        $validator = Validator::make($inputs, [
            'type_id' => ['required'],
            'user_id' => ['required']
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
     * Select all histories by type.
     *
     * @param  $user_id
     * @param  $type_id
     * @return \Illuminate\Http\Response
     */
    public function selectByType($user_id, $type_id)
    {
        $histories = History::where([['user_id', $user_id], ['type_id', $type_id]])->get();

        return $this->handleResponse(ResourcesHistory::collection($histories), __('notifications.find_all_histories_success'));
    }
}
