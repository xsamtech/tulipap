<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Address;
use App\Models\Area;
use App\Models\Group;
use App\Models\Neighborhood;
use App\Models\Status;
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
            'area_id' => $request->area_id,
            'neighborhood_id' => $request->neighborhood_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'office_id' => $request->office_id
        ];
        // Select all addresses of a same neighborhood and a same area to check unique constraint
        $addresses = Address::where([['neighborhood_id', $inputs['neighborhood_id']], ['area_id', $inputs['area_id']]])->get();

        // Validate required fields
        if ($request->neighborhood_id == null OR $request->neighborhood_id == ' ') {
            return $this->handleError($request->neighborhood_id, __('validation.required'), 400);
        }

        if ($request->area_id == null OR $request->area_id == ' ') {
            return $this->handleError($request->area_id, __('validation.required'), 400);
        }

        // Find area and neighborhood by their IDs to get their names
        $area = Area::find($inputs['area_id']);
        $neighborhood = Neighborhood::find($inputs['neighborhood_id']);

        // Check if address already exists
        foreach ($addresses as $another_address):
            if ($another_address->number == $inputs['number'] AND $another_address->street == $inputs['street'] AND $another_address->area_id == $inputs['area_id'] AND $another_address->neighborhood_id == $inputs['neighborhood_id']) {
                return $this->handleError(
                     __('notifications.address.number') . __('notifications.colon_after_word') . ' ' . $request->number . ', ' 
                    . __('notifications.address.street') . __('notifications.colon_after_word') . ' ' . $request->street . ', ' 
                    . __('notifications.address.neighborhood') . __('notifications.colon_after_word') . ' ' . $neighborhood->neighborhood_name . ', ' 
                    . __('notifications.address.area') . __('notifications.colon_after_word') . ' ' . $area->area_name, __('validation.custom.address.exists'), 400);
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
            'area_id' => $request->area_id,
            'neighborhood_id' => $request->neighborhood_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'office_id' => $request->office_id,
            'updated_at' => now()
        ];
        // Select all addresses of a same neighborhood and a same area. And select current address to check unique constraint
        $addresses = Address::where([['neighborhood_id', $inputs['neighborhood_id']], ['area_id', $inputs['area_id']]])->get();
        $current_address = Address::find($inputs['id']);

        if ($request->neighborhood_id == null OR $request->neighborhood_id == ' ') {
            return $this->handleError($request->neighborhood_id, __('validation.required'), 400);
        }

        if ($request->area_id == null OR $request->area_id == ' ') {
            return $this->handleError($request->area_id, __('validation.required'), 400);
        }

        // Find area and neighborhood by their IDs to get their names
        $area = Area::find($inputs['area_id']);
        $neighborhood = Neighborhood::find($inputs['neighborhood_id']);

        // Check if address already exists
        foreach ($addresses as $another_address):
            if ($current_address->area_id != $inputs['area_id'] AND $current_address->neighborhood_id != $inputs['neighborhood_id']) {
                if ($another_address->number == $inputs['number'] AND $another_address->street == $inputs['street'] AND $another_address->area_id == $inputs['area_id'] AND $another_address->neighborhood_id == $inputs['neighborhood_id']) {
                    return $this->handleError(
                         __('notifications.address.number') . __('notifications.colon_after_word') . ' ' . $request->number . ', ' 
                        . __('notifications.address.street') . __('notifications.colon_after_word') . ' ' . $request->street . ', ' 
                        . __('notifications.address.neighborhood') . __('notifications.colon_after_word') . ' ' . $neighborhood->neighborhood_name . ', ' 
                        . __('notifications.address.area') . __('notifications.colon_after_word') . ' ' . $area->area_name, __('validation.custom.address.exists'), 400);
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
     * Change address status of entity to "Principal".
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
        // find address by given ID
        $address = Address::find($id);
        // find all addresses to set status as secondary
        $addresses = Address::where($entity . '_id', $entity_id)->get();

        // Update "status_id" column of other addresses according to "$secondary_status" ID
        foreach ($addresses as $address):
            $address->update([
                'status_id' => $secondary_status->id,
                'updated_at' => now()
            ]);
        endforeach;

        // Update "status_id" column of current ad$address according to "$main_status" ID
        $address->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesAddress($address), __('notifications.update_address_success'));
    }

    /**
     * Change address status to "Secondaire".
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsSecondary($id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
        // find address by given ID
        $address = Address::find($id);

        // update "status_id" column according "$main_status" ID
        $address->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesAddress($address), __('notifications.update_address_success'));
    }
}
