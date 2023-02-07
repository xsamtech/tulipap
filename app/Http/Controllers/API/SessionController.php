<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Resources\Session as ResourcesSession;

class SessionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sessions = Session::all();

        return $this->handleResponse(ResourcesSession::collection($sessions), __('notifications.find_all_sessions_success'));
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
            'ip_address' => $request->ip_address,
            'user_agent' => $request->user_agent,
            'payload' => $request->payload,
            'last_activity' => $request->last_activity,
            'user_id' => $request->user_id
        ];

        $session = Session::create($inputs);

        return $this->handleResponse(new ResourcesSession($session), __('notifications.create_session_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $session = Session::find($id);

        if (is_null($session)) {
            return $this->handleError(__('notifications.find_session_404'));
        }

        return $this->handleResponse(new ResourcesSession($session), __('notifications.find_session_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Session $session)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'ip_address' => $request->ip_address,
            'user_agent' => $request->user_agent,
            'payload' => $request->payload,
            'last_activity' => $request->last_activity,
            'user_id' => $request->user_id,
            'updated_at' => now()
        ];

        $session->update($inputs);

        return $this->handleResponse(new ResourcesSession($session), __('notifications.update_session_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function destroy(Session $session)
    {
        $session->delete();

        $sessions = Session::all();

        return $this->handleResponse(ResourcesSession::collection($sessions), __('notifications.delete_session_success'));
    }
}
