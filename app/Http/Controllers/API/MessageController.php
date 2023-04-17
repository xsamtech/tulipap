<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Album;
use App\Models\File;
use App\Models\Group;
use App\Models\Message;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Message as ResourcesMessage;

class MessageController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $messages = Message::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
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
            'message_subject' => $request->message_subject,
            'message_content' => $request->message_content,
            'sent_to' => $request->sent_to,
            'answered_for' => $request->answered_for,
            'last_status' => $request->last_status,
            'status_given_by' => $request->status_given_by,
            'status_id' => $request->status_id,
            'type_id' => $request->type_id,
            'user_id' => $request->user_id
        ];

        $message = Message::create($inputs);

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.create_message_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $message = Message::find($id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.find_message_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'message_subject' => $request->message_subject,
            'message_content' => $request->message_content,
            'sent_to' => $request->sent_to,
            'answered_for' => $request->answered_for,
            'last_status' => $request->last_status,
            'status_given_by' => $request->status_given_by,
            'status_id' => $request->status_id,
            'type_id' => $request->type_id,
            'user_id' => $request->user_id,
            'updated_at' => now()
        ];

        $message->update($inputs);

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.update_message_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        $message->delete();

        $messages = Message::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.delete_message_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a message by its content.
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $messages = Message::search($data)->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
    }

    /**
     * GET: Display all received messages.
     *
     * @param  $entity
     * @return \Illuminate\Http\Response
     */
    public function inbox($entity)
    {
        $msg_type_group = Group::where('group_name', 'Type de fichier')->first();
        $msg_group = Group::where('group_name', 'Message')->first();

        if ($msg_type_group != null AND $msg_group != null) {
            $private_msg_type = Type::where([['type_name', 'Message privé'], ['group_id', $msg_type_group->id]])->first();
            $read_status = Status::where([['status_name', 'Lu'], ['group_id', $msg_group->id]])->first();
            $unread_status = Status::where([['status_name', 'Non lu'], ['group_id', $msg_group->id]])->first();

            if ($private_msg_type != null AND $read_status != null AND $unread_status != null) {
                $messages = Message::where([['sent_to', $entity], ['type_id', $private_msg_type->id], ['status_id', $read_status->id]])->orWhere([['sent_to', $entity], ['type_id', $private_msg_type->id], ['status_id', $unread_status->id]])->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));

            } else {
                $messages = Message::where('sent_to', $entity)->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
            }

        } else {
            $messages = Message::where('sent_to', $entity)->get();

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
        }
    }

    /**
     * GET: Display all received and unread messages.
     *
     * @param  $entity
     * @return \Illuminate\Http\Response
     */
    public function unreadInbox($entity)
    {
        $msg_type_group = Group::where('group_name', 'Type de fichier')->first();
        $msg_group = Group::where('group_name', 'Message')->first();

        if ($msg_type_group != null AND $msg_group != null) {
            $private_msg_type = Type::where([['type_name', 'Message privé'], ['group_id', $msg_type_group->id]])->first();
            $unread_status = Status::where([['status_name', 'Non lu'], ['group_id', $msg_group->id]])->first();

            if ($private_msg_type != null AND $unread_status != null) {
                $messages = Message::where([['sent_to', $entity], ['type_id', $private_msg_type->id],['status_id', $unread_status->id]])->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));

            } else {
                $messages = Message::where('sent_to', $entity)->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
            }

        } else {
            $messages = Message::where('sent_to', $entity)->get();

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
        }
    }

    /**
     * GET: Display all received messages and marked as "Spam".
     *
     * @param  $entity
     * @return \Illuminate\Http\Response
     */
    public function spams($entity)
    {
        $msg_type_group = Group::where('group_name', 'Type de fichier')->first();
        $msg_group = Group::where('group_name', 'Message')->first();

        if ($msg_type_group != null AND $msg_group != null) {
            $private_msg_type = Type::where([['type_name', 'Message privé'], ['group_id', $msg_type_group->id]])->first();
            $read_status = Status::where([['status_name','Lu'], ['group_id', $msg_group->id]])->first();
            $spam_status = Status::where([['status_name', 'Spam'], ['group_id', $msg_group->id]])->first();

            if ($private_msg_type != null AND $read_status != null AND $spam_status != null) {
                $messages = Message::where([['sent_to', $entity], ['type_id', $private_msg_type->id], ['status_id', $spam_status->id]])->orWhere([['sent_to', $entity], ['type_id', $private_msg_type->id], ['last_status', $spam_status->id], ['status_id', $read_status->id]])->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));

            } else {
                $messages = Message::where('sent_to', $entity)->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
            }

        } else {
            $messages = Message::where('sent_to', $entity)->get();

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
        }
    }

    /**
     * GET: Display all sent messages.
     *
     * @param  $user_id
     * @return \Illuminate\Http\Response
     */
    public function outbox($user_id)
    {
        $msg_type_group = Group::where('group_name', 'Type de fichier')->first();
        $msg_group = Group::where('group_name', 'Message')->first();

        if ($msg_type_group != null AND $msg_group != null) {
            $private_msg_type = Type::where([['type_name', 'Message privé'], ['group_id', $msg_type_group->id]])->first();
            $read_status = Status::where([['status_name','Lu'], ['group_id', $msg_group->id]])->first();
            $unread_status = Status::where([['status_name', 'Non lu'], ['group_id', $msg_group->id]])->first();
            $spam_status = Status::where([['status_name', 'Spam'], ['group_id', $msg_group->id]])->first();
            $zip_status = Status::where([['status_name', 'Archivé'], ['group_id', $msg_group->id]])->first();
            $to_recycle_bin_status = Status::where([['status_name', 'Supprimé'], ['group_id', $msg_group->id]])->first();

            if ($private_msg_type != null AND $read_status != null AND $unread_status != null AND $spam_status != null AND $zip_status != null AND $to_recycle_bin_status != null) {
                $messages = Message::whereNot('status_given_by', $user_id)->where([['user_id', $user_id], ['type_id', $private_msg_type->id], ['status_id', $read_status->id]])->orWhere([['user_id', $user_id], ['type_id', $private_msg_type->id],['status_id', $unread_status->id]])->get()->orWhere([['user_id', $user_id], ['type_id', $private_msg_type->id], ['status_id', $spam_status->id]])->get()->orWhere([['user_id', $user_id], ['type_id', $private_msg_type->id], ['status_id', $zip_status->id]])->get()->orWhere([['user_id', $user_id], ['type_id', $private_msg_type->id], ['status_id', $to_recycle_bin_status->id]])->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));

            } else {
                $messages = Message::where('user_id', $user_id)->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
            }

        } else {
            $messages = Message::where('user_id', $user_id)->get();

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
        }
    }

    /**
     * GET: Display all drafts messages.
     *
     * @param  $user_id
     * @return \Illuminate\Http\Response
     */
    public function drafts($user_id)
    {
        $msg_type_group = Group::where('group_name', 'Type de fichier')->first();
        $msg_group = Group::where('group_name', 'Message')->first();

        if ($msg_type_group != null AND $msg_group != null) {
            $private_msg_type = Type::where([['type_name', 'Message privé'], ['group_id', $msg_type_group->id]])->first();
            $draft_status = Status::where([['status_name', 'Brouillon'], ['group_id', $msg_group->id]])->first();

            if ($private_msg_type != null AND $draft_status != null) {
                $messages = Message::where([['user_id', $user_id], ['type_id', $private_msg_type->id], ['status_id', $draft_status->id]])->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));

            } else {
                $messages = Message::where('user_id', $user_id)->get();

                return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
            }

        } else {
            $messages = Message::where('user_id', $user_id)->get();

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
        }
    }

    /**
     * GET: Display all messages answered for a specific message.
     *
     * @param  $message_id
     * @return \Illuminate\Http\Response
     */
    public function answers($message_id)
    {
        $msg_group = Group::where('group_name', 'Message')->first();
        $spam_status = Status::where([['status_name', 'Spam'], ['group_id', $msg_group->id]])->first();
        $messages = Message::where('answered_for', $message_id)->whereNot('status_id', $spam_status->id)->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
    }

    /**
     * Switch between message statuses.
     *
     * @param  $id
     * @param  $user_id
     * @param  $status_name
     * @return \Illuminate\Http\Response
     */
    public function switchStatus($id, $user_id, $status_name)
    {
        $msg_group = Group::where('group_name', 'Message')->first();
        $spam_status = Status::where([['status_name', 'Spam'], ['group_id', $msg_group->id]])->first();
        $status = Status::where([['status_name', 'like', '%' . $status_name . '%'], ['group_id', $msg_group->id]])->first();
        $message = Message::find($id);

        // update "status_id" column
        if ($message->status_id == $spam_status->id AND $status->id != $spam_status->id) {
            $message->update([
                'last_status' => $spam_status->id,
                'status_given_by' => $user_id,
                'status_id' => $status->id,
                'updated_at' => now()
            ]);

        } else {
            $message->update([
                'status_given_by' => $user_id,
                'status_id' => $status->id,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.find_message_success'));
    }

    /**
     * Mark all messages statuses.
     *
     * @param  $id
     * @param  $entity
     * @param  $entity_id
     * @return \Illuminate\Http\Response
     */
    public function markAllRead($entity)
    {
        $msg_type_group = Group::where('group_name', 'Type de message')->first();
        $msg_group = Group::where('group_name', 'Message')->first();
        $private_msg_type = Type::where([['type_name', 'Message privé'], ['group_id', $msg_type_group->id]])->first();
        $read_status = Status::where([['status_name', 'Lu'], ['group_id', $msg_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lu'], ['group_id', $msg_group->id]])->first();
        $messages = Message::where([['sent_to', $entity], ['status_id', $unread_status->id], ['type_id', $private_msg_type->id]])->get();

        foreach ($messages as $message):
            // update "status_id" column
            $message->update([
                'status_given_by' => $entity,
                'status_id' => $read_status->id,
                'updated_at' => now()
            ]);
        endforeach;

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
    }

    /**
     * Upload message documents in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadDoc(Request $request, $id)
    {
        $inputs = [
            'message_id' => $request->message_id,
            'document' => $request->file('document'),
            'extension' => $request->file('document')->extension()
        ];
		// Find album by name to get its ID
		$representation_album = Album::where('album_name', 'Représentations')->where('message_id', $inputs['message_id'])->first();
		// Find type by name to get its ID
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
		$document_type = Type::where([['type_name', 'Document'], ['group_id', $file_type_group->id]])->first();
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

            // Validate file mime type
            $request->validate([
                'document' => 'required|mimes:txt,pdf,doc,docx,xls,xlsx,ppt,pptx,pps,ppsx'
            ]);

            // Create file name
			$file_name = 'documents/messages/' . $inputs['message_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, $inputs['document']));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($document_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $document_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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
                if ($document_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $document_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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
				'message_id' => $inputs['message_id']
			]);

            // Validate file mime type
            $request->validate([
                'document' => 'required|mimes:txt,pdf,doc,docx,xls,xlsx,ppt,pptx,pps,ppsx'
            ]);

            // Create file name
			$file_name = 'documents/messages/' . $inputs['message_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, $inputs['document']));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($document_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $document_type->id,
                        'album_id' => $album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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
                if ($document_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $document_type->id,
                        'album_id' => $album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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
                            'type_name' => 'Document',
                            'type_description' => 'Uploadez des documents depuis les dossiers de votre appareil.',
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

        $message = Message::find($id);

        $message->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.update_message_success'));
    }

    /**
     * Upload message audio in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadAudio(Request $request, $id)
    {
        $inputs = [
            'message_id' => $request->message_id,
            'audio' => $request->file('audio'),
            'extension' => $request->file('audio')->extension()
        ];
		// Find album by name to get its ID
		$representation_album = Album::where('album_name', 'Représentations')->where('message_id', $inputs['message_id'])->first();
		// Find type by name to get its ID
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
		$audio_type = Type::where([['type_name', 'Audio'], ['group_id', $file_type_group->id]])->first();
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

            // Validate required file and its mime type
            $validator = Validator::make($inputs, [
                'audio' => 'required|mimes:mp3,wav,m4a'
            ]);

            if ($validator->fails()) {
                return $this->handleError($validator->errors());       
            }

            // Create file name
			$file_name = 'audios/messages/' . $inputs['message_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, $inputs['audio']));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($audio_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $audio_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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
                if ($audio_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $audio_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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
				'message_id' => $inputs['message_id']
			]);

            // Validate file mime type
            $request->validate([
                'audio' => 'required|mimes:mp3,wav,m4a'
            ]);

            // Create file name
			$file_name = 'audios/messages/' . $inputs['message_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, $inputs['video']));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($audio_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $audio_type->id,
                        'album_id' => $album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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
                if ($audio_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $audio_type->id,
                        'album_id' => $album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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
                            'type_name' => 'Audio',
                            'type_description' => 'Utilisez votre microphone pour enregistrer le son que vous voulez partager.',
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

        $message = Message::find($id);

        $message->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.update_message_success'));
    }

    /**
     * Upload message video in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadVideo(Request $request, $id)
    {
        $inputs = [
            'message_id' => $request->message_id,
            'video' => $request->file('video'),
            'extension' => $request->file('video')->extension()
        ];
		// Find album by name to get its ID
		$representation_album = Album::where('album_name', 'Représentations')->where('message_id', $inputs['message_id'])->first();
		// Find type by name to get its ID
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
		$video_type = Type::where([['type_name', 'Vidéo'], ['group_id', $file_type_group->id]])->first();
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

            // Validate required file and its mime type
            $validator = Validator::make($inputs, [
                'video' => 'required|mimes:mp4,ogx,oga,ogv,ogg,webm'
            ]);

            if ($validator->fails()) {
                return $this->handleError($validator->errors());       
            }

            // Create file name
			$file_name = 'images/messages/' . $inputs['message_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, $inputs['video']));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($video_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $video_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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
                if ($video_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $video_type->id,
                        'album_id' => $representation_album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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
				'message_id' => $inputs['message_id']
			]);

            // Validate file mime type
            $request->validate([
                'video' => 'required|mimes:mp4,ogx,oga,ogv,ogg,webm'
            ]);

            // Create file name
			$file_name = 'images/messages/' . $inputs['message_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, $inputs['video']));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($video_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $video_type->id,
                        'album_id' => $album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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
                if ($video_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $video_type->id,
                        'album_id' => $album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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
                            'type_name' => 'Vidéo',
                            'type_description' => 'Uploadez des vidéos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
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

        $message = Message::find($id);

        $message->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.update_message_success'));
    }

    /**
     * Update message picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePicture(Request $request, $id)
    {
        $inputs = [
            'message_id' => $request->entity_id,
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
		$representation_album = Album::where('album_name', 'Représentations')->where('message_id', $inputs['message_id'])->first();
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
			$file_name = 'images/messages/' . $inputs['message_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.png';

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
				'message_id' => $inputs['message_id']
			]);
			// Create file name
			$file_name = 'images/messages/' . $inputs['message_id'] . '/' . $album->id . '/' . Str::random(50) . '.png';

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

		$message = Message::find($id);

        $message->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.update_message_success'));
	}
}
