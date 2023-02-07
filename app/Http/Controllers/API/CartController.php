<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\AboutContent;
use App\Models\Address;
use App\Models\Cart;
use App\Models\City;
use App\Models\Currency;
use App\Models\Group;
use App\Models\History;
use App\Models\Notification;
use App\Models\Reaction;
use App\Models\SellerTender;
use App\Models\SellerUser;
use App\Models\Status;
use App\Models\Tender;
use App\Models\TenderCart;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $tender_group = Group::where('group_name', 'Offre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $payment_group = Group::where('group_name', 'Fonctionnement')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $tender_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $ongoing_status = Status::where([['status_name', 'En cours'], ['group_id', $payment_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'tranfer_code' => $request->tranfer_code,
            'address_id' => $request->address_id,
            'seller_user_id' => $request->seller_user_id,
            'currency_id' => $request->currency_id,
            'status_id' => $ongoing_status->id
        ];
        // Select all carts to check unique constraint
        $carts = Cart::all();

        // Validate required fields
        if ($inputs['seller_user_id'] == null OR $inputs['seller_user_id'] == ' ') {
            return $this->handleError($inputs['seller_user_id'], __('validation.required'), 400);
        }

        // If tranfer code is given, check if it already exists
        if ($inputs['tranfer_code'] != null) {
            if ($inputs['tranfer_code'] == ' ') {
                return $this->handleError($inputs['tranfer_code'], __('validation.required'), 400);
            }

            foreach ($carts as $another_cart):
                if ($another_cart->tranfer_code == $inputs['tranfer_code']) {
                    return $this->handleError($inputs['tranfer_code'], __('validation.custom.tranfer_code.exists'), 400);
                }
            endforeach;
        }

        $cart = Cart::create($inputs);

        if ($request->seller_tenders_ids != null) {
            foreach ($request->seller_tenders_ids as $seller_tender_id):
                $seller_tender = SellerTender::find($seller_tender_id);

                // If the quantity stored is known, it concerns tenders whose quantity must be controlled 
                if ($seller_tender->stored_quantity != null) {
                    // Ensure that, for each seller, the quantity of tenders is sufficient to make the orders
                    if ($seller_tender->stored_quantity > 0) {
                        foreach ($request->quantities as $quantity):
                            if ($quantity <= $seller_tender->stored_quantity) {
                                TenderCart::create([
                                    'seller_tender_id' => $seller_tender->tender_id,
                                    'cart_id' => $cart->id,
                                    'ordered_quantity' => $quantity
                                ]);
                                // Reduce the quantity at the store
                                $updated_quantity = $seller_tender->stored_quantity - $quantity;

                                $seller_tender->update([
                                    'stored_quantity' => $updated_quantity,
                                    'updated_at' => now()
                                ]);

                                /*
                                    HISTORY AND/OR NOTIFICATION MANAGEMENT
                                */
                                // Find user that ordered tenders
                                $seller_user = SellerUser::find($cart->seller_user_id);
                                $user = User::find($seller_user->user_id);
                                $tender = Tender::find($seller_tender->tender->id);
                                $tender_type = Type::find($tender->type->id);
                                $reaction_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

                                History::create([
                                    'history_url' => 'carts/' . $cart->id,
                                    'history_content' => __('notifications.you_added_cart_tenders'),
                                    'user_id' => $user->id,
                                    'type_id' => $activities_history_type->id
                                ]);

                                if ($reaction_to_tender == null) {
                                    // Send notification to seller
                                    Notification::create([
                                        'notification_url' => 'carts/' . $cart->id,
                                        'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.ordered_your') . ' ' . strtolower($tender_type->type_name),
                                        'seller_id' => $seller_tender->seller_id,
                                        'status_id' => $unread_status->id
                                    ]);
                                }

                            } else {
                                return $this->handleError($seller_tender->stored_quantity, __('validation.custom.quantity'), 400);
                            }
                        endforeach;

                    } else {
                        return $this->handleError($seller_tender->stored_quantity, __('validation.custom.quantity'), 400);
                    }

                // Otherwise, it concerns tenders that do not need to have quantity
                } else {
                    TenderCart::create([
                        'seller_tender_id' => $seller_tender->tender_id,
                        'cart_id' => $cart->id
                    ]);

                    /*
                        HISTORY AND/OR NOTIFICATION MANAGEMENT
                    */
                    // Find user that ordered tenders
                    $seller_user = SellerUser::find($cart->seller_user_id);
                    $user = User::find($seller_user->user_id);
                    $tender = Tender::find($seller_tender->tender->id);
                    $tender_type = Type::find($tender->type->id);
                    $reaction_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

                    History::create([
                        'history_url' => 'carts/' . $cart->id,
                        'history_content' => __('notifications.you_added_cart_tenders'),
                        'user_id' => $user->id,
                        'type_id' => $activities_history_type->id
                    ]);

                    if ($reaction_to_tender == null) {
                        // Send notification to seller
                        Notification::create([
                            'notification_url' => 'carts/' . $cart->id,
                            'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.ordered_your') . ' ' . strtolower($tender_type->type_name),
                            'seller_id' => $seller_tender->seller_id,
                            'status_id' => $unread_status->id
                        ]);
                    }
                }
            endforeach;
        }

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
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'tranfer_code' => $request->tranfer_code,
            'address_id' => $request->address_id,
            'seller_user_id' => $request->seller_user_id,
            'currency_id' => $request->currency_id,
            'status_id' => $request->status_id,
            'updated_at' => now()
        ];
        // Select all carts and specific cart to check unique constraint
        $carts = Cart::all();
        $current_cart = Cart::find($inputs['id']);

        if ($inputs['tranfer_code'] != null) {
            if ($inputs['tranfer_code'] == ' ') {
                return $this->handleError($inputs['tranfer_code'], __('validation.required'), 400);
            }

            foreach ($carts as $another_cart):
                if ($current_cart->tranfer_code != $inputs['tranfer_code']) {
                    if ($another_cart->tranfer_code == $inputs['tranfer_code']) {
                        return $this->handleError($inputs['tranfer_code'], __('validation.custom.tranfer_code.exists'), 400);
                    }
                }
            endforeach;
        }

        $cart->update($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        // Find user that ordered tenders
        $seller_user = SellerUser::find($cart->seller_user_id);
        $user = User::find($seller_user->user_id);

        History::create([
            'history_url' => 'carts/' . $cart->id,
            'history_content' => __('notifications.you_updated_cart'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

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
        $you_deleted_about_content = AboutContent::where('subtitle', 'Vous avez supprimé l\'information')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        // Find user that ordered tenders
        $seller_user = SellerUser::find($cart->seller_user_id);
        $user = User::find($seller_user->user_id);

        History::create([
            'history_url' => 'about_contents/' . $you_deleted_about_content->id,
            'history_content' => __('notifications.you_deleted_cart'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        $cart->delete();

        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.delete_cart_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Associate address to cart in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function associateAddress(Request $request, $id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $cart = Cart::find($id);
        // Find user that ordered tenders
        $seller_user = SellerUser::find($cart->seller_user_id);
        $user = User::find($seller_user->user_id);

        // If user want to add address for his cart
		if ($request->number != null OR $request->street != null OR $request->neighborhood != null OR $request->area != null OR $request->reference != null OR $request->city_id != null) {
			// Select all addresses of a same city to check unique constraint
			$addresses = Address::where('city_id', $request->city_id)->get();

			if ($request->area == null OR $request->area == ' ') {
				return $this->handleError($request->area, __('validation.required'), 400);
			}

			if ($request->city_id == null OR $request->city_id == ' ') {
				return $this->handleError($request->city_id, __('validation.required'), 400);
			}

			// Find city by ID to get city name
			$city = City::find($request->city_id);

			// Check if address already exists
			foreach ($addresses as $another_address):
				if ($another_address->number == $request->number AND $another_address->street == $request->street AND $another_address->neighborhood == $request->neighborhood AND $another_address->area == $request->area) {
					return $this->handleError(
						 __('notifications.address.number') . __('notifications.colon_after_word') . ' ' . $request->number . ', ' 
						. __('notifications.address.street') . __('notifications.colon_after_word') . ' ' . $request->street . ', ' 
						. __('notifications.address.neighborhood') . __('notifications.colon_after_word') . ' ' . $request->neighborhood . ', ' 
						. __('notifications.address.area') . __('notifications.colon_after_word') . ' ' . $request->area . ', ' 
						. __('notifications.location.city.title') . __('notifications.colon_after_word') . ' ' . $city->city_name, __('validation.custom.address.exists'), 400);
				}
			endforeach;

			$address = Address::create([
				'number' => $request->number,
				'street' => $request->street,
				'neighborhood' => $request->neighborhood,
				'area' => $request->area,
				'reference' => $request->reference,
				'city_id' => $request->city_id
			]);

            // update "address_id" column
            $cart->update([
                'address_id' => $address->id,
                'updated_at' => now()
            ]);
		}

        if ($request->address_id != null) {
			// update "address_id" column
            $cart->update([
                'address_id' => $request->address_id,
                'updated_at' => now()
            ]);
		}

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        History::create([
            'history_url' => 'carts/' . $cart->id,
            'history_content' => __('notifications.you_added_cart_address'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Withdraw address from cart in storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function withdrawAddress($id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $cart = Cart::find($id);
        // Find user that ordered tenders
        $seller_user = SellerUser::find($cart->seller_user_id);
        $user = User::find($seller_user->user_id);

        // update "address_id" column
        $cart->update([
            'address_id' => null,
            'updated_at' => now()
        ]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        History::create([
            'history_url' => 'carts/' . $cart->id,
            'history_content' => __('notifications.you_deleted_cart_address'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Switch between several currencies.
     *
     * @param  $id
     * @param  $data
     * @return \Illuminate\Http\Response
     */
    public function switchCurrency($id, $data)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $currency = Currency::where('name', 'like', '%' . $data . '%')->first();
        $cart = Cart::find($id);
        // Find user that ordered tenders
        $seller_user = SellerUser::find($cart->seller_user_id);
        $user = User::find($seller_user->user_id);

        // update "currency_id" column
        $cart->update([
            'currency_id' => $currency->id,
            'updated_at' => now()
        ]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        History::create([
            'history_url' => 'carts/' . $cart->id,
            'history_content' => __('notifications.you_updated_cart_currency'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Add tenders at the cart in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function addTenders(Request $request, $id)
    {
        $tender_group = Group::where('group_name', 'Offre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $tender_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $cart = Cart::find($id);

        foreach ($request->seller_tenders_ids as $seller_tender_id):
            $seller_tender = SellerTender::find($seller_tender_id);

            // If the quantity stored is known, it concerns tenders whose quantity must be controlled 
            if ($seller_tender->stored_quantity != null) {
                // Ensure that, for each seller, the quantity of tenders is sufficient to make the orders
                if ($seller_tender->stored_quantity > 0) {
                    foreach ($request->quantities as $quantity):
                        if ($quantity <= $seller_tender->stored_quantity) {
                            TenderCart::create([
                                'seller_tender_id' => $seller_tender->id,
                                'cart_id' => $cart->id,
                                'ordered_quantity' => $quantity
                            ]);
                            // Reduce the quantity at the store
                            $updated_quantity = $seller_tender->stored_quantity - $quantity;

                            $seller_tender->update([
                                'stored_quantity' => $updated_quantity,
                                'updated_at' => now()
                            ]);

                            /*
                                HISTORY AND/OR NOTIFICATION MANAGEMENT
                            */
                            // Find user that ordered tenders
                            $seller_user = SellerUser::find($cart->seller_user_id);
                            $user = User::find($seller_user->user_id);
                            $tender = Tender::find($seller_tender->tender->id);
                            $tender_type = Type::find($tender->type->id);
                            $reaction_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

                            History::create([
                                'history_url' => 'carts/' . $cart->id,
                                'history_content' => __('notifications.you_added_cart_tenders'),
                                'user_id' => $user->id,
                                'type_id' => $activities_history_type->id
                            ]);

                            if ($reaction_to_tender == null) {
                                // Send notification to seller
                                Notification::create([
                                    'notification_url' => 'carts/' . $cart->id,
                                    'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.ordered_your') . ' ' . strtolower($tender_type->type_name),
                                    'seller_id' => $seller_tender->seller_id,
                                    'status_id' => $unread_status->id
                                ]);
                            }

                        } else {
                            return $this->handleError($seller_tender->stored_quantity, __('validation.custom.quantity'), 400);
                        }
                    endforeach;

                } else {
                    return $this->handleError($seller_tender->stored_quantity, __('validation.custom.quantity'), 400);
                }

            // Otherwise, it concerns tenders that do not need to have quantity
            } else {
                TenderCart::create([
                    'seller_tender_id' => $seller_tender->id,
                    'cart_id' => $cart->id
                ]);

                /*
                    HISTORY AND/OR NOTIFICATION MANAGEMENT
                */
                // Find user that ordered tenders
                $seller_user = SellerUser::find($cart->seller_user_id);
                $user = User::find($seller_user->user_id);
                $tender = Tender::find($seller_tender->tender->id);
                $tender_type = Type::find($tender->type->id);
                $reaction_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

                History::create([
                    'history_url' => 'carts/' . $cart->id,
                    'history_content' => __('notifications.you_added_cart_tenders'),
                    'user_id' => $user->id,
                    'type_id' => $activities_history_type->id
                ]);

                if ($reaction_to_tender == null) {
                    // Send notification to seller
                    Notification::create([
                        'notification_url' => 'carts/' . $cart->id,
                        'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.ordered_your') . ' ' . strtolower($tender_type->type_name),
                        'seller_id' => $seller_tender->seller_id,
                        'status_id' => $unread_status->id
                    ]);
                }
            }
        endforeach;

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Withdraw tenders from the cart in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function withdrawTenders(Request $request, $id)
    {
        $tender_group = Group::where('group_name', 'Offre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $tender_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $cart = Cart::find($id);

        foreach ($request->seller_tenders_ids as $seller_tender_id):
            $seller_tender = SellerTender::find($seller_tender_id);
            $tender_cart = TenderCart::where('cart_id', $cart->id)->where('seller_tender_id', $seller_tender_id)->first();

            if ($seller_tender->stored_quantity != null) {
                // Reset the stored quantity to the value it had before order
                $updated_quantity = $seller_tender->stored_quantity + $tender_cart->ordered_quantity;

                $seller_tender->update([
                    'stored_quantity' => $updated_quantity,
                    'updated_at' => now()
                ]);
            }

            // Delete link between tender and cart
            $tender_cart->delete();

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            // Find user that ordered tenders
            $seller_user = SellerUser::find($cart->seller_user_id);
            $user = User::find($seller_user->user_id);
            $tender = Tender::find($seller_tender->tender->id);
            $tender_type = Type::find($tender->type->id);
            $reaction_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

            History::create([
                'history_url' => 'carts/' . $cart->id,
                'history_content' => __('notifications.you_deleted_cart_tenders'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);

            if ($reaction_to_tender == null) {
                // Send notification to seller
                Notification::create([
                    'notification_url' => 'carts/' . $cart->id,
                    'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.canceled_order') . ' ' . strtolower($tender_type->type_name),
                    'seller_id' => $seller_tender->seller_id,
                    'status_id' => $unread_status->id
                ]);
            }
        endforeach;

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Update cart tranfer code in storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTranferCode($id)
    {
        $tender_group = Group::where('group_name', 'Offre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $payment_group = Group::where('group_name', 'Fonctionnement')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $tender_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $paid_status = Status::where([['status_name', 'Payé'], ['group_id', $payment_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // find cart by given ID
        $cart = Cart::find($id);

        // update "tranfer_code" column
        $cart->update([
            'tranfer_code' => Str::random(10),
            'status_id' => $paid_status->id,
            'updated_at' => now()
        ]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        foreach ($cart->seller_tenders as $seller_tender):
            // Find user that ordered tenders
            $seller_user = SellerUser::find($cart->seller_user_id);
            $user = User::find($seller_user->user_id);
            $tender = Tender::find($seller_tender->tender->id);
            $tender_type = Type::find($tender->type->id);
            $user_reacted_to_tender = Reaction::where('user_id', $user->id)->where('reacted_by', 'user')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();
            $seller_reacted_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

            History::create([
                'history_url' => 'carts/' . $cart->id,
                'history_content' => __('notifications.you_paid_cart'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);

            if ($user_reacted_to_tender == null) {
                // Send notification to user
                Notification::create([
                    'notification_url' => 'carts/' . $cart->id,
                    'notification_content' => __('notifications.payment_done') . ' ' . $seller_tender->tender->tender_name,
                    'user_id' => $user->id,
                    'status_id' => $unread_status->id
                ]);
            }

            if ($seller_reacted_to_tender == null) {
                // Send notification to seller
                Notification::create([
                    'notification_url' => 'carts/' . $cart->id,
                    'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.made_payment_of_your') . ' ' . strtolower($tender_type->type_name),
                    'seller_id' => $seller_tender->seller_id,
                    'status_id' => $unread_status->id
                ]);
            }
        endforeach;

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Cancel order, so update status of cart to "Annulé".
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelOrder($id)
    {
        $tender_group = Group::where('group_name', 'Offre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $payment_group = Group::where('group_name', 'Fonctionnement')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $tender_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $canceled_status = Status::where([['status_name', 'Annulé'], ['group_id', $payment_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $cart = Cart::find($id);

        foreach ($cart->seller_tenders as $seller_tender):
            $tender_cart = TenderCart::where('cart_id', $cart->id)->where('seller_tender_id', $seller_tender->id)->first();

            if ($seller_tender->stored_quantity != null) {
                // Reset the stored quantity to the value it had before order
                $updated_quantity = $seller_tender->stored_quantity + $tender_cart->ordered_quantity;

                $seller_tender->update([
                    'stored_quantity' => $updated_quantity,
                    'updated_at' => now()
                ]);
            }

            // Update status of cart
            $cart->update([
                'status_id' => $canceled_status->id,
                'updated_at' => now()
            ]);

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            // Find user that ordered tenders
            $seller_user = SellerUser::find($cart->seller_user_id);
            $user = User::find($seller_user->user_id);
            $tender = Tender::find($seller_tender->tender->id);
            $tender_type = Type::find($tender->type->id);
            $user_reacted_to_tender = Reaction::where('user_id', $user->id)->where('reacted_by', 'user')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();
            $seller_reacted_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

            History::create([
                'history_url' => 'carts/' . $cart->id,
                'history_content' => __('notifications.you_canceled_cart'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);

            if ($user_reacted_to_tender == null) {
                // Send notification to user
                Notification::create([
                    'notification_url' => 'carts/' . $cart->id,
                    'notification_content' => __('notifications.order_canceled'),
                    'user_id' => $user->id,
                    'status_id' => $unread_status->id
                ]);
            }

            if ($seller_reacted_to_tender == null) {
                // Send notification to seller
                Notification::create([
                    'notification_url' => 'carts/' . $cart->id,
                    'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.canceled_order') . ' ' . strtolower($tender_type->type_name),
                    'seller_id' => $seller_tender->seller_id,
                    'status_id' => $unread_status->id
                ]);
            }
        endforeach;

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Recover order, so update status of cart to "En cours".
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function recoverOrder($id)
    {
        $tender_group = Group::where('group_name', 'Offre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $payment_group = Group::where('group_name', 'Fonctionnement')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $tender_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $ongoing_status = Status::where([['status_name', 'En cours'], ['group_id', $payment_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $cart = Cart::find($id);

        foreach ($cart->seller_tenders as $seller_tender):
            $tender_cart = TenderCart::where('cart_id', $cart->id)->where('seller_tender_id', $seller_tender->id)->first();

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            // Find user that ordered tenders
            $seller_user = SellerUser::find($cart->seller_user_id);
            $user = User::find($seller_user->user_id);
            $tender = Tender::find($seller_tender->tender->id);
            $tender_type = Type::find($tender->type->id);
            $user_reacted_to_tender = Reaction::where('user_id', $user->id)->where('reacted_by', 'user')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();
            $seller_reacted_to_tender = Reaction::where('seller_id', $seller_tender->seller->id)->where('reacted_by', 'seller')->where('seller_tender_id', $seller_tender->id)->where('status_id', $stopped_status->id)->first();

            History::create([
                'history_url' => 'carts/' . $cart->id,
                'history_content' => __('notifications.you_recovered_cart'),
                'user_id' => $user->id,
                'type_id' => $activities_history_type->id
            ]);

            if ($seller_tender->stored_quantity != null) {
                // If the stored quantity is lower than the ordered quantity, set the ordered quantity to zero
                if ($seller_tender->stored_quantity < $tender_cart->ordered_quantity) {
                    $cart->update([
                        'status_id' => $ongoing_status->id,
                        'updated_at' => now()
                    ]);
                    $tender_cart->update([
                        'ordered_quantity' => 0,
                        'updated_at' => now()
                    ]);

                    // Send notification to user
                    Notification::create([
                        'notification_url' => 'carts/' . $cart->id,
                        'notification_content' => __('notifications.quantity_becomes_insufficient'),
                        'user_id' => $user->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesCart($cart), __('notifications.quantity_becomes_insufficient'));

                } else {
                    $cart->update([
                        'status_id' => $ongoing_status->id,
                        'updated_at' => now()
                    ]);

                    if ($user_reacted_to_tender == null) {
                        // Send notification to user
                        Notification::create([
                            'notification_url' => 'carts/' . $cart->id,
                            'notification_content' => __('notifications.order_recovered'),
                            'user_id' => $user->id,
                            'status_id' => $unread_status->id
                        ]);
                    }

                    if ($seller_reacted_to_tender == null) {
                        // Send notification to seller
                        Notification::create([
                            'notification_url' => 'carts/' . $cart->id,
                            'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.ordered_your') . ' ' . strtolower($tender_type->type_name),
                            'seller_id' => $seller_tender->seller_id,
                            'status_id' => $unread_status->id
                        ]);
                    }
    
                    return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
                }

            } else {
                $cart->update([
                    'status_id' => $ongoing_status->id,
                    'updated_at' => now()
                ]);

                if ($user_reacted_to_tender == null) {
                    // Send notification to user
                    Notification::create([
                        'notification_url' => 'carts/' . $cart->id,
                        'notification_content' => __('notifications.order_recovered'),
                        'user_id' => $user->id,
                        'status_id' => $unread_status->id
                    ]);
                }

                if ($seller_reacted_to_tender == null) {
                    // Send notification to seller
                    Notification::create([
                        'notification_url' => 'carts/' . $cart->id,
                        'notification_content' => $user->firstname . ' ' . $user->lastname . ' ' . __('notifications.ordered_your') . ' ' . strtolower($tender_type->type_name),
                        'seller_id' => $seller_tender->seller_id,
                        'status_id' => $unread_status->id
                    ]);
                }

                return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
            }
        endforeach;
    }
}
