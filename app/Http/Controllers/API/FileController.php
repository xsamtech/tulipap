<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\File;
use App\Models\Group;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Resources\File as ResourcesFile;

class FileController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = File::all();

        return $this->handleResponse(ResourcesFile::collection($files), __('notifications.find_all_files_success'));
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
            'file_name' => $request->file_name,
            'file_url' => $request->file_url,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'album_id' => $request->album_id
        ];
        // Select all files to check unique constraint
        $files = File::where('album_id', $inputs['album_id'])->get();

        // Validate required fields
        if ($inputs['file_url'] == null OR $inputs['file_url'] == ' ') {
            return $this->handleError($inputs['file_url'], __('validation.required'), 400);
        }

        if ($inputs['type_id'] == null OR $inputs['type_id'] == ' ') {
            return $this->handleError($inputs['type_id'], __('validation.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        if ($inputs['album_id'] == null OR $inputs['album_id'] == ' ') {
            return $this->handleError($inputs['album_id'], __('validation.required'), 400);
        }

        // Check if file name already exists
        foreach ($files as $another_file):
            if ($another_file->file_url == $inputs['file_url']) {
                return $this->handleError($inputs['file_url'], __('validation.custom.file.exists'), 400);
            }
        endforeach;

        $file = File::create($inputs);

        return $this->handleResponse(new ResourcesFile($file), __('notifications.create_file_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $file = File::find($id);

        if (is_null($file)) {
            return $this->handleError(__('notifications.find_file_404'));
        }

        return $this->handleResponse(new ResourcesFile($file), __('notifications.find_file_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'file_name' => $request->file_name,
            'file_url' => $request->file_url,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'album_id' => $request->album_id,
            'updated_at' => now()
        ];
        // Select all files and specific file to check unique constraint
        $files = File::where('album_id', $inputs['album_id'])->get();
        $current_file = File::find($inputs['id']);

        if ($inputs['file_url'] == null OR $inputs['file_url'] == ' ') {
            return $this->handleError($inputs['file_url'], __('validation.required'), 400);
        }

        if ($inputs['type_id'] == null OR $inputs['type_id'] == ' ') {
            return $this->handleError($inputs['type_id'], __('validation.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        if ($inputs['album_id'] == null OR $inputs['album_id'] == ' ') {
            return $this->handleError($inputs['album_id'], __('validation.required'), 400);
        }

        foreach ($files as $another_file):
            if ($current_file->file_url != $inputs['file_url']) {
                if ($another_file->file_url == $inputs['file_url']) {
                    return $this->handleError($inputs['file_url'], __('validation.custom.file.exists'), 400);
                }
            }
        endforeach;

        $file->update($inputs);

        return $this->handleResponse(new ResourcesFile($file), __('notifications.update_file_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        $file->delete();

        $files = File::all();

        return $this->handleResponse(ResourcesFile::collection($files), __('notifications.delete_file_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Change file status to "Principal".
     *
     * @param  $id
     * @param  $album_id
     * @return \Illuminate\Http\Response
     */
    public function markAsMain($id, $album_id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
		$secondary_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();
        // find file by given ID
        $file = File::find($id);
        // find all files to set status as secondary
        $files = File::where('album_id', $album_id)->get();

        if ($secondary_status != null) {
            foreach ($files as $other_file):
                $other_file->update([
                    'status_id' => $secondary_status->id,
                    'updated_at' => now()
                ]);
            endforeach;

        // Otherwhise, create status with necessary name
        } else {
            $status = Status::create([
                'status_name' => 'Secondaire',
                'status_description' => 'Donnée cachée qui ne peut être vue que lorsqu\'on entre dans le dossier où elle se trouve (Exemple : Plusieurs autres photos d\'un album ou plusieurs autres numéro de téléphone d\'un utilisateur).).).',
                'group_id' => 1
            ]);

            foreach ($files as $other_file):
                $other_file->update([
                    'status_id' => $status->id,
                    'updated_at' => now()
                ]);
            endforeach;
        }

        // update "status_id" column according to "$main_status" ID
        $file->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesFile($file), __('notifications.update_file_success'));
    }

    /**
     * Change file status to "Secondaire".
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsSecondary($id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $main_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();
        // find file by given ID
        $file = File::find($id);

        // update "status_id" column according to "$main_status" ID
        $file->update([
            'status_id' => $main_status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesFile($file), __('notifications.update_file_success'));
    }
}
