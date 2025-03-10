<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\Auth;

use App\Models\Group;
use App\Models\Icon;
use App\Models\PasswordReset;
use App\Models\Phone;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class RegisteredUserController extends Controller
{
    public static $client;

    public function __construct()
    {
        // Client used for accessing API | Use authorization key
        $this::$client = new Client();

        $this->middleware('auth');
    }

    // ==================================== HTTP GET METHODS ====================================
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Display the registration view.
     */
    public function createEntity($entity): View
    {
        if ($entity == 'admin') {
            return view('auth.register', [
                'entity' => $entity
            ]);
        }

        if ($entity == 'superadmin') {
            $users = User::all();

            // Ensure that the ROLE, the SERVICE, the STATUS are registered
            if ($users->count() > 0) {
                return view('errors.403');

            } else {
                if (Role::all()->count() == 0) {
                    $superadmin_role = Role::create([
                        'role_name' => 'Super administrateur',
                        'role_description' => 'Gestion des données qui permettent le fonctionnement de Tulipap et de ses services.'
                    ]);

                    // Add an icon for the registered role
                    Icon::create([
                        'icon_name' => 'ri-user-settings-line',
                        'role_id' => $superadmin_role->id
                    ]);
                }

                if (Service::all()->count() == 0) {
                    $phone_service_group = Group::where('group_name', 'Service téléphonique')->first();

                    if ($phone_service_group != null) {
                        Service::create([
                            'service_name' => 'M-PESA',
                            'provider' => 'Vodacom',
                            'group_id' => $phone_service_group->id
                        ]);

                    } else {
                        $group = Group::create([
                            'group_name' => 'Service téléphonique',
                            'group_description' => 'Grouper les services qui serviront à gérer les transferts d\'argent par mobile-money et autre.'
                        ]);

                        Service::create([
                            'service_name' => 'M-PESA',
                            'provider' => 'Vodacom',
                            'group_id' => $group->id
                        ]);
                    }

                }

                if (Status::all()->count() == 0) {
                    $functioning_group = Group::where('group_name', 'Fonctionnement')->first();

                    if ($functioning_group != null) {
                        Status::create([
                            'status_name' => 'Activé',
                            'status_description' => 'Fonctionnement normal dans tous les espaces des applications.',
                            'group_id' => $functioning_group->id
                        ]);
                        Status::create([
                            'status_name' => 'Principal',
                            'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
                            'group_id' => $functioning_group->id
                        ]);

                    } else {
                        $group = Group::create([
                            'group_name' => 'Fonctionnement',
                            'group_description' => 'Grouper les états permettant aux utilisateurs et autres de fonctionner normalement, ou de manière restreinte, ou encore de ne pas fonctionner du tout.'
                        ]);

                        Status::create([
                            'status_name' => 'Activé',
                            'status_description' => 'Fonctionnement normal dans tous les espaces des applications.',
                            'group_id' => $group->id
                        ]);
                        Status::create([
                            'status_name' => 'Principal',
                            'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
                            'group_id' => $group->id
                        ]);
                    }
                }
            }

            $phone_service_group = Group::where('group_name', 'Service téléphonique')->first();
            $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
            $superadmin = Role::where('role_name', 'Super administrateur')->first();
            $m_pesa_service = Service::where([['service_name', 'M-PESA'], ['group_id', $phone_service_group->id]])->first();
            $activated_status = Status::where([['status_name', 'Activé'], ['group_id', $functioning_group->id]])->first();
            $main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();

            return view('auth.register', [
                'entity' => $entity,
                'superadmin_role_id' => $superadmin->id,
                'm_pesa_service_id' => $m_pesa_service->id,
                'activated_status_id' => $activated_status->id,
                'main_status_id' => $main_status->id
            ]);
        }
    }

    // ==================================== HTTP POST METHODS ====================================
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (isset($request->object) AND $request->object == 'superadmin') {
            // Validate form
            $request->validate([
                'register_firstname' => ['required', 'string', 'max:255'],
                'register_phone' => ['required', 'regex:/^0[1-9][0-9]([-. ]?[0-9]{2,3}){3,4}$/', 'numeric', 'min:10', 'unique:phones,phone_number'],
                'register_email' => ['required', 'regex:/^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/', 'unique:emails,email_content'],
                'register_password' => ['required', 'regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', 'confirmed', Rules\Password::defaults()]
            ]);

            // Register USER adding registered STATUS id
            $user = User::create([
                'firstname' => $request->register_firstname,
                'lastname' => $request->register_lastname,
                'surname' => $request->register_surname,
                'gender' => $request->register_gender,
                'birthdate' => $request->register_birthdate,
                'password' => Hash::make($request->register_password),
                'password_visible' => $request->register_password,
                'api_token' => Str::random(100),
                'status_id' => $request->user_status_id
            ]);

            // Associate USER to registered ROLE
            RoleUser::create([
                'role_id' => $request->role_id,
                'user_id' => $user->id,
                'selected' => 1
            ]);

            // Associate USER to registered E-MAIL
            $email = Phone::create([
                'email_content' => $request->email,
                'status_id' => $request->email_status_id,
                'user_id' => $user->id
            ]);

            // Associate USER to registered PHONE
            $phone = Phone::create([
                'phone_code' => $request->register_phone_code,
                'phone_number' => $request->register_phone,
                'service_id' => $request->service_id,
                'user_id' => $user->id,
                'status_id' => $request->phone_status_id
            ]);

            // Register password in the case user want to reset it
            PasswordReset::create([
                'email' => $email->email_content,
                'phone_code' => $phone->phone_code,
                'phone_number' => $phone->phone_number,
                'former_password' => $request->register_password
            ]);

            event(new Registered($user));

            /*
                GET NEW USER HISTORY AND MESSAGES
            */
            $headers = [
                'Authorization' => 'Bearer '. $user->api_token,
                'Accept' => 'application/json',
                'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
            ];
            // Select user API URL
            $url_current_user = 'https://biliap-admin.dev:1443/api/user/' . $user->id;

            try {
                // user API response used for received messages
                $response_current_user = $this::$client->request('GET', $url_current_user, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $current_user = json_decode($response_current_user->getBody(), false);

                // Put API response as an authenticatable object "user"
                session()->put('current_user', $current_user->data);

                // NOTIFICATIONS
                // Select user unread notifications API URL
                $url_unread_notifications = 'https://biliap-admin.dev:1443/api/notification/select_unread_by_user/' . $current_user->data->id;

                try {
                    // Select user unread notifications API response
                    $response_unread_notifications = $this::$client->request('GET', $url_unread_notifications, [
                        'headers' => $headers,
                        'verify'  => false
                    ]);
                    $unread_notifications = json_decode($response_unread_notifications->getBody(), false);

                    // Put "received messages" API response in the session
                    session()->put('unread_notifications', $unread_notifications);

                } catch (ClientException $e) {
                    return view('auth.login', [
                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    ]);
                }

                // HISTORY
                // Select type by name API URL
                $activity_history_name = 'Historique des activités';
                $url_search_type = 'https://biliap-admin.dev:1443/api/type/search/' . $activity_history_name;

                try {
                    // Select role by name API response
                    $response_search_type = $this::$client->request('GET', $url_search_type, [
                        'headers' => $headers,
                        'verify'  => false
                    ]);
                    $types = json_decode($response_search_type->getBody(), false);

                    foreach ($types->data as $type):
                        // Select user history by type API URL
                        $url_activity_history = 'https://biliap-admin.dev:1443/api/history/select_by_type/' . $current_user->data->id . '/' . $type->id;

                        try {
                            // Select user history API response
                            $response_activity_history = $this::$client->request('GET', $url_activity_history, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $activity_history = json_decode($response_activity_history->getBody(), false);

                            // Put "activity history" API response in the session
                            session()->put('activity_history', $activity_history);

                        } catch (ClientException $e) {
                            return view('auth.login', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            ]);
                        }
                    endforeach;

                } catch (ClientException $e) {
                    return view('auth.login', [
                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    ]);
                }

                // MESSAGES
                foreach ($current_user->role_users as $role_user):
                    $superadmin_name = 'Super administrateur';
                    // Select role by name API URL
                    $url_search_role = 'https://biliap-admin.dev:1443/api/role/search/' . $superadmin_name;

                    try {
                        // Select role by name API response
                        $response_search_role = $this::$client->request('GET', $url_search_role, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $roles = json_decode($response_search_role->getBody(), false);

                        foreach ($roles->data as $role):
                            if ($role_user->role_id == $role->id) {
                                // Select received messages API URL
                                $url_message = 'https://biliap-admin.dev:1443/api/message/unread_inbox/' . $role->role_name;

                                try {
                                    // Select received messages API response
                                    $response_message = $this::$client->request('GET', $url_message, [
                                        'headers' => $headers,
                                        'verify'  => false
                                    ]);
                                    $messages = json_decode($response_message->getBody(), false);

                                    // Put "received messages" API response in the session
                                    session()->put('unread_messages', $messages);

                                } catch (ClientException $e) {
                                    return view('auth.login', [
                                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                    ]);
                                }
                            }
                        endforeach;

                    } catch (ClientException $e) {
                        return view('auth.login', [
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                        ]);
                    }
                endforeach;

            } catch (ClientException $e) {
                return view('auth.login', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                ]);
            }

            // If everything is ok, connect the user adding necessary datas like new messages
            Auth::login($user);

            return redirect('/');
        }

        if (isset($request->object) AND $request->object == 'admin') {
            // Get inputs
            $inputs = [
                // User datas
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'surname' => $request->surname,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
                'password' => $request->password,
                'password_visible' => $request->password_visible,
                'confirm_password' => $request->confirm_password,
                'status_id' => $request->status_id,
                // Add user role
                'role_id' => $request->role_id,
                // Add user address
                'number' => $request->number,
                'street' => $request->street,
                'neighborhood_id' => $request->neighborhood_id,
                'area_id' => $request->area_id,
                // Add user e-mail
                'email_content' => $request->email_content,
                'email_status_id' => $request->email_status_id,
                'phone_code' => $request->phone_code,
                // Add user phone number
                'phone_number' => $request->phone_number,
                'phone_service_id' => $request->phone_service_id,
                'phone_status_id' => $request->phone_status_id,
            ];

            $headers = [
                'Authorization' => 'Bearer '. $request->devref,
                'Accept' => 'application/json',
                'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
            ];
            // Register user (admin) API URL
            $url_current_user = 'https://biliap-admin.dev:1443/api/user';

            try {
                // user API response used for received messages
                $response_current_user = $this::$client->request('POST', $url_current_user, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $current_user = json_decode($response_current_user->getBody(), false);

                /*
                    GET NEW USER NOTIFICATIONS, HISTORY AND MESSAGES
                */
                // NOTIFICATIONS
                // Select user unread notifications API URL
                $url_unread_notifications = 'https://biliap-admin.dev:1443/api/notification/select_unread_by_user/' . $current_user->data->id;

                try {
                    // Select user unread notifications API response
                    $response_unread_notifications = $this::$client->request('GET', $url_unread_notifications, [
                        'headers' => $headers,
                        'verify'  => false
                    ]);
                    $unread_notifications = json_decode($response_unread_notifications->getBody(), false);

                    // Put "received messages" API response in the session
                    session()->put('unread_notifications', $unread_notifications);

                } catch (ClientException $e) {
                    return view('auth.login', [
                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    ]);
                }

                // HISTORY
                // Select type by name API URL
                $activity_history_name = 'Historique des activités';
                $url_search_type = 'https://biliap-admin.dev:1443/api/type/search/' . $activity_history_name;

                try {
                    // Select role by name API response
                    $response_search_type = $this::$client->request('GET', $url_search_type, [
                        'headers' => $headers,
                        'verify'  => false
                    ]);
                    $types = json_decode($response_search_type->getBody(), false);

                    foreach ($types->data as $type):
                        // Select user history by type API URL
                        $url_activity_history = 'https://biliap-admin.dev:1443/api/history/select_by_type/' . $current_user->data->id . '/' . $type->id;

                        try {
                            // Select user history API response
                            $response_activity_history = $this::$client->request('GET', $url_activity_history, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $activity_history = json_decode($response_activity_history->getBody(), false);

                            // Put "activity history" API response in the session
                            session()->put('activity_history', $activity_history);

                        } catch (ClientException $e) {
                            return view('auth.login', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            ]);
                        }
                    endforeach;

                } catch (ClientException $e) {
                    return view('auth.login', [
                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    ]);
                }

                // MESSAGES
                foreach ($current_user->role_users as $role_user):
                    $admin_name = 'Administrateur';
                    // Select role by name API URL
                    $url_search_role = 'https://biliap-admin.dev:1443/api/role/search/' . $admin_name;

                    try {
                        // Select role by name API response
                        $response_search_role = $this::$client->request('GET', $url_search_role, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $roles = json_decode($response_search_role->getBody(), false);

                        foreach ($roles->data as $role):
                            if ($role_user->role_id == $role->id) {
                                // Select received messages API URL
                                $url_message = 'https://biliap-admin.dev:1443/api/message/unread_inbox/' . $role->role_name;

                                try {
                                    // Select received messages API response
                                    $response_message = $this::$client->request('GET', $url_message, [
                                        'headers' => $headers,
                                        'verify'  => false
                                    ]);
                                    $messages = json_decode($response_message->getBody(), false);

                                    // Put "received messages" API response in the session
                                    session()->put('unread_messages', $messages);

                                } catch (ClientException $e) {
                                    return view('auth.login', [
                                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                    ]);
                                }
                            }
                        endforeach;

                    } catch (ClientException $e) {
                        return view('auth.login', [
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                        ]);
                    }
                endforeach;

                // If everything is ok, connect the user adding necessary datas like new messages
                Auth::login($current_user->data);

                return view('auth.login');

            } catch (ClientException $e) {
                return view('auth.login', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                ]);
            }
        }

        if (isset($request->object) AND $request->object == 'customer') {
            // If everything is ok, connect the user adding necessary datas like new messages
            // Auth::login($user);

            return redirect('/');
        }
    }
}
