<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\AboutContent;
use App\Models\Album;
use App\Models\File;
use App\Models\Group;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\AboutContent as ResourcesAboutContent;

class AboutContentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $about_contents = AboutContent::all();

        return $this->handleResponse(ResourcesAboutContent::collection($about_contents), __('notifications.find_all_about_contents_success'));
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
            'subtitle' => $request->subtitle,
            'content' => $request->content,
            'about_title_id' => $request->about_title_id
        ];
        // Select all contents of a same title to check unique constraint
        $about_contents = AboutContent::where('about_title_id', $inputs['about_title_id'])->get();

        // Validate required fields
        if ($inputs['content'] == null OR $inputs['content'] == ' ') {
            return $this->handleError($inputs['content'], __('validation.required'), 400);
        }

        if ($inputs['about_title_id'] == null OR $inputs['about_title_id'] == ' ') {
            return $this->handleError($inputs['about_title_id'], __('validation.required'), 400);
        }

        // Check if content already exists
        foreach ($about_contents as $another_about_content):
            if ($another_about_content->content == $inputs['content']) {
                return $this->handleError($inputs['content'], __('validation.custom.content.exists'), 400);
            }
        endforeach;

        $about_content = AboutContent::create($inputs);

        return $this->handleResponse(new ResourcesAboutContent($about_content), __('notifications.create_about_content_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $about_content = AboutContent::find($id);

        if (is_null($about_content)) {
            return $this->handleError(__('notifications.find_about_content_404'));
        }

        return $this->handleResponse(new ResourcesAboutContent($about_content), __('notifications.find_about_content_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AboutContent  $about_content
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AboutContent $about_content)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'subtitle' => $request->subtitle,
            'content' => $request->content,
            'about_title_id' => $request->about_title_id,
            'updated_at' => now()
        ];
        // Select all contents of a same title and current content to check unique constraint
        $about_contents = AboutContent::where('about_title_id', $inputs['about_title_id'])->get();
        $current_about_content = AboutContent::find($inputs['id']);

        // Validate required fields
        if ($inputs['content'] == null OR $inputs['content'] == ' ') {
            return $this->handleError($inputs['content'], __('validation.required'), 400);
        }

        if ($inputs['about_title_id'] == null OR $inputs['about_title_id'] == ' ') {
            return $this->handleError($inputs['about_title_id'], __('validation.required'), 400);
        }

        foreach ($about_contents as $another_about_content):
            if ($current_about_content->content != $inputs['content']) {
                if ($another_about_content->content == $inputs['content']) {
                    return $this->handleError($inputs['content'], __('validation.custom.content.exists'), 400);
                }
            }
        endforeach;

        $about_content->update($inputs);

        return $this->handleResponse(new ResourcesAboutContent($about_content), __('notifications.update_about_content_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AboutContent  $about_content
     * @return \Illuminate\Http\Response
     */
    public function destroy(AboutContent $about_content)
    {
        $about_content->delete();

        $about_contents = AboutContent::all();

        return $this->handleResponse(ResourcesAboutContent::collection($about_contents), __('notifications.delete_about_content_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a content of about by a string.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $about_contents = AboutContent::search($data)->get();

        return $this->handleResponse(ResourcesAboutContent::collection($about_contents), __('notifications.find_all_about_contents_success'));
    }

    /**
     * Update about content picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePicture(Request $request, $id)
    {
        $inputs = [
            'about_content_id' => $request->entity_id,
            'image_64' => $request->base64image
        ];
        // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
        $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
        // Find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $inputs['image_64']);
        $image = str_replace(' ', '+', $image);
		// Find type by name to get its ID
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
		$photo_type = Type::where([['type_name', 'Photo'], ['group_id', $file_type_group->id]])->first();
		// Find album by name to get its ID
		$representation_album = Album::where('album_name', 'Représentations')->where('about_content_id', $inputs['about_content_id'])->first();
		// Find status by name to store its ID in "files" table
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
		$main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
		$secondary_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();

		if ($representation_album != null) {
            // Select all files to update their statuses
            $representation_images = File::where('album_id', $representation_album->id)->where('status_id', $main_status->id)->get();

			// If status with given name exist
			if ($secondary_status != null) {
                foreach ($representation_images as $representation):
                    $representation->update([
                        'status_id' => $secondary_status->id,
                        'updated_at' => now()
                    ]);
                endforeach;
    
			// Otherwhise, create status with necessary name
			} else {
                if ($functioning_group != null) {
                    $status = Status::create([
                        'status_name' => 'Secondaire',
                        'status_description' => 'Donnée cachée qui ne peut être vue que lorsqu\'on entre dans le dossier où elle se trouve (Exemple : Plusieurs autres photos d\'un album ou plusieurs autres numéro de téléphone d\'un utilisateur).).).',
                        'group_id' => $functioning_group->id
                    ]);

                    foreach ($representation_images as $representation):
                        $representation->update([
                            'status_id' => $status->id,
                            'updated_at' => now()
                        ]);
                    endforeach;

                } else {
                    $group = Group::create([
                        'group_name' => 'Fonctionnement',
                        'group_description' => 'Grouper les états permettant aux utilisateurs et autres de fonctionner normalement, ou de manière restreinte, ou encore de ne pas fonctionner du tout.'
                    ]);
                    $status = Status::create([
                        'status_name' => 'Secondaire',
                        'status_description' => 'Donnée cachée qui ne peut être vue que lorsqu\'on entre dans le dossier où elle se trouve (Exemple : Plusieurs autres photos d\'un album ou plusieurs autres numéro de téléphone d\'un utilisateur).).).',
                        'group_id' => $group->id
                    ]);

                    foreach ($representation_images as $representation):
                        $representation->update([
                            'status_id' => $status->id,
                            'updated_at' => now()
                        ]);
                    endforeach;
                }
			}

			// Create file name
			$file_name = 'images/about_contents/' . $inputs['about_content_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.png';

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, base64_decode($image)));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $representation_album->id,
                            'status_id' => $main_status->id
                        ]);

                    } else {
                        $group = Group::create([
                            'group_name' => 'Type de fichier',
                            'group_description' => 'Grouper les types qui serviront à gérer les fichiers.'
                        ]);
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $representation_album->id,
                            'status_id' => $main_status->id
                        ]);
                    }
                }

			// Otherwhise, create status with necessary name
			} else {
				$status = Status::create([
					'status_name' => 'Principal',
					'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
					'group_id' => 1
				]);

                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $representation_album->id,
                            'status_id' => $status->id
                        ]);

                    } else {
                        $group = Group::create([
                            'group_name' => 'Type de fichier',
                            'group_description' => 'Grouper les types qui serviront à gérer les fichiers.'
                        ]);
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $representation_album->id,
                            'status_id' => $status->id
                        ]);
                    }
                }
			}

		} else {
			// Store album name in "albums" table
			$album = Album::create([
				'album_name' => 'Représentations',
				'about_content_id' => $inputs['about_content_id']
			]);
			// Create file name
			$file_name = 'images/about_contents/' . $inputs['about_content_id'] . '/' . $album->id . '/' . Str::random(50) . '.png';

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, base64_decode($image)));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $album->id,
                            'status_id' => $main_status->id
                        ]);

                    } else {
                        $group = Group::create([
                            'group_name' => 'Type de fichier',
                            'group_description' => 'Grouper les types qui serviront à gérer les fichiers.'
                        ]);
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $album->id,
                            'status_id' => $main_status->id
                        ]);
                    }
                }

			// Otherwhise, create status with necessary name
			} else {
				$status = Status::create([
					'status_name' => 'Principal',
					'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
					'group_id' => 1
				]);

                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $album->id,
                            'status_id' => $status->id
                        ]);

                    } else {
                        $group = Group::create([
                            'group_name' => 'Type de fichier',
                            'group_description' => 'Grouper les types qui serviront à gérer les fichiers.'
                        ]);
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $album->id,
                            'status_id' => $status->id
                        ]);
                    }
                }
			}
		}

		$about_content = AboutContent::find($id);

        $about_content->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesAboutContent($about_content), __('notifications.update_about_content_success'));
	}
}
