<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Album;
use App\Models\File;
use App\Models\Group;
use App\Models\Service;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Service as ResourcesService;

class ServiceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = Service::all();

        return $this->handleResponse(ResourcesService::collection($services), __('notifications.find_all_services_success'));
    }

    /**
     * Store a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'service_name' => $request->service_name,
            'provider' => $request->provider,
            'group_id' => $request->group_id
        ];
        // Select all services to check unique constraint
        $services = Service::where('group_id', $inputs['group_id'])->get;

        // Validate required fields
        if ($inputs['service_name'] == null OR $inputs['service_name'] == ' ') {
            return $this->handleError($inputs['service_name'], __('validation.required'), 400);
        }

        if ($inputs['group_id'] == null OR $inputs['group_id'] == ' ') {
            return $this->handleError($inputs['group_id'], __('validation.required'), 400);
        }

        // Check if service name already exists
        foreach ($services as $anotherService):
            if ($anotherService->service_name == $inputs['service_name']) {
                return $this->handleError($inputs['service_name'], __('validation.custom.service_name.exists'), 400);
            }
        endforeach;

        $service = Service::create($inputs);

        return $this->handleResponse(new ResourcesService($service), __('notifications.create_service_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $service = Service::find($id);

        if (is_null($service)) {
            return $this->handleError(__('notifications.find_service_404'));
        }

        return $this->handleResponse(new ResourcesService($service), __('notifications.find_service_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'service_name' => $request->service_name,
            'provider' => $request->provider,
            'group_id' => $request->group_id,
            'updated_at' => now()
        ];
        // Select all services and specific service to check unique constraint
        $services = Service::where('group_id', $inputs['group_id'])->get;
        $currentService = Service::find($inputs['id']);

        if ($inputs['service_name'] == null OR $inputs['service_name'] == ' ') {
            return $this->handleError($inputs['service_name'], __('validation.required'), 400);
        }

        if ($inputs['group_id'] == null OR $inputs['group_id'] == ' ') {
            return $this->handleError($inputs['group_id'], __('validation.required'), 400);
        }

        foreach ($services as $anotherService):
            if ($currentService->service_name != $inputs['service_name']) {
                if ($anotherService->service_name == $inputs['service_name']) {
                    return $this->handleError($inputs['service_name'], __('validation.custom.service_name.exists'), 400);
                }
            }
        endforeach;

        $service->update($inputs);

        return $this->handleResponse(new ResourcesService($service), __('notifications.update_service_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        $service->delete();

        $services = Service::all();

        return $this->handleResponse(ResourcesService::collection($services), __('notifications.delete_service_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a service by its name.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $services = Service::search($data)->get();

        return $this->handleResponse(ResourcesService::collection($services), __('notifications.find_all_services_success'));
    }

    /**
     * Search a service by its name with its group.
     *
     * @param  string $group_name
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchWithGroup($group_name, $data)
    {
        $group = Group::where('group_name', 'like', '%' . $group_name . '%')->first();
        $service = Service::where([['service_name', 'like', '%' . $data . '%'], ['group_id', $group->id]])->first();

        return $this->handleResponse(new ResourcesService($service), __('notifications.find_service_success'));
    }

    /**
     * Update service logo in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updateLogoPicture(Request $request, $id)
    {
        $inputs = [
            'service_id' => $request->entity_id,
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
		$logo_album = Album::where('album_name', 'Logos')->where('service_id', $inputs['service_id'])->first();
		// Find status by name to store its ID in "files" table
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
		$main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
		$secondary_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();

		if ($logo_album != null) {
            // Select all files to update their statuses
            $logo_images = File::where('album_id', $logo_album->id)->where('status_id', $main_status->id)->get();

			// If status with given name exist
			if ($secondary_status != null) {
                foreach ($logo_images as $logo):
                    $logo->update([
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

                    foreach ($logo_images as $logo):
                        $logo->update([
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

                    foreach ($logo_images as $logo):
                        $logo->update([
                            'status_id' => $status->id,
                            'updated_at' => now()
                        ]);
                    endforeach;
                }
			}

			// Create file name
			$file_name = 'images/services/' . $inputs['service_id'] . '/' . $logo_album->id . '/' . Str::random(50) . '.png';

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, base64_decode($image)));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $logo_album->id,
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
                            'album_id' => $logo_album->id,
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
                            'album_id' => $logo_album->id,
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
                        'album_id' => $logo_album->id,
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
                            'album_id' => $logo_album->id,
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
                            'album_id' => $logo_album->id,
                            'status_id' => $main_status->id
                        ]);
                    }
                }
			}

		} else {
			// Store album name in "albums" table
			$album = Album::create([
				'album_name' => 'Logos',
				'service_id' => $inputs['service_id']
			]);
			// Create file name
			$file_name = 'images/services/' . $inputs['service_id'] . '/' . $album->id . '/' . Str::random(50) . '.png';

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
			}
		}

		$service = Service::find($id);

        $service->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesService($service), __('notifications.update_service_success'));
    }
}
