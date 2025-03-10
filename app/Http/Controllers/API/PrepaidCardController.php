<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\Group;
use App\Models\History;
use App\Models\Notification;
use App\Models\PrepaidCard;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\PrepaidCard as ResourcesPrepaidCard;

class PrepaidCardController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prepaid_cards = PrepaidCard::all();

        return $this->handleResponse(ResourcesPrepaidCard::collection($prepaid_cards), __('notifications.find_all_prepaid_cards_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
		$deactivated_status = Status::where([['status_name', 'Désactivaté'], ['group_id', $functioning_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'card_number' => $request->card_number,
            'number_of_kilowatt_hours' => $request->number_of_kilowatt_hours,
            'price' => $request->price,
            'status_id' => $deactivated_status->id,
            'company_id' => $request->company_id,
            'cart_id' => $request->cart_id
        ];
        // Select all prepaid cards to check unique constraint
        $prepaid_cards = PrepaidCard::where('company_id', $inputs['company_id'])->get();

        // Validate required fields
        if ($inputs['card_number'] == null OR $inputs['card_number'] == ' ') {
            return $this->handleError($inputs['card_number'], __('validation.required'), 400);
        }

        if ($inputs['number_of_kilowatt_hours'] == null OR $inputs['number_of_kilowatt_hours'] == ' ') {
            return $this->handleError($inputs['number_of_kilowatt_hours'], __('validation.required'), 400);
        }

        if ($inputs['price'] == null OR $inputs['price'] == ' ') {
            return $this->handleError($inputs['price'], __('validation.required'), 400);
        }

        if ($inputs['company_id'] == null OR $inputs['company_id'] == ' ') {
            return $this->handleError($inputs['company_id'], __('validation.required'), 400);
        }

        // Check if card number already exists
        foreach ($prepaid_cards as $another_prepaid_card):
            if ($another_prepaid_card->card_number == $inputs['card_number']) {
                return $this->handleError($inputs['card_number'], __('validation.custom.card_number.exists'), 400);
            }
        endforeach;

        $prepaid_card = PrepaidCard::create($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $admin_role = Role::where('role_name', 'Administrateur')->first();
        // The prepaid card creator
        $creator = User::find($request->creator_id);
        $company = Company::find($inputs['company_id']);
        // All company users to get admins
        $company_users = User::where('company_id', $company->id)->get();

        History::create([
            'history_url' => 'company/prepaid_card/' . $prepaid_card->id,
            'history_content' => __('notifications.you_added_prepaid_card'),
            'user_id' => $creator->id,
            'type_id' => $activities_history_type->id
        ]);

        foreach ($company_users as $company_user):
            $other_admins = RoleUser::where('role_id', $admin_role->id)->get();

            foreach ($other_admins as $other_admin):
                if ($company_user->id == $other_admin->id AND $company_user->id != $creator->id) {
                    Notification::create([
                        'notification_url' => 'company/prepaid_card/' . $prepaid_card->id,
                        'notification_content' => $creator->firstname . ' ' . $creator->lastname . ' ' . __('notifications.added_prepaid_card'),
                        'user_id' => $company_user->id,
                        'status_id' => $unread_status->id
                    ]);
                }
            endforeach;
        endforeach;

        return $this->handleResponse(new ResourcesPrepaidCard($prepaid_card), __('notifications.create_prepaid_card_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $prepaid_card = PrepaidCard::find($id);

        if (is_null($prepaid_card)) {
            return $this->handleError(__('notifications.find_prepaid_card_404'));
        }

        return $this->handleResponse(new ResourcesPrepaidCard($prepaid_card), __('notifications.find_prepaid_card_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PrepaidCard  $prepaid_card
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PrepaidCard $prepaid_card)
    {
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'card_number' => $request->card_number,
            'number_of_kilowatt_hours' => $request->number_of_kilowatt_hours,
            'price' => $request->price,
            'status_id' => $request->status_id,
            'company_id' => $request->company_id,
            'cart_id' => $request->cart_id,
            'updated_at' => now()
        ];
        // Select all files and specific file to check unique constraint
        $prepaid_cards = PrepaidCard::where('company_id', $inputs['company_id'])->get();
        $current_prepaid_card = PrepaidCard::find($inputs['id']);

        if ($inputs['card_number'] == null OR $inputs['card_number'] == ' ') {
            return $this->handleError($inputs['card_number'], __('validation.required'), 400);
        }

        if ($inputs['number_of_kilowatt_hours'] == null OR $inputs['number_of_kilowatt_hours'] == ' ') {
            return $this->handleError($inputs['number_of_kilowatt_hours'], __('validation.required'), 400);
        }

        if ($inputs['price'] == null OR $inputs['price'] == ' ') {
            return $this->handleError($inputs['price'], __('validation.required'), 400);
        }

        if ($inputs['company_id'] == null OR $inputs['company_id'] == ' ') {
            return $this->handleError($inputs['company_id'], __('validation.required'), 400);
        }

        // Check if card number already exists
        foreach ($prepaid_cards as $another_prepaid_card):
            if ($current_prepaid_card->card_number != $inputs['card_number']) {
                if ($another_prepaid_card->card_number == $inputs['card_number']) {
                    return $this->handleError($inputs['card_number'], __('validation.custom.card_number.exists'), 400);
                }
            }
        endforeach;

        $prepaid_card->update($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $admin_role = Role::where('role_name', 'Administrateur')->first();
        // The prepaid card updater
        $updater = User::find($request->updater_id);
        $company = Company::find($inputs['company_id']);
        // All company users to get admins
        $company_users = User::where('company_id', $company->id)->get();

        History::create([
            'history_url' => 'company/prepaid_card/' . $prepaid_card->id,
            'history_content' => __('notifications.you_updated_prepaid_card'),
            'user_id' => $updater->id,
            'type_id' => $activities_history_type->id
        ]);

        foreach ($company_users as $company_user):
            $users_admin = RoleUser::where('role_id', $admin_role->id)->get();

            foreach ($users_admin as $user_admin):
                if ($company_user->id == $user_admin->id AND $company_user->id != $updater->id) {
                    Notification::create([
                        'notification_url' => 'company/prepaid_card/' . $prepaid_card->id,
                        'notification_content' => $updater->firstname . ' ' . $updater->lastname . ' ' . __('notifications.updated_prepaid_card'),
                        'user_id' => $company_user->id,
                        'status_id' => $unread_status->id
                    ]);
                }
            endforeach;
        endforeach;

        return $this->handleResponse(new ResourcesPrepaidCard($prepaid_card), __('notifications.update_prepaid_card_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PrepaidCard  $prepaid_card
     * @return \Illuminate\Http\Response
     */
    public function destroy(PrepaidCard $prepaid_card)
    {
        $prepaid_card->delete();

        $prepaid_cards = PrepaidCard::all();

        return $this->handleResponse(ResourcesPrepaidCard::collection($prepaid_cards), __('notifications.delete_prepaid_card_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Publish previously created prepaid card(s).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $publisher_id
     * @return \Illuminate\Http\Response
     */
    public function publish(Request $request, $publisher_id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activated_status = Status::where([['status_name', 'Activé'], ['group_id', $functioning_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $admin_role = Role::where('role_name', 'Administrateur')->first();
        $publisher = User::find($publisher_id);

        if ($request->prepaid_cards_ids != null) {
            /*
                HISTORY MANAGEMENT
            */
            History::create([
                'history_url' => 'company/prepaid_card',
                'history_content' => __('notifications.you_published_prepaid_cards1') . ' ' . count($request->prepaid_cards_ids) . ' ' . __('notifications.you_published_prepaid_cards2'),
                'user_id' => $publisher->id,
                'type_id' => $activities_history_type->id
            ]);

            foreach ($request->prepaid_cards_ids as $prepaid_card_id):
                // find invoice by given ID
                $prepaid_card = PrepaidCard::find($prepaid_card_id);

                // update "status_id" column
                $prepaid_card->update([
                    'status_id' => $activated_status,
                    'updated_at' => now()
                ]);

                /*
                    NOTIFICATION MANAGEMENT
                */
                $company = Company::find($prepaid_card->company->id);
                $company_users = User::where('company_id', $company->id)->get();

                // Send a notification to each company admin
                foreach ($company_users as $company_user):
                    $users_admin = RoleUser::where('role_id', $admin_role->id)->get();

                    foreach ($users_admin as $user_admin):
                        if ($company_user->id == $user_admin->id AND $company_user->id != $publisher->id) {
                            Notification::create([
                                'notification_url' => 'company/prepaid_card',
                                'notification_content' => $publisher->firstname . ' ' . $publisher->lastname . ' ' . __('notifications.published_prepaid_cards1') . ' ' . count($request->prepaid_cards_ids) . ' ' . __('notifications.published_prepaid_cards2'),
                                'user_id' => $company_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }
                    endforeach;
                endforeach;

                return $this->handleResponse(new ResourcesPrepaidCard($prepaid_card), __('notifications.update_prepaid_card_success'));
            endforeach;
        }

        if ($request->prepaid_card_id != null) {
            // find invoice by given ID
            $prepaid_card = PrepaidCard::find($request->prepaid_card_id);

            // update "status_id" column
            $prepaid_card->update([
                'status_id' => $activated_status,
                'updated_at' => now()
            ]);

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            $company = Company::find($prepaid_card->company->id);
            $company_users = User::where('company_id', $company->id)->get();

            History::create([
                'history_url' => 'company/prepaid_card/' . $prepaid_card->id,
                'history_content' => __('notifications.you_published_prepaid_card'),
                'user_id' => $publisher->id,
                'type_id' => $activities_history_type->id
            ]);

            // Send a notification to each company admin
            foreach ($company_users as $company_user):
                $users_admin = RoleUser::where('role_id', $admin_role->id)->get();
    
                foreach ($users_admin as $user_admin):
                    if ($company_user->id == $user_admin->id AND $company_user->id != $publisher->id) {
                        Notification::create([
                            'notification_url' => 'company/prepaid_card/' . $prepaid_card->id,
                            'notification_content' => $publisher->firstname . ' ' . $publisher->lastname . ' ' . __('notifications.published_prepaid_card'),
                            'user_id' => $company_user->id,
                            'status_id' => $unread_status->id
                        ]);
                    }
                endforeach;
            endforeach;

            return $this->handleResponse(new ResourcesPrepaidCard($prepaid_card), __('notifications.update_prepaid_card_success'));
        }
    }
}
