<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\AboutSubject;
use App\Models\Address;
use App\Models\Album;
use App\Models\Area;
use App\Models\BankCode;
use App\Models\Company;
use App\Models\Email;
use App\Models\File;
use App\Models\Group;
use App\Models\History;
use App\Models\Neighborhood;
use App\Models\Notification;
use App\Models\PasswordReset;
use App\Models\Phone;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\SocialNetwork;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\User as ResourcesUser;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'));
    }

    /**
     * Store a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
		$main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'registration_number' => $request->registration_number,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'surname' => $request->surname,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'customer_name' => $request->customer_name,
            'password' => $request->password,
            'password_visible' => $request->password_visible,
            'confirm_password' => $request->confirm_password,
            'remember_token' => $request->remember_token,
            'status_id' => $request->status_id,
            'type_id' => $request->type_id,
            'company_id' => $request->company_id,
            'office_id' => $request->office_id
        ];

        // Validate required fields
        if ($inputs['firstname'] == null OR $inputs['firstname'] == ' ') {
            return $this->handleError($inputs['firstname'], __('validation.required'), 400);
        }

        if ($request->phone_number == null AND $request->email == null) {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($request->phone_number == ' ' AND $request->email == ' ') {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($request->phone_number == null AND $request->email == ' ') {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($request->phone_number == ' ' AND $request->email == null) {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        // If user doesn't enter password, register datas with generated password
        if ($inputs['password'] == null OR $inputs['password'] == ' ') {
            $new_password = Str::random(8);
            $inputs['password'] = Hash::make($new_password);
            $inputs['password_visible'] = $new_password;

        } else {
            if ($inputs['confirm_password'] != $inputs['password']) {
                return $this->handleError($inputs['confirm_password'], __('notifications.confirm_password.error'), 400);
            }

            if (preg_match('#^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$#', $inputs['password']) == 0) {
                return $this->handleError($inputs['password'], __('notifications.password.error'), 400);
            }

            // Set password visible
            $inputs['password_visible'] = $inputs['password'];

            Hash::make($inputs['password']);
        }

        $user = User::create($inputs);

        if ($request->role_id != null) {
            RoleUser::create([
                'role_id' => $request->role_id,
                'user_id' => $user->id,
                'selected' => 1,
            ]);
        }

        // If user want to add company address
        if ($request->number != null OR $request->street != null OR $request->neighborhood_id != null OR $request->area_id != null) {
            // Select all addresses of a same neighborhood to check unique constraint
            $addresses = Address::where('neighborhood_id', $request->neighborhood_id)->get();

            if ($request->neighborhood_id == null OR $request->neighborhood_id == ' ') {
                return $this->handleError($request->neighborhood_id, __('validation.required'), 400);
            }

            if ($request->area_id == null OR $request->area_id == ' ') {
                return $this->handleError($request->area_id, __('validation.required'), 400);
            }

            // Find area and neighborhood by their IDs to get their names
            $area = Area::find($request->area_id);
            $neighborhood = Neighborhood::find($request->neighborhood_id);

            // Check if address already exists
            foreach ($addresses as $another_address):
                if ($another_address->number == $request->number AND $another_address->street == $request->street AND $another_address->neighborhood_id == $request->neighborhood_id AND $another_address->area_id == $request->area_id) {
                    return $this->handleError(
                        __('notifications.address.number') . __('notifications.colon_after_word') . ' ' . $request->number . ', ' 
                        . __('notifications.address.street') . __('notifications.colon_after_word') . ' ' . $request->street . ', ' 
                        . __('notifications.address.neighborhood') . __('notifications.colon_after_word') . ' ' . $neighborhood->neighborhood_name . ', ' 
                        . __('notifications.address.area') . __('notifications.colon_after_word') . ' ' . $area->area_name, __('validation.custom.address.exists'), 400);
                }
            endforeach;

            Address::create([
                'number' => $request->number,
                'street' => $request->street,
                'area_id' => $request->area_id,
                'neighborhood_id' => $request->neighborhood_id,
                'status_id' => $main_status->id,
                'user_id' => $user->id
            ]);
        }

        // If user want to add e-mail and phone number
        if ($request->email_content != null AND $request->email_content == ' ' AND $request->phone_number != null AND $request->phone_number == ' ') {
            // Select all e-mails and phone numbers to check unique constraint
            $emails = Email::where('user_id', $user->id)->get();
            $phones = Phone::where('user_id', $user->id)->get();

            if ($request->email_status_id == null OR $request->email_status_id == ' ') {
                return $this->handleError($request->email_status_id, __('validation.required'), 400);
            }

            if ($request->phone_code == null OR $request->phone_code == ' ') {
                return $this->handleError($request->phone_number, __('validation.required'), 400);
            }

            if ($request->phone_service_id == null OR $request->phone_service_id == ' ') {
                return $this->handleError($request->service_id, __('validation.required'), 400);
            }

            if ($request->phone_status_id == null OR $request->phone_status_id == ' ') {
                return $this->handleError($request->phone_status_id, __('validation.required'), 400);
            }

            if (preg_match('#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#', $request->email_content) == 0) {
                return $this->handleError($request->email_content, __('validation.custom.email.incorrect'), 400);
            }

            if (preg_match('#^0[1-9][0-9]([-. ]?[0-9]{2,3}){3,4}$#', $request->phone_number) == 0) {
                return $this->handleError($request->phone_number, __('validation.custom.phone.incorrect'), 400);
            }

            // Check if email or phone already exists
            foreach ($emails as $another_email):
                if ($another_email->email_content == $request->email_content) {
                    return $this->handleError($request->email_content, __('validation.custom.email.exists'), 400);
                }
            endforeach;
            foreach ($phones as $another_phone):
                if ($another_phone->phone_code == $request->phone_code AND $another_phone->phone_number == $request->phone_number) {
                    return $this->handleError($request->phone_number, __('validation.custom.phone.exists'), 400);
                }
            endforeach;

            // Register email and phone number associated to user
            $email = Email::create([
                'email_content' => $request->email_content,
                'user_id' => $user->id,
                'status_id' => $main_status->id
            ]);
            $phone = Phone::create([
                'phone_code' => $request->phone_code,
                'phone_number' => $request->phone_number,
                'service_id' => $request->service_id,
                'user_id' => $user->id,
                'status_id' => $main_status->id
            ]);

            // Register password in the case user want to reset it
            PasswordReset::create([
                'email' => $email->email_content,
                'phone_code' => $phone->phone_code,
                'phone_number' => $phone->phone_number,
                'former_password' => $inputs['password']
            ]);

        // Otherwise, check e-mail and phone existence separately
        } else {
            // If user want to add an e-mail
            if ($request->email_content != null AND $request->email_content == ' ') {
                // Select all company e-mails to check unique constraint
                $emails = Email::where('user_id', $user->id)->get();

                if ($request->email_status_id == null OR $request->email_status_id == ' ') {
                    return $this->handleError($request->email_status_id, __('validation.required'), 400);
                }

                if (preg_match('#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#', $request->email_content) == 0) {
                    return $this->handleError($request->email_content, __('validation.custom.email.incorrect'), 400);
                }

                // Check if e-mail already exists
                foreach ($emails as $another_email):
                    if ($another_email->email_content == $request->email_content) {
                        return $this->handleError($request->email_content, __('validation.custom.email.exists'), 400);
                    }
                endforeach;

                $email = Email::create([
                    'email_content' => $request->email_content,
                    'user_id' => $user->id,
                    'status_id' => $main_status->id
                ]);

                // Register password in the case user want to reset it
                PasswordReset::create([
                    'email' => $email->email_content,
                    'former_password' => $inputs['password']
                ]);
            }

            // If user want to add a phone number
            if ($request->phone_number != null AND $request->phone_number == ' ') {
                // Select all user phones to check unique constraint
                $phones = Phone::where('user_id', $user->id)->get();

                if ($request->phone_code == null OR $request->phone_code == ' ') {
                    return $this->handleError($request->phone_number, __('validation.required'), 400);
                }

                if ($request->service_id == null OR $request->service_id == ' ') {
                    return $this->handleError($request->service_id, __('validation.required'), 400);
                }

                if ($request->phone_status_id == null OR $request->phone_status_id == ' ') {
                    return $this->handleError($request->phone_status_id, __('validation.required'), 400);
                }

                if (preg_match('#^0[1-9][0-9]([-. ]?[0-9]{2,3}){3,4}$#', $request->phone_number) == 0) {
                    return $this->handleError($request->phone_number, __('validation.custom.phone.incorrect'), 400);
                }

                // Check if phone number already exists
                foreach ($phones as $another_phone):
                    if ($another_phone->phone_code == $request->phone_code AND $another_phone->phone_number == $request->phone_number) {
                        return $this->handleError($request->phone_number, __('validation.custom.phone.exists'), 400);
                    }
                endforeach;

                $phone = Phone::create([
                    'phone_code' => $request->phone_code,
                    'phone_number' => $request->phone_number,
                    'service_id' => $request->phone_service_id,
                    'user_id' => $user->id,
                    'status_id' => $main_status->id
                ]);

                // Register password in the case user want to reset it
                PasswordReset::create([
                    'phone_code' => $phone->phone_code,
                    'phone_number' => $phone->phone_number,
                    'former_password' => $inputs['password']
                ]);
            }
        }

        // If user want to add a bank code
        if ($request->card_number != null AND $request->card_number == ' ') {
            // Select all bank codes to check unique constraint
            $bank_codes = BankCode::all();

            if ($request->bank_code_service_id == ' ') {
                return $this->handleError($request->service_id, __('validation.required'), 400);
            }

            // Check if user social network URL already exists
            foreach ($bank_codes as $another_bank_code):
                if ($another_bank_code->card_number == $request->card_number) {
                    return $this->handleError($request->card_number, __('validation.custom.card_number.exists'), 400);
                }
            endforeach;

            BankCode::create([
                'card_name' => $request->card_name,
                'card_number' => $request->card_number,
                'account_number' => $request->account_number,
                'expiration' => $request->expiration,
                'service_id' => $request->service_id,
                'status_id' => $main_status->id,
                'user_id' => $user->id
            ]);
        }

        // If user want to add a social network account
        if ($request->network_name != null AND $request->network_name == ' ') {
            // Select all social network accounts to check unique constraint
            $social_networks = SocialNetwork::all();

            // Check if network url already exists
            foreach ($social_networks as $another_social_network):
                if ($another_social_network->network_url == $request->network_url) {
                    return $this->handleError($request->network_url, __('validation.custom.network_url.exists'), 400);
                }
            endforeach;

            SocialNetwork::create([
                'network_name' => $request->network_name,
                'network_url' => $request->network_url,
                'user_id' => $user->id
            ]);
        }

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $superadmin_role = Role::where('role_name', 'Super administrateur')->first();
        $developer_role = Role::where('role_name', 'Développeur')->first();
        $admin_role = Role::where('role_name', 'Administrateur')->first();
        $customer_role = Role::where('role_name', 'Client')->first();
        $user_roles = RoleUser::where('user_id', $user->id)->first();

        History::create([
            'history_url' => 'account',
            'history_content' => __('notifications.user_created'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        foreach ($user_roles as $user_role):
            // Send welcome notification to the new user, excepted if its role is "Super administrateur"
            if ($user_role->id_role != $superadmin_role->id AND $user_role->id_role != $developer_role->id) {
                Notification::create([
                    'notification_url' => 'about/terms_of_use',
                    'notification_content' => __('notifications.welcome_user'),
                    'user_id' => $user->id,
                    'status_id' => $unread_status->id
                ]);
            }
        endforeach;

        // if the user belongs to some company 
        if ($inputs['company_id'] != null) {
            $company = Company::find($inputs['company_id']);
            $company_users = User::where(['company_id', $company->id])->get();

            foreach ($user_roles as $user_role):
                // Send notification to all company administrators if the new user is a customer
                if ($user_role->id_role == $customer_role->id) {
                    foreach ($company_users as $company_user):
                        $user_admin = RoleUser::where([['role_id', $admin_role->id], ['user_id', $company_user->id]])->first();

                        Notification::create([
                            'notification_url' => 'company',
                            'notification_content' => $user->fistname . ' ' . $user->lastname . ' ' . __('notifications.subscribed_to_company'),
                            'user_id' => $user_admin->id,
                            'status_id' => $unread_status->id
                        ]);
                    endforeach;
                }
            endforeach;
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.create_user_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.find_user_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'registration_number' => $request->registration_number,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'surname' => $request->surname,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'customer_name' => $request->customer_name,
            'password' => $request->password,
            'password_visible' => $request->password_visible,
            'confirm_password' => $request->confirm_password,
            'remember_token' => $request->remember_token,
            'status_id' => $request->status_id,
            'type_id' => $request->type_id,
            'company_id' => $request->company_id,
            'office_id' => $request->office_id,
            'updated_at' => now(),
        ];

        // Validate required fields
        if ($inputs['firstname'] == null OR $inputs['firstname'] == ' ') {
            return $this->handleError($inputs['firstname'], __('validation.required'), 400);
        }

        if ($request->phone_number == null AND $request->email == null) {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($request->phone_number == ' ' AND $request->email == ' ') {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($request->phone_number == null AND $request->email == ' ') {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($request->phone_number == ' ' AND $request->email == null) {
            return $this->handleError(__('validation.custom.email_or_phone.required'));
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        $user->update($inputs);

        if ($request->role_id != null) {
            $user_roles = RoleUser::where(['user_id', $user->id])->get();

            foreach ($user_roles as $role_user):
                $role_user->update([
                    'selected' => 0,
    				'updated_at' => now()
                ]);

                if ($role_user->id == $request->role_id) {
                    $role_user->update([
                        'selected' => 1,
        				'updated_at' => now()
                    ]);

                } else {
                    RoleUser::create([
                        'role_id' => $request->role_id,
                        'user_id' => $user->id,
                        'selected' => 1
                    ]);
                }
            endforeach;
        }

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        History::create([
            'history_url' => 'account',
            'history_content' => __('notifications.you_updated_account'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        $users = User::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.delete_user_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a user by his firstname / lastname / surname / username.
     *
     * @param  int $visitor_user_id
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($visitor_user_id, $data)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $search_history_type = Type::where([['type_name', 'Historique de recherche'], ['group_id', $history_type_group->id]])->first();
        $users = User::search($data)->get();

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
		History::create([
            'history_url' => 'search/users/' . $data,
            'history_content' => $data,
            'user_id' => $visitor_user_id,
            'type_id' => $search_history_type->id
        ]);

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'));
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Get inputs
        $inputs = [
            'username' => $request->username,
            'password' => $request->password
        ];

        if ($inputs['username'] == null OR $inputs['username'] == ' ') {
            return $this->handleError($inputs['username'], __('validation.required'), 400);
        }

        if ($inputs['password'] == null) {
            return $this->handleError($inputs['password'], __('validation.required'), 400);
        }

        if (is_numeric($inputs['username'])) {
            $phone = Phone::where('phone_number', $inputs['username'])->first();

            if ($phone != null) {
                $user = User::find($phone->user_id);

                if (!$user) {
                    return $this->handleError($inputs['username'], __('auth.username'), 400);
                }

                if (!Hash::check($inputs['password'], $user->password)) {
                    return $this->handleError($inputs['password'], __('auth.password'), 400);
                }

                // update "last_connection" column
                $user->update([
                    'last_connection' => now(),
                    'updated_at' => now()
                ]);

                return $this->handleResponse(new ResourcesUser($user), __('notifications.find_user_success'));

            } else {
                return $this->handleError(__('notifications.find_phone_404'));
            }

        } else {
            $email = Email::where('email_content', '=', $inputs['username'])->first();

            if ($email != null) {
                $user = User::find($email->user_id);

                if (!$user) {
                    return $this->handleError($inputs['username'], __('auth.username'), 400);
                }

                if (!Hash::check($inputs['password'], $user->password)) {
                    return $this->handleError($inputs['password'], __('auth.password'), 400);
                }

                // update "last_connection" column
                $user->update([
                    'last_connection' => now(),
                    'updated_at' => now()
                ]);

                return $this->handleResponse(new ResourcesUser($user), __('notifications.find_user_success'));

            } else {
                return $this->handleError(__('notifications.find_phone_404'));
            }
        }
    }

    /**
     * Switch between user statuses.
     *
     * @param  $id
     * @param  $status_name
     * @return \Illuminate\Http\Response
     */
    public function switchStatus($id, $status_name)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
		$notification_group = Group::where('group_name', 'Notification')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $status = Status::where([['status_name', 'like', '%' . $status_name . '%'], ['group_id', $functioning_group->id]])->first();
        $user = User::find($id);
        $activated_status = Status::where([['status_name', 'Activé'], ['group_id', $functioning_group->id]]);
        $deactivated_status = Status::where([['status_name', 'Désactivé'], ['group_id', $functioning_group->id]]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        // If the status name is "Activé", verify if the user reactivated his account and send him the notification
        if ($status->status_name == 'Activé') {
            // Check whether since the user deactivation, terms of use or privacy policy are updated to send a good notification
            if ($user->status_id == $deactivated_status->id) {
                $recent_terms_about_subject = AboutSubject::where([['subject', 'Conditions d\'utilisation'], ['status_id', $activated_status->id]])->get();
                $recent_privacy_policy_about_subject = AboutSubject::where([['subject', 'Politique de confidentialité'], ['status_id', $activated_status->id]])->get();

                if ($user->updated_at < $recent_terms_about_subject->created_at AND $user->updated_at < $recent_privacy_policy_about_subject->created_at) {
                    Notification::create([
                        'notification_url' => 'about_subjects/' . $recent_terms_about_subject->id,
                        'notification_content' => __('notifications.welcome_back_user_read_terms_and_privacy_policy'),
                        'user_id' => $user->id,
                        'status_id' => $unread_status->id
                    ]);

                } else {
                    if ($user->updated_at < $recent_terms_about_subject->created_at) {
                        Notification::create([
                            'notification_url' => 'about_subjects/' . $recent_terms_about_subject->id,
                            'notification_content' => __('notifications.welcome_back_user_read_terms'),
                            'user_id' => $user->id,
                            'status_id' => $unread_status->id
                        ]);

                    } else if ($user->updated_at < $recent_privacy_policy_about_subject->created_at) {
                        Notification::create([
                            'notification_url' => 'about_subjects/' . $recent_privacy_policy_about_subject->id,
                            'notification_content' => __('notifications.welcome_back_user_read_privacy_policy'),
                            'user_id' => $user->id,
                            'status_id' => $unread_status->id
                        ]);

                    } else {
                        Notification::create([
                            'notification_url' => 'home',
                            'notification_content' => __('notifications.welcome_back_user'),
                            'user_id' => $user->id,
                            'status_id' => $unread_status->id
                        ]);
                    }
                }
            }
        }

        // update "status_id" column
        $user->update([
            'status_id' => $status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Store seller user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function subscribeToCompany(Request $request, $id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $ongoing_status = Status::where([['status_name', 'En cours'], ['group_id', $functioning_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $user = User::find($id);

        if ($request->company_id == null OR $request->company_id == ' ') {
            return $this->handleError($request->company_id, __('validation.required'), 400);
        }

        // update "status_id" column
        $user->update([
            'company_id' => $request->company_id,
            'status_id' => $ongoing_status->id,
            'updated_at' => now()
        ]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        History::create([
            'history_url' => 'account',
            'history_content' => __('notifications.you_added_company'),
            'user_id' => $user->id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Associate roles to user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function associateRoles(Request $request, $id)
    {
        $user = User::find($id);

        if ($request->role_id != null) {
            RoleUser::create([
                'role_id' => $request->role_id,
                'user_id' => $user->id
            ]);
		}

        if ($request->roles_ids != null) {
            foreach ($request->roles_ids as $role_id) {
                RoleUser::create([
                    'role_id' => $role_id,
                    'user_id' => $user->id
                ]);
            }
		}

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Withdraw roles from user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function withdrawRoles(Request $request, $id)
    {
        $user = User::find($id);

        if ($request->role_id != null) {
            $role_user = RoleUser::where([['role_id', $request->role_id], ['user_id', $user->id]])->first();

            $role_user->delete();
        }

        if ($request->roles_ids != null) {
            foreach ($request->roles_ids as $role_id):
                $role_user = RoleUser::where([['role_id', $role_id], ['user_id', $user->id]])->first();

                $role_user->delete();
            endforeach;
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Update user prefered role in storage.
     *
     * @param  $id
     * @param  Integer  $role_id
     * @param  Integer  $status_id
     * @return \Illuminate\Http\Response
     */
    public function updatePreferedRole($id, $role_id)
    {
        $user = User::find($id);
        $all_user_roles = RoleUser::where('user_id', $user->id)->get();
        $specific_user_role = RoleUser::where([['role_id', $role_id], ['user_id', $user->id]])->first();

        foreach ($all_user_roles as $role_user):
            $role_user->update([
                'selected' => 0,
                'updated_at' => now()
            ]);
        endforeach;

        $specific_user_role->update([
			'selected' => 1,
            'updated_at' => now()
		]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Update user password in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, $id)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
		$main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
        // Get inputs
        $inputs = [
            'former_password' => $request->former_password,
            'new_password' => $request->new_password,
            'confirm_new_password' => $request->confirm_new_password
        ];
        $user = User::find($id);
        $email = Email::where([['user_id', $user->id], ['status_id', $main_status->id]])->first();
        $phone = Phone::where([['user_id', $user->id], ['status_id', $main_status->id]])->first();

        if ($inputs['former_password'] == null) {
            return $this->handleError($inputs['former_password'], __('notifications.former_password.empty'), 400);
        }

        if ($inputs['new_password'] == null) {
            return $this->handleError($inputs['new_password'], __('notifications.new_password.empty'), 400);
        }

        if ($inputs['confirm_new_password'] == null) {
            return $this->handleError($inputs['confirm_new_password'], __('notifications.confirm_new_password.empty'), 400);
        }

        if (Hash::check($inputs['former_password'], $user->password) == false) {
            return $this->handleError($inputs['former_password'], __('auth.password'), 400);
        }

        if ($inputs['confirm_new_password'] != $inputs['new_password']) {
            return $this->handleError($inputs['confirm_new_password'], __('notifications.confirm_new_password.error'), 400);
        }

        if (preg_match('#^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$#', $inputs['new_password']) == 0) {
            return $this->handleError($inputs['new_password'], __('notifications.new_password.error'), 400);
        }

        // Update password in the case user want to reset it
        PasswordReset::create([
            'email' => $email->email_content,
            'phone_code' => $phone->phone_code,
            'phone_number' => $phone->phone_number,
            'former_password' => $inputs['new_password']
        ]);

        // update "password" and "password_visible" column
        $user->update([
            'password' => Hash::make($inputs['new_password']),
            'password_visible' => $inputs['new_password'],
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_password_success'));
    }

    /**
     * Get super administrator api token in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function getApiToken()
    {
        $role = Role::where('role_name', 'Super administrateur')->first();
        $role_user = RoleUser::where('role_id', $role->id)->first();
        $user = User::find($role_user->user_id);

        return $this->handleResponse($user->api_token, __('notifications.find_api_token_success'));
    }

    /**
     * Update user api token in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateApiToken()
    {
        $role = Role::where('role_name', 'Super administrateur')->first();

        if ($role != null) {
            $role_users = RoleUser::where('role_id', $role->id)->get();

            foreach ($role_users as $role_user):
                // find user by given ID
                $user = User::find($role_user->user_id);

                // update "api_token" column
                $user->update([
                    'api_token' => Str::random(100),
                    'updated_at' => now()
                ]);

                return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));

            endforeach;

        } else {
            return $this->handleResponse(null, __('notifications.find_role_404'));
        }
    }

    /**
     * Update user avatar picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAvatarPicture(Request $request, $id)
    {
        $inputs = [
            'user_id' => $request->entity_id,
            'image_64' => $request->base64image
        ];
        // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
        $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
        // Find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $inputs['image_64']);
        $image = str_replace(' ', '+', $image);
		// Find type by name to get its ID
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
		// Find album by name to get its ID
		$avatar_album = Album::where('album_name', 'Avatars')->where('user_id', $inputs['user_id'])->first();
		// Find status by name to store its ID in "files" table
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
		$main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
		$secondary_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();

		if ($avatar_album != null) {
            // Select all files to update their status to "Secondaire"
            $avatar_images = File::where('album_id', $avatar_album->id)->where('status_id', $main_status->id)->get();

			// If status with given name exist
			if ($secondary_status != null) {
                foreach ($avatar_images as $avatar):
                    $avatar->update([
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

                    foreach ($avatar_images as $avatar):
                        $avatar->update([
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

                    foreach ($avatar_images as $avatar):
                        $avatar->update([
                            'status_id' => $status->id,
                            'updated_at' => now()
                        ]);
                    endforeach;
                }
			}

            // Create file name
			$file_name = 'images/users/' . $inputs['user_id'] . '/' . $avatar_album->id . '/' . Str::random(50) . '.png';

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, base64_decode($image)));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($file_type_group != null) {
                    $photo_type = Type::where([['type_name', 'Photo'], ['group_id', $file_type_group->id]])->first();

                    if ($photo_type != null) {
                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $photo_type->id,
                            'album_id' => $avatar_album->id,
                            'status_id' => $main_status->id
                        ]);
    
                    } else {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);
    
                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $avatar_album->id,
                            'status_id' => $main_status->id
                        ]);
                    }

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
                        'album_id' => $avatar_album->id,
                        'status_id' => $main_status->id
                    ]);
                }

			// Otherwhise, create status with necessary name
			} else {
				$status = Status::create([
					'status_name' => 'Principal',
					'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
					'group_id' => $functioning_group->id
				]);

                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($file_type_group != null) {
                    $photo_type = Type::where([['type_name', 'Photo'], ['group_id', $file_type_group->id]])->first();

                    if ($photo_type != null) {
                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $photo_type->id,
                            'album_id' => $avatar_album->id,
                            'status_id' => $status->id
                        ]);
    
                    } else {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $avatar_album->id,
                            'status_id' => $status->id
                        ]);
                    }

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
                        'album_id' => $avatar_album->id,
                        'status_id' => $status->id
                    ]);
                }
			}

		} else {
			// Store album name in "albums" table
			$album = Album::create([
				'album_name' => 'Avatars',
				'user_id' => $inputs['user_id']
			]);
			// Create file name
			$file_name = 'images/users/' . $inputs['user_id'] . '/' . $album->id . '/' . Str::random(50) . '.png';

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, base64_decode($image)));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($file_type_group != null) {
                    $photo_type = Type::where([['type_name', 'Photo'], ['group_id', $file_type_group->id]])->first();

                    if ($photo_type != null) {
                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $photo_type->id,
                            'album_id' => $album->id,
                            'status_id' => $main_status->id
                        ]);

                    } else {
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
                    }

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

			// Otherwhise, create status with necessary name
			} else {
				$status = Status::create([
					'status_name' => 'Principal',
					'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
					'group_id' => $functioning_group->id
				]);

                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($file_type_group != null) {
                    $photo_type = Type::where([['type_name', 'Photo'], ['group_id', $file_type_group->id]])->first();

                    if ($photo_type != null) {
                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $photo_type->id,
                            'album_id' => $album->id,
                            'status_id' => $status->id
                        ]);

                    } else {
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
                    }

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

		$user = User::find($id);

        $user->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }
}
