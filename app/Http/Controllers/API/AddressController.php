<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Address;
use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Resources\Address as ResourcesAddress;

class AddressController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $addresses = Address::all();

        return $this->handleResponse(ResourcesAddress::collection($addresses), __('notifications.find_all_addresses_success'));
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
            'number' => $request->number,
            'street' => $request->street,
            'neighborhood' => $request->neighborhood,
            'area' => $request->area,
            'reference' => $request->reference,
            'city_id' => $request->city_id
        ];
        // Select all addresses of a same city to check unique constraint
        $addresses = Address::where('city_id', $inputs['city_id'])->get();

        // Validate required fields
        if ($inputs['area'] == null OR $inputs['area'] == ' ') {
            return $this->handleError($inputs['area'], __('validation.required'), 400);
        }

        if ($inputs['city_id'] == null OR $inputs['city_id'] == ' ') {
            return $this->handleError($inputs['city_id'], __('validation.required'), 400);
        }

		// Find city by ID to get city name
		$city = City::find($inputs['city_id']);

        // Check if address already exists
        foreach ($addresses as $another_address):
            if ($another_address->number == $inputs['number'] AND $another_address->street == $inputs['street'] AND $another_address->neighborhood == $inputs['neighborhood'] AND $another_address->area == $inputs['area']) {
                return $this->handleError(__('notifications.address.number') . __('notifications.colon_after_word') . ' ' . $inputs['number'] . ', ' 
					. __('notifications.address.street') . __('notifications.colon_after_word') . ' ' . $inputs['street'] . ', ' 
					. __('notifications.address.neighborhood') . __('notifications.colon_after_word') . ' ' . $inputs['neighborhood'] . ', ' 
					. __('notifications.address.area') . __('notifications.colon_after_word') . ' ' . $inputs['area'] . ', ' 
					. __('notifications.location.city.title') . __('notifications.colon_after_word') . ' ' . $city->city_name, 
				__('validation.custom.address.exists'), 400);
            }
        endforeach;

        $address = Address::create($inputs);

        return $this->handleResponse(new ResourcesAddress($address), __('notifications.create_address_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $address = Address::find($id);

        if (is_null($address)) {
            return $this->handleError(__('notifications.find_address_404'));
        }

        return $this->handleResponse(new ResourcesAddress($address), __('notifications.find_address_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Address $address)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'number' => $request->number,
            'street' => $request->street,
            'neighborhood' => $request->neighborhood,
            'area' => $request->area,
            'reference' => $request->reference,
            'city_id' => $request->city_id,
            'updated_at' => now()
        ];
        // Select all addresses of a same city and current address to check unique constraint
        $addresses = Address::where('city_id', $inputs['city_id'])->get();
        $current_address = Address::find($inputs['id']);

        // Validate required fields
        if ($inputs['area'] == null OR $inputs['area'] == ' ') {
            return $this->handleError($inputs['area'], __('validation.required'), 400);
        }

        if ($inputs['city_id'] == null OR $inputs['city_id'] == ' ') {
            return $this->handleError($inputs['city_id'], __('validation.required'), 400);
        }

		// Find city by ID to get city name
		$city = City::find($inputs['city_id']);

        // Check if address already exists
        foreach ($addresses as $another_address):
            if ($current_address->city_id != $inputs['city_id']) {
                if ($another_address->number == $inputs['number'] AND $another_address->street == $inputs['street'] AND $another_address->neighborhood == $inputs['neighborhood'] AND $another_address->area == $inputs['area']) {
                    return $this->handleError(__('notifications.address.number') . __('notifications.colon_after_word') . ' ' . $inputs['number'] . ', ' 
						. __('notifications.address.street') . __('notifications.colon_after_word') . ' ' . $inputs['street'] . ', ' 
						. __('notifications.address.neighborhood') . __('notifications.colon_after_word') . ' ' . $inputs['neighborhood'] . ', ' 
						. __('notifications.address.area') . __('notifications.colon_after_word') . ' ' . $inputs['area'] . ', ' 
						. __('notifications.location.city.title') . __('notifications.colon_after_word') . ' ' . $city->city_name, 
					__('validation.custom.address.exists'), 400);
                }
            }
        endforeach;

        $address->update($inputs);

        return $this->handleResponse(new ResourcesAddress($address), __('notifications.update_address_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function destroy(Address $address)
    {
        $address->delete();

        $addresses = Address::all();

        return $this->handleResponse(ResourcesAddress::collection($addresses), __('notifications.delete_address_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * GET: Search address by number / street / neighborhood / area / city.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $addresses = Address::where('number', 'like', '%' . $data . '%')->orWhere('street', 'like', '%' . $data . '%')->orWhere('neighborhood', 'like', '%' . $data . '%')->orWhere('area', 'like', '%' . $data . '%')->orWhere('city_id', 'like', '%' . $data . '%')->get();

        return $this->handleResponse(ResourcesAddress::collection($addresses), __('notifications.find_all_addresses_success'));
    }
}
