<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Album;
use App\Models\Cart;
use App\Models\File;
use App\Models\Group;
use App\Models\History;
use App\Models\PrepaidCard;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Cart as ResourcesCart;

class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.find_all_carts_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $payment_group = Group::where('group_name', 'Paiement')->first();
        $ongoing_status = Status::where([['status_name', 'En cours'], ['group_id', $payment_group->id]])->first();
        // Get inputs
        $inputs = [
            'payment_code' => $request->payment_code,
            'status_id' => $ongoing_status->id,
            'user_id' => $request->user_id
        ];
        // Select all carts to check unique constraint
        $carts = Cart::where('user_id', $inputs['user_id'])->get();

        // Validate required fields
        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null OR $inputs['user_id'] == ' ') {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        // If payment code is given, check if it already exists
        if ($inputs['payment_code'] != null) {
            if ($inputs['payment_code'] == ' ') {
                return $this->handleError($inputs['payment_code'], __('validation.required'), 400);
            }

            foreach ($carts as $another_cart):
                if ($another_cart->payment_code == $inputs['payment_code']) {
                    return $this->handleError($inputs['payment_code'], __('validation.custom.code.exists'), 400);
                }
            endforeach;
        }

        $cart = Cart::create($inputs);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.create_cart_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cart = Cart::find($id);

        if (is_null($cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.find_cart_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'payment_code' => $request->payment_code,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'updated_at' => now()
        ];
        // Select all carts and specific cart to check unique constraint
        $carts = Cart::all();
        $current_cart = Cart::find($inputs['id']);

        if ($inputs['payment_code'] != null) {
            if ($inputs['payment_code'] == ' ') {
                return $this->handleError($inputs['payment_code'], __('validation.required'), 400);
            }

            foreach ($carts as $another_cart):
                if ($current_cart->payment_code != $inputs['payment_code']) {
                    if ($another_cart->payment_code == $inputs['payment_code']) {
                        return $this->handleError($inputs['payment_code'], __('validation.custom.code.exists'), 400);
                    }
                }
            endforeach;
        }

        $cart->update($inputs);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();

        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.delete_cart_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Update cart payment code in storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePaymentCode($id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $payment_group = Group::where('group_name', 'Paiement')->first();
        $paid_status = Status::where([['status_name', 'Payé'], ['group_id', $payment_group->id]])->first();
        // find cart by given ID
        $cart = Cart::find($id);
        $prepaid_card_by_cart = PrepaidCard::where('cart_id', $cart->id)->get()->count();

        // update "payment_code" column
        $cart->update([
            'payment_code' => Str::random(10),
            'status_id' => $paid_status->id,
            'updated_at' => now()
        ]);

        if ($prepaid_card_by_cart > 0) {
            if ($prepaid_card_by_cart == 1) {
                History::create([
                    'history_url' => 'cart/receipt/' . $cart->id,
                    'history_content' => __('notifications.you_bought_prepaid_card'),
                    'user_id' => $cart->user_id,
                    'type_id' => $activities_history_type->id
                ]);
    
            } else {
                History::create([
                    'history_url' => 'cart/receipt/' . $cart->id,
                    'history_content' => __('notifications.you_bought_prepaid_cards1') . ' ' . $prepaid_card_by_cart . ' ' . __('notifications.you_bought_prepaid_cards2'),
                    'user_id' => $cart->user_id,
                    'type_id' => $activities_history_type->id
                ]);
            }
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Add prepaid cards in the cart in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function addPrepaidCards(Request $request, $id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $payment_group = Group::where('group_name', 'Paiement')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $ongoing_status = Status::where([['status_name', 'En cours'], ['group_id', $payment_group->id]])->first();
        // find cart by given ID
        $cart = Cart::find($id);

        foreach ($request->prepaid_cards_ids as $prepaid_card_id):
            $prepaid_card = PrepaidCard::find($prepaid_card_id);

            $prepaid_card->update([
                'status_id' => $ongoing_status->id,
                'cart_id' => $cart->id,
                'updated_at' => now()
            ]);
        endforeach;

        History::create([
            'history_url' => 'cart',
            'history_content' => __('notifications.you_added_prepaid_card'),
            'user_id' => $cart->user_id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Withdraw prepaid cards in the cart in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function withdrawPrepaidCards(Request $request, $id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();

        if (count($request->prepaid_cards_ids) > 0) {
            // find cart by given ID
            $cart = Cart::find($id);

            foreach ($request->prepaid_cards_ids as $prepaid_card_id):
                $prepaid_card = PrepaidCard::find($prepaid_card_id);

                if ($prepaid_card->cart_id == $cart->id) {
                    $prepaid_card->update([
                        'status_id' => null,
                        'cart_id' => null,
                        'updated_at' => now()
                    ]);
                }
            endforeach;

            if (count($request->prepaid_cards_ids) == 1) {
                History::create([
                    'history_url' => 'cart',
                    'history_content' => __('notifications.you_deleted_prepaid_card'),
                    'user_id' => $cart->user_id,
                    'type_id' => $activities_history_type->id
                ]);

            } else {
                History::create([
                    'history_url' => 'cart',
                    'history_content' => __('notifications.you_deleted_prepaid_cards1') . ' ' . count($request->prepaid_cards_ids) . ' ' . __('notifications.you_deleted_prepaid_cards2'),
                    'user_id' => $cart->user_id,
                    'type_id' => $activities_history_type->id
                ]);
            }
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Upload cart documents in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadDoc(Request $request, $id)
    {
        $inputs = [
            'cart_id' => $request->cart_id,
            'document' => $request->file('document'),
            'extension' => $request->file('document')->extension()
        ];
		// Find album by name to get its ID
		$representation_album = Album::where('album_name', 'Représentations')->where('cart_id', $inputs['cart_id'])->first();
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
			$file_name = 'documents/carts/' . $inputs['cart_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

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
				'cart_id' => $inputs['cart_id']
			]);

            // Validate file mime type
            $request->validate([
                'document' => 'required|mimes:txt,pdf,doc,docx,xls,xlsx,ppt,pptx,pps,ppsx'
            ]);

            // Create file name
			$file_name = 'documents/carts/' . $inputs['cart_id'] . '/' . $representation_album->id . '/' . Str::random(50) . '.' . $inputs['extension'];

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

        $cart = Cart::find($id);

        $cart->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }
}
