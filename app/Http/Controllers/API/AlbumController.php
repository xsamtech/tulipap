<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Resources\Album as ResourcesAlbum;

class AlbumController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $albums = Album::all();

        return $this->handleResponse(ResourcesAlbum::collection($albums), __('notifications.find_all_albums_success'));
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
            'album_name' => $request->album_name,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'message_id' => $request->message_id,
            'about_content_id' => $request->about_content_id,
            'service_id' => $request->service_id,
            'cart_id' => $request->cart_id
        ];

        // Validate required fields
        if ($inputs['album_name'] == null OR $inputs['album_name'] == ' ') {
            return $this->handleError($inputs['album_name'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null AND $inputs['message_id'] == null AND $inputs['about_content_id'] == null AND $inputs['service_id'] == null AND $inputs['cart_id'] == null) {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ' AND $inputs['message_id'] == ' ' AND $inputs['about_content_id'] == ' ' AND $inputs['service_id'] == ' ' AND $inputs['cart_id'] == ' ') {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

		if ($inputs['user_id'] != null) {
			// Select all user albums to check unique constraint
			$albums = Album::where('user_id', $inputs['user_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all company albums to check unique constraint
			$albums = Album::where('company_id', $inputs['company_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['message_id'] != null) {
			// Select all message albums to check unique constraint
			$albums = Album::where('message_id', $inputs['message_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['about_content_id'] != null) {
			// Select all about content albums to check unique constraint
			$albums = Album::where('about_content_id', $inputs['about_content_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['service_id'] != null) {
			// Select all service albums to check unique constraint
			$albums = Album::where('service_id', $inputs['service_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['cart_id'] != null) {
			// Select all cart albums to check unique constraint
			$albums = Album::where('cart_id', $inputs['cart_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

        $album = Album::create($inputs);

        return $this->handleResponse(new ResourcesAlbum($album), __('notifications.create_album_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $album = Album::find($id);

        if (is_null($album)) {
            return $this->handleError(__('notifications.find_album_404'));
        }

        return $this->handleResponse(new ResourcesAlbum($album), __('notifications.find_album_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Album $album)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'album_name' => $request->album_name,
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'message_id' => $request->message_id,
            'about_content_id' => $request->about_content_id,
            'service_id' => $request->service_id,
            'cart_id' => $request->cart_id,
            'updated_at' => now()
        ];

        if ($inputs['album_name'] == null OR $inputs['album_name'] == ' ') {
            return $this->handleError($inputs['album_name'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['company_id'] == null AND $inputs['message_id'] == null AND $inputs['about_content_id'] == null AND $inputs['service_id'] == null AND $inputs['cart_id'] == null) {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['company_id'] == ' ' AND $inputs['message_id'] == ' ' AND $inputs['about_content_id'] == ' ' AND $inputs['service_id'] == ' ' AND $inputs['cart_id'] == ' ') {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

		if ($inputs['user_id'] != null) {
			// Select all user albums and specific album to check unique constraint
			$albums = Album::where('user_id', $inputs['user_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['company_id'] != null) {
			// Select all company albums and specific album to check unique constraint
			$albums = Album::where('company_id', $inputs['company_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['message_id'] != null) {
			// Select all message albums and specific album to check unique constraint
			$albums = Album::where('message_id', $inputs['message_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['about_content_id'] != null) {
			// Select all about content albums and specific album to check unique constraint
			$albums = Album::where('about_content_id', $inputs['about_content_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['service_id'] != null) {
			// Select all service albums and specific album to check unique constraint
			$albums = Album::where('service_id', $inputs['service_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['cart_id'] != null) {
			// Select all cart albums and specific album to check unique constraint
			$albums = Album::where('cart_id', $inputs['cart_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

        $album->update($inputs);

        return $this->handleResponse(new ResourcesAlbum($album), __('notifications.update_album_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy(Album $album)
    {
        $album->delete();

        $albums = Album::all();

        return $this->handleResponse(ResourcesAlbum::collection($albums), __('notifications.delete_album_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select all albums belonging to a specific entity.
     *
     * @param  $entity
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function selectByEntity($entity, $id)
    {
        $albums = Album::where($entity . '_id', $id)->get();

        return $this->handleResponse(ResourcesAlbum::collection($albums), __('notifications.find_all_albums_success'));
    }
}
