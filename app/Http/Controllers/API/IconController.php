<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Icon;
use Illuminate\Http\Request;
use App\Http\Resources\Icon as ResourcesIcon;

class IconController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $icons = Icon::all();

        return $this->handleResponse(ResourcesIcon::collection($icons), __('notifications.find_all_icons_success'));
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
            'icon_name' => $request->icon_name,
            'icon_color' => $request->icon_color,
            'about_subject_id' => $request->about_subject_id,
            'currency_id' => $request->currency_id,
            'category_id' => $request->category_id,
            'role_id' => $request->role_id,
            'sector_id' => $request->sector_id,
            'service_id' => $request->service_id,
            'status_id' => $request->status_id,
            'subcategory_id' => $request->subcategory_id,
            'survey_element_id' => $request->survey_element_id,
            'type_id' => $request->type_id,
            'visibility_id' => $request->visibility_id,
            'country_id' => $request->country_id
        ];

        // Validate required fields
        if ($inputs['icon_name'] == null OR $inputs['icon_name'] == ' ') {
            return $this->handleError($inputs['icon_name'], __('validation.required'), 400);
        }

        if ($inputs['about_subject_id'] == null AND $inputs['category_id'] == null AND $inputs['currency_id'] == null AND $inputs['role_id'] == null AND $inputs['sector_id'] == null AND $inputs['service_id'] == null AND $inputs['status_id'] == null AND $inputs['subcategory_id'] == null AND $inputs['survey_element_id'] == null AND $inputs['type_id'] == null AND $inputs['visibility_id'] == null AND $inputs['country_id'] == null) {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

        if ($inputs['about_subject_id'] == ' ' AND $inputs['category_id'] == ' ' AND $inputs['currency_id'] == ' ' AND $inputs['role_id'] == ' ' AND $inputs['sector_id'] == ' ' AND $inputs['service_id'] == ' ' AND $inputs['status_id'] == ' ' AND $inputs['subcategory_id'] == ' ' AND $inputs['survey_element_id'] == ' ' AND $inputs['type_id'] == ' ' AND $inputs['visibility_id'] == ' ' AND $inputs['country_id'] == ' ') {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

		if ($inputs['about_subject_id'] != null) {
			// Select all about subject icons to check unique constraint
			$icons = Icon::where('about_subject_id', $inputs['about_subject_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['category_id'] != null) {
			// Select all category icons to check unique constraint
			$icons = Icon::where('category_id', $inputs['category_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['currency_id'] != null) {
			// Select all currency icons to check unique constraint
			$icons = Icon::where('currency_id', $inputs['currency_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['role_id'] != null) {
			// Select all role icons to check unique constraint
			$icons = Icon::where('role_id', $inputs['role_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['sector_id'] != null) {
			// Select all sector icons to check unique constraint
			$icons = Icon::where('sector_id', $inputs['sector_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['service_id'] != null) {
			// Select all service icons to check unique constraint
			$icons = Icon::where('service_id', $inputs['service_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['status_id'] != null) {
			// Select all status icons to check unique constraint
			$icons = Icon::where('status_id', $inputs['status_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['subcategory_id'] != null) {
			// Select all subcategory icons to check unique constraint
			$icons = Icon::where('subcategory_id', $inputs['subcategory_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['survey_element_id'] != null) {
			// Select all survey element icons to check unique constraint
			$icons = Icon::where('survey_element_id', $inputs['survey_element_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['type_id'] != null) {
			// Select all type icons to check unique constraint
			$icons = Icon::where('type_id', $inputs['type_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['visibility_id'] != null) {
			// Select all visibility icons to check unique constraint
			$icons = Icon::where('visibility_id', $inputs['visibility_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['country_id'] != null) {
			// Select all country icons to check unique constraint
			$icons = Icon::where('country_id', $inputs['country_id'])->get();

			// Check if icon name already exists
			foreach ($icons as $another_icon):
				if ($another_icon->icon_name == $inputs['icon_name']) {
					return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
				}
			endforeach;
		}

        $icon = Icon::create($inputs);

        return $this->handleResponse(new ResourcesIcon($icon), __('notifications.create_icon_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $icon = Icon::find($id);

        if (is_null($icon)) {
            return $this->handleError(__('notifications.find_icon_404'));
        }

        return $this->handleResponse(new ResourcesIcon($icon), __('notifications.find_icon_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Icon  $icon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Icon $icon)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'icon_name' => $request->icon_name,
            'icon_color' => $request->icon_color,
            'about_subject_id' => $request->about_subject_id,
            'currency_id' => $request->currency_id,
            'category_id' => $request->category_id,
            'role_id' => $request->role_id,
            'sector_id' => $request->sector_id,
            'service_id' => $request->service_id,
            'status_id' => $request->status_id,
            'subcategory_id' => $request->subcategory_id,
            'survey_element_id' => $request->survey_element_id,
            'type_id' => $request->type_id,
            'visibility_id' => $request->visibility_id,
            'country_id' => $request->country_id,
            'updated_at' => now()
        ];

        if ($inputs['icon_name'] == null OR $inputs['icon_name'] == ' ') {
            return $this->handleError($inputs['icon_name'], __('validation.required'), 400);
        }

		if ($inputs['about_subject_id'] != null) {
			// Select all about subject icons and specific icon to check unique constraint
			$icons = Icon::where('about_subject_id', $inputs['about_subject_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['category_id'] != null) {
			// Select all category icons and specific icon to check unique constraint
			$icons = Icon::where('category_id', $inputs['category_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['currency_id'] != null) {
			// Select all currency icons and specific icon to check unique constraint
			$icons = Icon::where('currency_id', $inputs['currency_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['role_id'] != null) {
			// Select all role icons and specific icon to check unique constraint
			$icons = Icon::where('role_id', $inputs['role_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['sector_id'] != null) {
			// Select all sector icons and specific icon to check unique constraint
			$icons = Icon::where('sector_id', $inputs['sector_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['service_id'] != null) {
			// Select all service icons and specific icon to check unique constraint
			$icons = Icon::where('service_id', $inputs['service_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['status_id'] != null) {
			// Select all status icons and specific icon to check unique constraint
			$icons = Icon::where('status_id', $inputs['status_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['subcategory_id'] != null) {
			// Select all subcategory icons and specific icon to check unique constraint
			$icons = Icon::where('subcategory_id', $inputs['subcategory_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['survey_element_id'] != null) {
			// Select all survey element icons and specific icon to check unique constraint
			$icons = Icon::where('survey_element_id', $inputs['survey_element_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['type_id'] != null) {
			// Select all type icons and specific icon to check unique constraint
			$icons = Icon::where('type_id', $inputs['type_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['visibility_id'] != null) {
			// Select all visibility icons and specific icon to check unique constraint
			$icons = Icon::where('visibility_id', $inputs['visibility_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['country_id'] != null) {
			// Select all country icons and specific icon to check unique constraint
			$icons = Icon::where('country_id', $inputs['country_id'])->get();
			$current_icon = Icon::find($inputs['id']);

			foreach ($icons as $another_icon):
				if ($current_icon->icon_name != $inputs['icon_name']) {
					if ($another_icon->icon_name == $inputs['icon_name']) {
						return $this->handleError($inputs['icon_name'], __('validation.custom.icon_name.exists'), 400);
					}
				}
			endforeach;
		}

        $icon->update($inputs);

        return $this->handleResponse(new ResourcesIcon($icon), __('notifications.update_icon_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Icon  $icon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Icon $icon)
    {
        $icon->delete();

        $icons = Icon::all();

        return $this->handleResponse(ResourcesIcon::collection($icons), __('notifications.delete_icon_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select all icons belonging to a specific entity.
     *
     * @param  $entity
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function selectByEntity($entity, $id)
    {
        $icons = Icon::where($entity . '_id', $id)->get();

        return $this->handleResponse(ResourcesIcon::collection($icons), __('notifications.find_all_icons_success'));
    }
}
