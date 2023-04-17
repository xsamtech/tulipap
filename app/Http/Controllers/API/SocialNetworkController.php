<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\SocialNetwork;
use Illuminate\Http\Request;
use App\Http\Resources\SocialNetwork as ResourcesSocialNetwork;

class SocialNetworkController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $social_networks = SocialNetwork::all();

        return $this->handleResponse(ResourcesSocialNetwork::collection($social_networks), __('notifications.find_all_social_networks_success'));
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
            'network_name' => $request->network_name,
            'network_url' => $request->network_url,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id
        ];

        // Validate required fields
        if ($inputs['network_url'] == null OR $inputs['network_url'] == ' ') {
            return $this->handleError($inputs['network_url'], __('validation.required'), 400);
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

		if ($inputs['user_id'] != null) {
			// Select all user network URLs to check unique constraint
			$social_networks = SocialNetwork::where('user_id', $inputs['user_id'])->get();

			// Check if network URL already exists
			foreach ($social_networks as $another_social_network):
				if ($another_social_network->network_url == $inputs['network_url']) {
					return $this->handleError($inputs['network_url'], __('validation.custom.network_url.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all company network URLs to check unique constraint
			$social_networks = SocialNetwork::where('company_id', $inputs['company_id'])->get();

			// Check if network URL already exists
			foreach ($social_networks as $another_social_network):
				if ($another_social_network->network_url == $inputs['network_url']) {
					return $this->handleError($inputs['network_url'], __('validation.custom.network_url.exists'), 400);
				}
			endforeach;
		}

        $social_network = SocialNetwork::create($inputs);

        return $this->handleResponse(new ResourcesSocialNetwork($social_network), __('notifications.create_social_network_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $social_network = SocialNetwork::find($id);

        if (is_null($social_network)) {
            return $this->handleError(__('notifications.find_social_network_404'));
        }

        return $this->handleResponse(new ResourcesSocialNetwork($social_network), __('notifications.find_social_network_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SocialNetwork  $social_network
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SocialNetwork $social_network)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'network_name' => $request->network_name,
            'network_url' => $request->network_url,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'updated_at' => now()
        ];

        if ($inputs['network_url'] == null OR $inputs['network_url'] == ' ') {
            return $this->handleError($inputs['network_url'], __('validation.required'), 400);
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

		if ($inputs['user_id'] != null) {
			// Select all user network URLs and specific network URL to check unique constraint
			$social_networks = SocialNetwork::where('user_id', $inputs['user_id'])->get();
			$current_social_network = SocialNetwork::find($inputs['id']);

			foreach ($social_networks as $another_social_network):
				if ($current_social_network->network_url != $inputs['network_url']) {
					if ($another_social_network->network_url == $inputs['network_url']) {
						return $this->handleError($inputs['network_url'], __('validation.custom.network_url.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all comapny network URLs and specific network URL to check unique constraint
			$social_networks = SocialNetwork::where('company_id', $inputs['company_id'])->get();
			$current_social_network = SocialNetwork::find($inputs['id']);

			foreach ($social_networks as $another_social_network):
				if ($current_social_network->network_url != $inputs['network_url']) {
					if ($another_social_network->network_url == $inputs['network_url']) {
						return $this->handleError($inputs['network_url'], __('validation.custom.network_url.exists'), 400);
					}
				}
			endforeach;
		}

        $social_network->update($inputs);

        return $this->handleResponse(new ResourcesSocialNetwork($social_network), __('notifications.update_social_network_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SocialNetwork  $social_network
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialNetwork $social_network)
    {
        $social_network->delete();

        $social_networks = SocialNetwork::all();

        return $this->handleResponse(ResourcesSocialNetwork::collection($social_networks), __('notifications.delete_social_network_success'));
    }
}
