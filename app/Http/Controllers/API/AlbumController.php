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
            'seller_id' => $request->seller_id,
            'sector_id' => $request->sector_id,
            'category_id' => $request->category_id,
            'partner_id' => $request->partner_id,
            'info_id' => $request->info_id,
            'service_id' => $request->service_id,
            'about_content_id' => $request->about_content_id,
            'third_party_app_id' => $request->third_party_app_id,
            'comment_id' => $request->comment_id,
            'seller_tender_id' => $request->seller_tender_id,
            'ad_id' => $request->ad_id,
            'message_id' => $request->message_id,
            'subcategory_id' => $request->subcategory_id,
            'message_group_id' => $request->message_group_id
        ];

        // Validate required fields
        if ($inputs['album_name'] == null OR $inputs['album_name'] == ' ') {
            return $this->handleError($inputs['album_name'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['sector_id'] == null AND $inputs['category_id'] == null AND $inputs['partner_id'] == null AND $inputs['info_id'] == null AND $inputs['service_id'] == null AND $inputs['about_content_id'] == null AND $inputs['third_party_app_id'] == null AND $inputs['comment_id'] == null AND $inputs['seller_tender_id'] == null AND $inputs['ad_id'] == null AND $inputs['message_id'] == null AND $inputs['subcategory_id'] == null AND $inputs['message_group_id'] == null) {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['sector_id'] == ' ' AND $inputs['category_id'] == ' ' AND $inputs['tender_id'] == ' ' AND $inputs['partner_id'] == ' ' AND $inputs['info_id'] == ' ' AND $inputs['service_id'] == ' ' AND $inputs['about_content_id'] == ' ' AND $inputs['third_party_app_id'] == ' ' AND $inputs['comment_id'] == ' ' AND $inputs['seller_tender_id'] == ' ' AND $inputs['ad_id'] == ' ' AND $inputs['message_id'] == ' ' AND $inputs['subcategory_id'] == ' ' AND $inputs['message_group_id'] == ' ') {
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

		if ($inputs['seller_id'] != null) {
			// Select all seller albums to check unique constraint
			$albums = Album::where('seller_id', $inputs['seller_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['sector_id'] != null) {
			// Select all sector albums to check unique constraint
			$albums = Album::where('sector_id', $inputs['sector_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['category_id'] != null) {
			// Select all category albums to check unique constraint
			$albums = Album::where('category_id', $inputs['category_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['partner_id'] != null) {
			// Select all partner albums to check unique constraint
			$albums = Album::where('partner_id', $inputs['partner_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['info_id'] != null) {
			// Select all info albums to check unique constraint
			$albums = Album::where('info_id', $inputs['info_id'])->get();

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

		if ($inputs['third_party_app_id'] != null) {
			// Select all third party app albums to check unique constraint
			$albums = Album::where('third_party_app_id', $inputs['third_party_app_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['comment_id'] != null) {
			// Select all comment albums to check unique constraint
			$albums = Album::where('comment_id', $inputs['comment_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['seller_tender_id'] != null) {
			// Select all tender albums to check unique constraint
			$albums = Album::where('seller_tender_id', $inputs['seller_tender_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['ad_id'] != null) {
			// Select all ad albums to check unique constraint
			$albums = Album::where('ad_id', $inputs['ad_id'])->get();

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

		if ($inputs['subcategory_id'] != null) {
			// Select all subcategory albums to check unique constraint
			$albums = Album::where('subcategory_id', $inputs['subcategory_id'])->get();

			// Check if album name already exists
			foreach ($albums as $another_album):
				if ($another_album->album_name == $inputs['album_name']) {
					return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
				}
			endforeach;
		}

		if ($inputs['message_group_id'] != null) {
			// Select all message group albums to check unique constraint
			$albums = Album::where('message_group_id', $inputs['message_group_id'])->get();

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
            'seller_id' => $request->seller_id,
            'sector_id' => $request->sector_id,
            'category_id' => $request->category_id,
            'partner_id' => $request->partner_id,
            'info_id' => $request->info_id,
            'service_id' => $request->service_id,
            'about_content_id' => $request->about_content_id,
            'third_party_app_id' => $request->third_party_app_id,
            'comment_id' => $request->comment_id,
            'seller_tender_id' => $request->seller_tender_id,
            'ad_id' => $request->ad_id,
            'message_id' => $request->message_id,
            'subcategory_id' => $request->subcategory_id,
            'message_group_id' => $request->message_group_id,
            'updated_at' => now()
        ];

        if ($inputs['album_name'] == null OR $inputs['album_name'] == ' ') {
            return $this->handleError($inputs['album_name'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null AND $inputs['seller_id'] == null AND $inputs['sector_id'] == null AND $inputs['category_id'] == null AND $inputs['partner_id'] == null AND $inputs['info_id'] == null AND $inputs['service_id'] == null AND $inputs['about_content_id'] == null AND $inputs['third_party_app_id'] == null AND $inputs['comment_id'] == null AND $inputs['seller_tender_id'] == null AND $inputs['ad_id'] == null AND $inputs['message_id'] == null AND $inputs['subcategory_id'] == null AND $inputs['message_group_id'] == null) {
            return $this->handleError(__('validation.custom.owner.required'), 400);
        }

        if ($inputs['user_id'] == ' ' AND $inputs['seller_id'] == ' ' AND $inputs['sector_id'] == ' ' AND $inputs['category_id'] == ' ' AND $inputs['tender_id'] == ' ' AND $inputs['partner_id'] == ' ' AND $inputs['info_id'] == ' ' AND $inputs['service_id'] == ' ' AND $inputs['about_content_id'] == ' ' AND $inputs['third_party_app_id'] == ' ' AND $inputs['comment_id'] == ' ' AND $inputs['seller_tender_id'] == ' ' AND $inputs['ad_id'] == ' ' AND $inputs['message_id'] == ' ' AND $inputs['subcategory_id'] == ' ' AND $inputs['message_group_id'] == ' ') {
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

		if ($inputs['seller_id'] != null) {
			// Select all seller albums and specific album to check unique constraint
			$albums = Album::where('seller_id', $inputs['seller_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['sector_id'] != null) {
			// Select all sector albums and specific album to check unique constraint
			$albums = Album::where('sector_id', $inputs['sector_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['category_id'] != null) {
			// Select all category albums and specific album to check unique constraint
			$albums = Album::where('category_id', $inputs['category_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['partner_id'] != null) {
			// Select all partner albums and specific album to check unique constraint
			$albums = Album::where('partner_id', $inputs['partner_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['info_id'] != null) {
			// Select all info albums and specific album to check unique constraint
			$albums = Album::where('info_id', $inputs['info_id'])->get();
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

		if ($inputs['third_party_app_id'] != null) {
			// Select all third party app albums and specific album to check unique constraint
			$albums = Album::where('third_party_app_id', $inputs['third_party_app_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['comment_id'] != null) {
			// Select all comment albums and specific album to check unique constraint
			$albums = Album::where('comment_id', $inputs['comment_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['seller_tender_id'] != null) {
			// Select all tender albums and specific album to check unique constraint
			$albums = Album::where('seller_tender_id', $inputs['seller_tender_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['ad_id'] != null) {
			// Select all ad albums and specific album to check unique constraint
			$albums = Album::where('ad_id', $inputs['ad_id'])->get();
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

		if ($inputs['subcategory_id'] != null) {
			// Select all subcategory albums and specific album to check unique constraint
			$albums = Album::where('subcategory_id', $inputs['subcategory_id'])->get();
			$current_album = Album::find($inputs['id']);

			foreach ($albums as $another_album):
				if ($current_album->album_name != $inputs['album_name']) {
					if ($another_album->album_name == $inputs['album_name']) {
						return $this->handleError($inputs['album_name'], __('validation.custom.album_name.exists'), 400);
					}
				}
			endforeach;
		}

		if ($inputs['message_group_id'] != null) {
			// Select all message group albums and specific album to check unique constraint
			$albums = Album::where('message_group_id', $inputs['message_group_id'])->get();
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
