<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\Web;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use GuzzleHttp\Exception\ClientException;

class AccountController extends Controller
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
     * GET: Current user account
     *
     * @return \Illuminate\View\View
     */
    public function account()
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Data used to check APIs
        $history_type_group = 'Type d\'historique';
        // Create group API URL
        $url_group = 'https://biliap-admin.dev:1443/api/group';
        // Search a group API URL
        $url_search_group = 'https://biliap-admin.dev:1443/api/group/search/type';
        // Create type API URL
        $url_type = 'https://biliap-admin.dev:1443/api/type';
        // Search a type API URL
        $url_search_type = 'https://biliap-admin.dev:1443/api/type/search/hist';
        // Select user API URL
        $url_user = 'https://biliap-admin.dev:1443/api/user/' . Auth::user()->id;

        try {
            // Search a group API response
            $response_search_group = $this::$client->request('GET', $url_search_group, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $search_group = json_decode($response_search_group->getBody(), false);

            if ($search_group->data != null) {
                // Search a type API response
                $response_search_type = $this::$client->request('GET', $url_search_type, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $search_type = json_decode($response_search_type->getBody(), false);

                if ($search_type->data == null) {
                    foreach($search_group->data as $group):
                        if ($group->group_name == $history_type_group) {
                            // Create type API response
                            $this::$client->request('POST', $url_type, [
                                'headers' => $headers,
                                'form_params' => [
                                    'type_name' => 'Historique personnel',
                                    'type_description' => 'Répertoire de toutes vos activités dans le réseau.',
                                    'group_id' => $search_group->data->id,
                                ],
                                'verify'  => false
                            ]);
                        }
                    endforeach;
                }

            } else {
                // Create group API response
                $response_group = $this::$client->request('POST', $url_group, [
                    'headers' => $headers,
                    'form_params' => [
                        'group_name' => 'Type d\'historique',
                        'group_description' => 'Grouper les types qui serviront à gérer les historiques des clients et des vendeurs.'
                    ],
                    'verify'  => false
                ]);
                $new_group = json_decode($response_group->getBody(), false);
                // Search a type API response
                $response_search_type = $this::$client->request('GET', $url_search_type, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $search_type = json_decode($response_search_type->getBody(), false);

                if ($search_type->data == null) {
                    // Create type API response
                    $this::$client->request('POST', $url_type, [
                        'headers' => $headers,
                        'form_params' => [
                            'type_name' => 'Historique personnel',
                            'type_description' => 'Répertoire de toutes vos activités dans le réseau.',
                            'group_id' => $new_group->data->id,
                        ],
                        'verify'  => false
                    ]);
                }
            }

            // Select user API response
            $response_user = $this::$client->request('GET', $url_user, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $user = json_decode($response_user->getBody(), false);

            return view('account', [
                'selected_user' => $user
            ]);

        } catch (ClientException $e) {
            dd($e);
            // If Select user API returns some error, get it,
            // return to the account page and display its message
            return view('account', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
            ]);
        }
    }

    /**
     * GET: Album content
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function albumDatas($id)
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Select user API URL
        $url_user = 'https://biliap-admin.dev:1443/api/user/' . Auth::user()->id;
        // Select album API URL
        $url_album = 'https://biliap-admin.dev:1443/api/album/' . $id;

        try {
            // Select user API response
            $response_user = $this::$client->request('GET', $url_user, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $user = json_decode($response_user->getBody(), false);
            // Select album API response
            $response_album = $this::$client->request('GET', $url_album, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $album = json_decode($response_album->getBody(), false);

            return view('account', [
                'user' => $user,
                'album' => $album
            ]);

        } catch (ClientException $e) {
            // If Select user API returns some error, get it,
            // return to the account page and display its message
            return view('account', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
            ]);
        }
    }

    /**
     * GET: New album form
     *
     * @return \Illuminate\View\View
     */
    public function newAlbum()
    {
        // Get form datas
        $inputs = [
            'album_name' => date('Y') . '_' . date('m') . '_' . date('d'),
            'user_id' => Auth::user()->id
        ];
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Select user API URL
        $url_user = 'https://biliap-admin.dev:1443/api/user/' . Auth::user()->id;
        // Select all albums belonging to user
        $url_albums = 'https://biliap-admin.dev:1443/api/album/select_by_entity/user/' . Auth::user()->id;
        // Create album API URL
        $url_album = 'https://biliap-admin.dev:1443/api/album';

        try {
            // Select user API response
            $response_user = $this::$client->request('GET', $url_user, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $user = json_decode($response_user->getBody(), false);
            // Select all albums API response
            $response_albums = $this::$client->request('GET', $url_albums, [
                'headers' => $headers,
                'form_params' => $inputs,
                'verify'  => false
            ]);
            $albums = json_decode($response_albums->getBody(), false);

            foreach ($albums->data as $another_album):
                if ($another_album->album_name == $inputs['album_name']) {
                    return view('account', [
                        'user' => $user,
                        'album' => $another_album
                    ]);

                } else {
                    // Create album API response
                    $response_album = $this::$client->request('POST', $url_album, [
                        'headers' => $headers,
                        'form_params' => $inputs,
                        'verify'  => false
                    ]);
                    $album = json_decode($response_album->getBody(), false);

                    return view('account', [
                        'user' => $user,
                        'album' => $album->data
                    ]);
                }
            endforeach;

        } catch (ClientException $e) {
            // If Select user API returns some error, get it,
            // return to the account page and display its message
            return view('account', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
            ]);
        }
    }

    /**
     * GET: Image details 
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function imageDatas($id)
    {
    }

    /**
     * GET: View "update password" form
     *
     * @return \Illuminate\View\View
     */
    public function editPassword()
    {
        return view('account');
    }

    // ==================================== HTTP POST METHODS ====================================
    /**
     * POST: Authentication and authorization
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function updateAccount(Request $request)
    {
        // Get form datas
        $inputs = [
            'id' => $request->user_id,
            'firstname' => $request->register_firstname,
            'lastname' => $request->register_lastname,
            'surname' => $request->register_surname,
            'gender' => $request->register_gender,
            'email' => $request->register_email,
            'status_id' => $request->status_id,
            'roles_ids' => $request->roles,
            'phone_code' => $request->phone_code,
            'phone' => $request->phone,
            'service_id' => $request->service_id
        ];
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Update user API URL
        $url = 'https://biliap-admin.dev:1443/api/user/' . $inputs['id'];
        // FIND THE SERVICE TO ASSOCIATE WITH THE PHONE NUMBER
        $phone_service_group = 'Service téléphonique';
        $service_mpesa = 'M-Pesa';
        $service_orangemoney = 'Orange money';
        $service_airtelmoney = 'Airtel money';
        $service_afrimoney = 'Afrimoney';
        // Select service by name with group API URL
        $url_service_mpesa = 'https://biliap-admin.dev:1443/api/service/search_with_group/' . $phone_service_group . '/' . $service_mpesa;
        $url_service_orangemoney = 'https://biliap-admin.dev:1443/api/service/search_with_group/' . $phone_service_group . '/' . $service_orangemoney;
        $url_service_airtelmoney = 'https://biliap-admin.dev:1443/api/service/search_with_group/' . $phone_service_group . '/' . $service_airtelmoney;
        $url_service_afrimoney = 'https://biliap-admin.dev:1443/api/service/search_with_group/' . $phone_service_group . '/' . $service_afrimoney;

        // DEFINE THE SERVICE BY REFERRING TO THE PHONE NUMBER
        // - - - - - - - - - M-PESA
        if (substr($inputs['phone'], 0, 3) == '081' OR substr($inputs['phone'], 0, 3) == '082') {
            try {
                // Select service by name with group API response
                $response_service_mpesa = $this::$client->request('GET', $url_service_mpesa, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $service_mpesa = json_decode($response_service_mpesa->getBody(), false);

                if ($service_mpesa->data != null) {
                    // Change service ID
                    $inputs['service_id'] = $service_mpesa->data->id;

                    try {
                        // Update user API response
                        $response = $this::$client->request('PUT', $url, [
                            'headers' => $headers,
                            'form_params' => $inputs,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'updated_account_message' => $user->message
                        ]);

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }

                } else {
                    // Find group by its name API URL
                    $url_groups = 'https://biliap-admin.dev:1443/api/group/search/' . $phone_service_group;

                    try {
                        // Find group by its name API response
                        $response_groups = $this::$client->request('GET', $url_groups, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $groups = json_decode($response_groups->getBody(), false);

                        foreach ($groups->data as $group):
                            // Create service API URL
                            $url_service_mpesa = 'https://biliap-admin.dev:1443/api/service';

                            try {
                                // Find group by its name API response
                                $response_service_mpesa = $this::$client->request('POST', $url_service_mpesa, [
                                    'headers' => $headers,
                                    'form_params' => [
                                        'service_name' => 'M-Pesa',
                                        'provider' => 'Vodacom',
                                        'group_id' => $group->id
                                    ],
                                    'verify'  => false
                                ]);
                                $service_mpesa = json_decode($response_service_mpesa->getBody(), false);

                                // Change service ID
                                $inputs['service_id'] = $service_mpesa->data->id;

                                try {
                                    // Update user API response
                                    $response = $this::$client->request('PUT', $url, [
                                        'headers' => $headers,
                                        'form_params' => $inputs,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'updated_account_message' => $user->message
                                    ]);

                                } catch (ClientException $e) {
                                    // If API returns some error, get it,
                                    // return to the update_account page and display its message
                                    $response = $this::$client->request('GET', $url, [
                                        'headers' => $headers,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                        'inputs' => $inputs
                                    ]);
                                }

                            } catch (ClientException $e) {
                                // If API returns some error, get it,
                                // return to the update_account page and display its message
                                $response = $this::$client->request('GET', $url, [
                                    'headers' => $headers,
                                    'verify'  => false
                                ]);
                                $user = json_decode($response->getBody(), false);

                                return view('account', [
                                    'user' => $user,
                                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                    'inputs' => $inputs
                                ]);
                            }
                        endforeach;

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }
                }

            } catch (ClientException $e) {
                // If API returns some error, get it,
                // return to the update_account page and display its message
                return view('account', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                ]);
            }

        // - - - - - - - - - ORANGE MONEY
        } else if (substr($inputs['phone'], 0, 3) == '080' OR substr($inputs['phone'], 0, 3) == '084' OR substr($inputs['phone'], 0, 3) == '085' OR substr($inputs['phone'], 0, 3) == '088' OR substr($inputs['phone'], 0, 3) == '089') {
            try {
                // Select service by name with group API response
                $response_service_orangemoney = $this::$client->request('GET', $url_service_orangemoney, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $service_orangemoney = json_decode($response_service_orangemoney->getBody(), false);

                if ($service_orangemoney->data != null) {
                    // Change service ID
                    $inputs['service_id'] = $service_orangemoney->data->id;

                    try {
                        // Update user API response
                        $response = $this::$client->request('PUT', $url, [
                            'headers' => $headers,
                            'form_params' => $inputs,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'updated_account_message' => $user->message
                        ]);

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }

                } else {
                    // Find group by its name API URL
                    $url_groups = 'https://biliap-admin.dev:1443/api/group/search/' . $phone_service_group;

                    try {
                        // Find group by its name API response
                        $response_groups = $this::$client->request('GET', $url_groups, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $groups = json_decode($response_groups->getBody(), false);

                        foreach ($groups->data as $group):
                            // Create service API URL
                            $url_service_orangemoney = 'https://biliap-admin.dev:1443/api/service';

                            try {
                                // Find group by its name API response
                                $response_service_orangemoney = $this::$client->request('POST', $url_service_orangemoney, [
                                    'headers' => $headers,
                                    'form_params' => [
                                        'service_name' => 'Orange money',
                                        'provider' => 'Orange',
                                        'group_id' => $group->id
                                    ],
                                    'verify'  => false
                                ]);
                                $service_orangemoney = json_decode($response_service_orangemoney->getBody(), false);

                                // Change service ID
                                $inputs['service_id'] = $service_orangemoney->data->id;

                                try {
                                    // Update user API response
                                    $response = $this::$client->request('PUT', $url, [
                                        'headers' => $headers,
                                        'form_params' => $inputs,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'updated_account_message' => $user->message
                                    ]);

                                } catch (ClientException $e) {
                                    // If API returns some error, get it,
                                    // return to the update_account page and display its message
                                    $response = $this::$client->request('GET', $url, [
                                        'headers' => $headers,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                        'inputs' => $inputs
                                    ]);
                                }

                            } catch (ClientException $e) {
                                // If API returns some error, get it,
                                // return to the update_account page and display its message
                                $response = $this::$client->request('GET', $url, [
                                    'headers' => $headers,
                                    'verify'  => false
                                ]);
                                $user = json_decode($response->getBody(), false);

                                return view('account', [
                                    'user' => $user,
                                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                    'inputs' => $inputs
                                ]);
                            }
                        endforeach;

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }
                }

            } catch (ClientException $e) {
                // If API returns some error, get it,
                // return to the update_account page and display its message
                return view('account', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                ]);
            }

        // - - - - - - - - - AIRTEL MONEY
        } else if (substr($inputs['phone'], 0, 3) == '099') {
            try {
                // Select service by name with group API response
                $response_service_airtelmoney = $this::$client->request('GET', $url_service_airtelmoney, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $service_airtelmoney = json_decode($response_service_airtelmoney->getBody(), false);

                if ($service_airtelmoney->data != null) {
                    // Change service ID
                    $inputs['service_id'] = $service_airtelmoney->data->id;

                    try {
                        // Update user API response
                        $response = $this::$client->request('PUT', $url, [
                            'headers' => $headers,
                            'form_params' => $inputs,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'updated_account_message' => $user->message
                        ]);

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }

                } else {
                    // Find group by its name API URL
                    $url_groups = 'https://biliap-admin.dev:1443/api/group/search/' . $phone_service_group;

                    try {
                        // Find group by its name API response
                        $response_groups = $this::$client->request('GET', $url_groups, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $groups = json_decode($response_groups->getBody(), false);

                        foreach ($groups->data as $group):
                            // Create service API URL
                            $url_service_airtelmoney = 'https://biliap-admin.dev:1443/api/service';

                            try {
                                // Find group by its name API response
                                $response_service_airtelmoney = $this::$client->request('POST', $url_service_airtelmoney, [
                                    'headers' => $headers,
                                    'form_params' => [
                                        'service_name' => 'Airtel money',
                                        'provider' => 'Airtel',
                                        'group_id' => $group->id
                                    ],
                                    'verify'  => false
                                ]);
                                $service_airtelmoney = json_decode($response_service_airtelmoney->getBody(), false);

                                // Change service ID
                                $inputs['service_id'] = $service_airtelmoney->data->id;

                                try {
                                    // Update user API response
                                    $response = $this::$client->request('PUT', $url, [
                                        'headers' => $headers,
                                        'form_params' => $inputs,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'updated_account_message' => $user->message
                                    ]);

                                } catch (ClientException $e) {
                                    // If API returns some error, get it,
                                    // return to the update_account page and display its message
                                    $response = $this::$client->request('GET', $url, [
                                        'headers' => $headers,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                        'inputs' => $inputs
                                    ]);
                                }

                            } catch (ClientException $e) {
                                // If API returns some error, get it,
                                // return to the update_account page and display its message
                                $response = $this::$client->request('GET', $url, [
                                    'headers' => $headers,
                                    'verify'  => false
                                ]);
                                $user = json_decode($response->getBody(), false);

                                return view('account', [
                                    'user' => $user,
                                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                    'inputs' => $inputs
                                ]);
                            }
                        endforeach;

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }
                }

            } catch (ClientException $e) {
                // If API returns some error, get it,
                // return to the update_account page and display its message
                return view('account', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                ]);
            }

        // - - - - - - - - - AFRIMONEY
        } else if (substr($inputs['phone'], 0, 3) == '090') {
            try {
                // Select service by name with group API response
                $response_service_afrimoney = $this::$client->request('GET', $url_service_afrimoney, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $service_afrimoney = json_decode($response_service_afrimoney->getBody(), false);

                if ($service_afrimoney->data != null) {
                    // Change service ID
                    $inputs['service_id'] = $service_afrimoney->data->id;

                    try {
                        // Update user API response
                        $response = $this::$client->request('PUT', $url, [
                            'headers' => $headers,
                            'form_params' => $inputs,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'updated_account_message' => $user->message
                        ]);

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }

                } else {
                    // Find group by its name API URL
                    $url_groups = 'https://biliap-admin.dev:1443/api/group/search/' . $phone_service_group;

                    try {
                        // Find group by its name API response
                        $response_groups = $this::$client->request('GET', $url_groups, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $groups = json_decode($response_groups->getBody(), false);

                        foreach ($groups->data as $group):
                            // Create service API URL
                            $url_service_afrimoney = 'https://biliap-admin.dev:1443/api/service';

                            try {
                                // Find group by its name API response
                                $response_service_afrimoney = $this::$client->request('POST', $url_service_afrimoney, [
                                    'headers' => $headers,
                                    'form_params' => [
                                        'service_name' => 'Afrimoney',
                                        'provider' => 'Africell',
                                        'group_id' => $group->id
                                    ],
                                    'verify'  => false
                                ]);
                                $service_afrimoney = json_decode($response_service_afrimoney->getBody(), false);

                                // Change service ID
                                $inputs['service_id'] = $service_afrimoney->data->id;

                                try {
                                    // Update user API response
                                    $response = $this::$client->request('PUT', $url, [
                                        'headers' => $headers,
                                        'form_params' => $inputs,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'updated_account_message' => $user->message
                                    ]);

                                } catch (ClientException $e) {
                                    // If API returns some error, get it,
                                    // return to the update_account page and display its message
                                    $response = $this::$client->request('GET', $url, [
                                        'headers' => $headers,
                                        'verify'  => false
                                    ]);
                                    $user = json_decode($response->getBody(), false);

                                    return view('account', [
                                        'user' => $user,
                                        'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                        'inputs' => $inputs
                                    ]);
                                }

                            } catch (ClientException $e) {
                                // If API returns some error, get it,
                                // return to the update_account page and display its message
                                $response = $this::$client->request('GET', $url, [
                                    'headers' => $headers,
                                    'verify'  => false
                                ]);
                                $user = json_decode($response->getBody(), false);

                                return view('account', [
                                    'user' => $user,
                                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                                    'inputs' => $inputs
                                ]);
                            }
                        endforeach;

                    } catch (ClientException $e) {
                        // If API returns some error, get it,
                        // return to the update_account page and display its message
                        $response = $this::$client->request('GET', $url, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $user = json_decode($response->getBody(), false);

                        return view('account', [
                            'user' => $user,
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                            'inputs' => $inputs
                        ]);
                    }
                }

            } catch (ClientException $e) {
                // If API returns some error, get it,
                // return to the update_account page and display its message
                return view('account', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                ]);
            }

        } else {
            try {
                // API response
                $response = $this::$client->request('PUT', $url, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $user = json_decode($response->getBody(), false);

                return view('account', [
                    'user' => $user,
                    'updated_account_message' => $user->message
                ]);

            } catch (ClientException $e) {
                // If API returns some error, get it,
                // return to the update_account page and display its message
                $response = $this::$client->request('GET', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $user = json_decode($response->getBody(), false);

                return view('account', [
                    'user' => $user,
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    'inputs' => $inputs
                ]);
            }
        }
    }

    /**
     * POST: Update password
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function updatePassword(Request $request)
    {
        // Get form datas
        $inputs = [
            'id' => $request->user_id,
            'former_password' => $request->former_password,
            'new_password' => $request->new_password,
            'confirm_new_password' => $request->confirm_new_password
        ];
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Update password API URL
        $url_update = 'https://biliap-admin.dev:1443/api/user/update_password/' . $inputs['id'];

        try {
            // API response
            $response = $this::$client->request('PUT', $url_update, [
                'headers' => $headers,
                'form_params' => $inputs,
                'verify'  => false
            ]);
            $user = json_decode($response->getBody(), false);

            return Redirect::route('account', [
                'updated_password_message' => $user->message
            ]);

        } catch (ClientException $e) {
            // If Update password API returns some error, get it,
            // return to the update_password page and display its message
            return view('account', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                'inputs' => $inputs
            ]);
        }
    }
}
