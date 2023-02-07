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

class ContinentController extends Controller
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
     * GET: Home "miscellaneous" page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('continent');
    }

    /**
     * GET: Data details
     *
     * @param  $data
     * @param  $id
     * @return \Illuminate\View\View
     */
    public function show($data, $id)
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];

        if ($data == 'role') {
            // Select role API URL
            $url = 'https://biliap-admin.dev:1443/api/role/' . $id;

            try {
                // Select role API response
                $response = $this::$client->request('GET', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $role = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'role' => $role
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        }

        if ($data == 'group') {
            // Select group API URL
            $url = 'https://biliap-admin.dev:1443/api/group/' . $id;

            try {
                // Select group API response
                $response = $this::$client->request('GET', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $group = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'group' => $group
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        }

        if ($data == 'service') {
            // Select service API URL
            $url = 'https://biliap-admin.dev:1443/api/service/' . $id;

            try {
                // Select service API response
                $response = $this::$client->request('GET', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $service = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'service' => $service
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        }
    }

    /**
     * GET: Delete data
     *
     * @param  $data
     * @param  $id
     * @return \Illuminate\View\View
     */
    public function delete($data, $id)
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];

        if ($data == 'role') {
            // Delete role API URL
            $url = 'https://biliap-admin.dev:1443/api/role/' . $id;

            try {
                // Delete role API response
                $this::$client->request('DELETE', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);

                return Redirect::route('miscellaneous.home', [
                    'deleted_message' => 'done'
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        }

        if ($data == 'group') {
            // Delete group API URL
            $url = 'https://biliap-admin.dev:1443/api/group/' . $id;

            try {
                // Delete group API response
                $this::$client->request('DELETE', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);

                return Redirect::route('miscellaneous.home', [
                    'deleted_message' => 'done'
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        }

        if ($data == 'service') {
            // Delete service API URL
            $url = 'https://biliap-admin.dev:1443/api/service/' . $id;

            try {
                // Delete service API response
                $this::$client->request('DELETE', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);

                return Redirect::route('miscellaneous.home', [
                    'deleted_message' => 'done'
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        }
    }

    // ==================================== HTTP POST METHODS ====================================
    /**
     * POST: Register a new data
     *
     * @param \Illuminate\Http\Request  $request
     * @param  $data
     * @return \Illuminate\View\View
     */
    public function store(Request $request, $data)
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];

        // Select all roles or Create new role API URL
        $url_roles = 'https://biliap-admin.dev:1443/api/role';
        // Select all groups or Create new group API URL
        $url_groups = 'https://biliap-admin.dev:1443/api/group';
        // Select all services or Create new service API URL
        $url_services = 'https://biliap-admin.dev:1443/api/service';

        if ($data == 'role') {
            // Get inputs
            $inputs = [
                'role_name' => $request->register_role_name,
                'role_description' => $request->register_role_description
            ];

            try {
                // Create role API response
                $response_new_role = $this::$client->request('POST', $url_roles, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $new_role = json_decode($response_new_role->getBody(), false);
                // Select all roles API response
                $response_roles = $this::$client->request('GET', $url_roles, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $roles = json_decode($response_roles->getBody(), false);
                // Select all groups API response
                $response_groups = $this::$client->request('GET', $url_groups, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $groups = json_decode($response_groups->getBody(), false);
                // Select all services API response
                $response_services = $this::$client->request('GET', $url_services, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $services = json_decode($response_services->getBody(), false);

                return view('miscellaneous', [
                    'created_message' => $new_role->message,
                    'roles' => $roles,
                    'groups' => $groups,
                    'services' => $services
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the role page
                // Select all roles API response
                $response_roles = $this::$client->request('GET', $url_roles, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $roles = json_decode($response_roles->getBody(), false);
                // Select all groups API response
                $response_groups = $this::$client->request('GET', $url_groups, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $groups = json_decode($response_groups->getBody(), false);
                // Select all services API response
                $response_services = $this::$client->request('GET', $url_services, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $services = json_decode($response_services->getBody(), false);

                return view('miscellaneous', [
                    'inputs' => $inputs,
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    'roles' => $roles,
                    'groups' => $groups,
                    'services' => $services
                ]);
            }
        }

        if ($data == 'group') {
            // Get inputs
            $inputs = [
                'group_name' => $request->register_group_name,
                'group_description' => $request->register_group_description
            ];

            try {
                // Create group API response
                $response_new_group = $this::$client->request('POST', $url_groups, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $new_group = json_decode($response_new_group->getBody(), false);
                // Select all roles API response
                $response_roles = $this::$client->request('GET', $url_roles, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $roles = json_decode($response_roles->getBody(), false);
                // Select all groups API response
                $response_groups = $this::$client->request('GET', $url_groups, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $groups = json_decode($response_groups->getBody(), false);
                // Select all services API response
                $response_services = $this::$client->request('GET', $url_services, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $services = json_decode($response_services->getBody(), false);

                return view('miscellaneous', [
                    'created_message' => $new_group->message,
                    'roles' => $roles,
                    'groups' => $groups,
                    'services' => $services
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                // Select all roles API response
                $response_roles = $this::$client->request('GET', $url_roles, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $roles = json_decode($response_roles->getBody(), false);
                // Select all groups API response
                $response_groups = $this::$client->request('GET', $url_groups, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $groups = json_decode($response_groups->getBody(), false);
                // Select all services API response
                $response_services = $this::$client->request('GET', $url_services, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $services = json_decode($response_services->getBody(), false);

                return view('miscellaneous', [
                    'inputs' => $inputs,
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    'roles' => $roles,
                    'groups' => $groups,
                    'services' => $services
                ]);
            }
        }

        if ($data == 'service') {
            // Get inputs
            $inputs = [
                'service_name' => $request->register_service_name,
                'phone_operator' => $request->register_phone_operator
            ];
            // Select all services or Create new service API URL
            $url = 'https://biliap-admin.dev:1443/api/service';

            try {
                // Create service API response
                $response_new_service = $this::$client->request('POST', $url, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $new_service = json_decode($response_new_service->getBody(), false);
                // Select all roles API response
                $response_roles = $this::$client->request('GET', $url_roles, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $roles = json_decode($response_roles->getBody(), false);
                // Select all groups API response
                $response_groups = $this::$client->request('GET', $url_groups, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $groups = json_decode($response_groups->getBody(), false);
                // Select all services API response
                $response_services = $this::$client->request('GET', $url_services, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $services = json_decode($response_services->getBody(), false);

                return view('miscellaneous', [
                    'created_message' => $new_service->message,
                    'roles' => $roles,
                    'groups' => $groups,
                    'services' => $services
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                // Select all roles API response
                $response_roles = $this::$client->request('GET', $url_roles, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $roles = json_decode($response_roles->getBody(), false);
                // Select all groups API response
                $response_groups = $this::$client->request('GET', $url_groups, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $groups = json_decode($response_groups->getBody(), false);
                // Select all services API response
                $response_services = $this::$client->request('GET', $url_services, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $services = json_decode($response_services->getBody(), false);

                return view('miscellaneous', [
                    'inputs' => $inputs,
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    'roles' => $roles,
                    'groups' => $groups,
                    'services' => $services
                ]);
            }
        }
    }

    /**
     * POST: Update data
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $data
     * @param  $id
     * @return \Illuminate\View\View
     */
    public function update(Request $request, $data, $id)
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];

        if ($data == 'role') {
            // Get inputs
            $inputs = [
                'id' => $request->role_id,
                'role_name' => $request->register_role_name,
                'role_description' => $request->register_role_description
            ];
            // Select role or Update role API URL
            $url = 'https://biliap-admin.dev:1443/api/role/' . $id;

            try {
                // Update role API response
                $response = $this::$client->request('PUT', $url, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $role = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'role' => $role,
                    'updated_message' => $role->message,
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                // Select role API response
                $response = $this::$client->request('GET', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $role = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    'inputs' => $inputs,
                    'role' => $role
                ]);
            }
        }

        if ($data == 'group') {
            // Get inputs
            $inputs = [
                'id' => $request->group_id,
                'group_name' => $request->register_group_name,
                'group_description' => $request->register_group_description
            ];
            // Select group or Update group API URL
            $url = 'https://biliap-admin.dev:1443/api/group/' . $id;

            try {
                // Update group API response
                $response = $this::$client->request('PUT', $url, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $group = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'group' => $group,
                    'updated_message' => $group->message,
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                // Select group API response
                $response = $this::$client->request('GET', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $group = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    'inputs' => $inputs,
                    'group' => $group
                ]);
            }
        }

        if ($data == 'service') {
            // Get inputs
            $inputs = [
                'id' => $request->service_id,
                'service_name' => $request->register_service_name,
                'phone_operator' => $request->register_phone_operator
            ];
            // Select service or Update service API URL
            $url = 'https://biliap-admin.dev:1443/api/service/' . $id;

            try {
                // Update service API response
                $response = $this::$client->request('PUT', $url, [
                    'headers' => $headers,
                    'form_params' => $inputs,
                    'verify'  => false
                ]);
                $service = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'service' => $service,
                    'updated_message' => $service->message,
                ]);

            } catch (ClientException $e) {
                // If API returns some error, display the message in the miscellaneous page
                // Select service API response
                $response = $this::$client->request('GET', $url, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $service = json_decode($response->getBody(), false);

                return view('miscellaneous', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                    'inputs' => $inputs,
                    'service' => $service
                ]);
            }
        }
    }
}
