<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Resources\Type as ResourcesType;

class TypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = Type::all();

        return $this->handleResponse(ResourcesType::collection($types), __('notifications.find_all_types_success'));
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
            'type_name' => $request->type_name,
            'type_description' => $request->type_description,
            'group_id' => $request->group_id
        ];
        // Select all types to check unique constraint
        $types = Type::all();

        // Validate required fields
        if ($inputs['type_name'] == null OR $inputs['type_name'] == ' ') {
            return $this->handleError($inputs['type_name'], __('validation.required'), 400);
        }

        if ($inputs['group_id'] == null OR $inputs['group_id'] == ' ') {
            return $this->handleError($inputs['group_id'], __('validation.required'), 400);
        }

        // Check if type name already exists
        foreach ($types as $another_type):
            if ($another_type->type_name == $inputs['type_name']) {
                return $this->handleError($inputs['type_name'], __('validation.custom.type_name.exists'), 400);
            }
        endforeach;

        $type = Type::create($inputs);

        return $this->handleResponse(new ResourcesType($type), __('notifications.create_type_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $type = Type::find($id);

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        return $this->handleResponse(new ResourcesType($type), __('notifications.find_type_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Type $type)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'type_name' => $request->type_name,
            'type_description' => $request->type_description,
            'group_id' => $request->group_id,
            'updated_at' => now()
        ];
        // Select all types and specific type to check unique constraint
        $types = Type::all();
        $current_type = Type::find($inputs['id']);

        if ($inputs['type_name'] == null OR $inputs['type_name'] == ' ') {
            return $this->handleError($inputs['type_name'], __('validation.required'), 400);
        }

        if ($inputs['group_id'] == null OR $inputs['group_id'] == ' ') {
            return $this->handleError($inputs['group_id'], __('validation.required'), 400);
        }

        foreach ($types as $another_type):
            if ($current_type->type_name != $inputs['type_name']) {
                if ($another_type->type_name == $inputs['type_name']) {
                    return $this->handleError($inputs['type_name'], __('validation.custom.type_name.exists'), 400);
                }
            }
        endforeach;

        $type->update($inputs);

        return $this->handleResponse(new ResourcesType($type), __('notifications.update_type_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Type $type)
    {
        $type->delete();

        $types = Type::all();

        return $this->handleResponse(ResourcesType::collection($types), __('notifications.delete_type_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a group by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $types = Type::search($data)->get();

        return $this->handleResponse(ResourcesType::collection($types), __('notifications.find_all_types_success'));
    }
}
