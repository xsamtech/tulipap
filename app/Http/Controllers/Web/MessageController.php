<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\Web;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;

class MessageController extends Controller
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
     * GET: View received messages list
     *
     * @return \Illuminate\View\View
     */
    public function receivedMessages()
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];

        foreach (Auth::user()->role_users as $role_user):
            $role_for_company_group = 'Rôle pour la société';
            $admin_role = 'Administrateur';
            // Select role by name with group API URL
            $url_admin_role = 'https://biliap-admin.dev:1443/api/role/search_with_group/' . $role_for_company_group . '/' . $admin_role;

            try {
                // API response
                $response_admin_role = $this::$client->request('GET', $url_admin_role, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $admin_role = json_decode($response_admin_role->getBody(), false);

                if ($role_user->role->id == $admin_role->data->id) {
                    // Select all received messages API URL
                    $url_inbox = 'https://biliap-admin.dev:1443/api/message/inbox/ROL/' . $role_user->role->id;

                    try {
                        // API response
                        $response_inbox = $this::$client->request('GET', $url_inbox, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $messages = json_decode($response_inbox->getBody(), false);

                        return view('message', [
                            'messages' => $messages
                        ]);

                    } catch (ClientException $e) {
                        // If API returns some error, display the message in the message page
                        return view('message', [
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                        ]);
                    }
                }

            } catch (ClientException $e) {
                // If API returns some error, display the message in the message page
                return view('message', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        endforeach;
    }

    /**
     * GET: View sent messages list
     *
     * @return \Illuminate\View\View
     */
    public function sentMessages()
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Select all received messages API URL
        $url = 'https://biliap-admin.dev:1443/api/message/outbox/USR/' . Auth::user()->id;

        try {
            // API response
            $response = $this::$client->request('GET', $url, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $messages = json_decode($response->getBody(), false);

            if ($messages->data != null) {
                foreach ($messages->data as $message):
                    if (substr($message->sent_to, 0, 3) == 'USR') {
                        $addressee_id = substr($message->sent_to, 4);
                        $url_user = 'https://biliap-admin.dev:1443/api/user/' . $addressee_id;
    
                        try {
                            // API response
                            $response_user = $this::$client->request('GET', $url_user, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $addressee = json_decode($response_user->getBody(), false);
    
                            return view('message', [
                                'messages' => $messages,
                                'addressee' => $addressee->data
                            ]);
     
                        } catch (ClientException $e) {
                            // If API returns some error, display the message in the message page
                            return view('message', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                            ]);
                        }
                    }
    
                    if (substr($message->sent_to, 0, 3) == 'SLR') {
                        $addressee_id = substr($message->sent_to, 4);
                        $url_seller = 'https://biliap-admin.dev:1443/api/seller/' . $addressee_id;
    
                        try {
                            // API response
                            $response_seller = $this::$client->request('GET', $url_seller, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $addressee = json_decode($response_seller->getBody(), false);
    
                            return view('message', [
                                'messages' => $messages,
                                'addressee' => $addressee->data
                            ]);
     
                        } catch (ClientException $e) {
                            // If API returns some error, display the message in the message page
                            return view('message', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                            ]);
                        }
                    }
    
                    if (substr($message->sent_to, 0, 3) == 'GRP') {
                        $addressee_id = substr($message->sent_to, 4);
                        $url_msg_group = 'https://biliap-admin.dev:1443/api/message_group/' . $addressee_id;
    
                        try {
                            // API response
                            $response_msg_group = $this::$client->request('GET', $url_msg_group, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $addressee = json_decode($response_msg_group->getBody(), false);
    
                            return view('message', [
                                'messages' => $messages,
                                'addressee' => $addressee->data
                            ]);
     
                        } catch (ClientException $e) {
                            // If API returns some error, display the message in the message page
                            return view('message', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                            ]);
                        }
                    }
                endforeach;

            } else {
                return view('message', [
                    'messages' => $messages
                ]);
            }

        } catch (ClientException $e) {
            // If API returns some error, display the message in the message page
            return view('message', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
            ]);
        }
    }

    /**
     * GET: View drafts messages list
     *
     * @return \Illuminate\View\View
     */
    public function draftsMessages()
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Select all dratfs API URL
        $url = 'https://biliap-admin.dev:1443/api/message/drafts/user/' . Auth::user()->id;

        try {
            // API response
            $response = $this::$client->request('GET', $url, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $messages = json_decode($response->getBody(), false);

            if ($messages->data != null) {
                foreach ($messages->data as $message):
                    if (substr($message->sent_to, 0, 3) == 'USR') {
                        $addressee_id = substr($message->sent_to, 4);
                        $url_user = 'https://biliap-admin.dev:1443/api/user/' . $addressee_id;
    
                        try {
                            // API response
                            $response_user = $this::$client->request('GET', $url_user, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $addressee = json_decode($response_user->getBody(), false);
    
                            return view('message', [
                                'messages' => $messages,
                                'addressee' => $addressee->data
                            ]);
     
                        } catch (ClientException $e) {
                            // If API returns some error, display the message in the message page
                            return view('message', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                            ]);
                        }
                    }
    
                    if (substr($message->sent_to, 0, 3) == 'SLR') {
                        $addressee_id = substr($message->sent_to, 4);
                        $url_seller = 'https://biliap-admin.dev:1443/api/seller/' . $addressee_id;
    
                        try {
                            // API response
                            $response_seller = $this::$client->request('GET', $url_seller, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $addressee = json_decode($response_seller->getBody(), false);
    
                            return view('message', [
                                'messages' => $messages,
                                'addressee' => $addressee->data
                            ]);
     
                        } catch (ClientException $e) {
                            // If API returns some error, display the message in the message page
                            return view('message', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                            ]);
                        }
                    }
    
                    if (substr($message->sent_to, 0, 3) == 'GRP') {
                        $addressee_id = substr($message->sent_to, 4);
                        $url_msg_group = 'https://biliap-admin.dev:1443/api/message_group/' . $addressee_id;
    
                        try {
                            // API response
                            $response_msg_group = $this::$client->request('GET', $url_msg_group, [
                                'headers' => $headers,
                                'verify'  => false
                            ]);
                            $addressee = json_decode($response_msg_group->getBody(), false);
    
                            return view('message', [
                                'messages' => $messages,
                                'addressee' => $addressee->data
                            ]);
     
                        } catch (ClientException $e) {
                            // If API returns some error, display the message in the message page
                            return view('message', [
                                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                            ]);
                        }
                    }
                endforeach;

            } else {
                return view('message', [
                    'messages' => $messages
                ]);
            }

        } catch (ClientException $e) {
            // If API returns some error, display the message in the message page
            return view('message', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
            ]);
        }
    }

    /**
     * GET: View spams messages list
     *
     * @return \Illuminate\View\View
     */
    public function spamsMessages()
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];

        foreach (Auth::user()->role_users as $role_user):
            $role_for_company_group = 'Rôle pour la société';
            $admin_role = 'Administrateur';
            // Select role by group API URL
            $url_admin_role = 'https://biliap-admin.dev:1443/api/role/search_with_group/' . $role_for_company_group . '/' . $admin_role;

            try {
                // API response
                $response_admin_role = $this::$client->request('GET', $url_admin_role, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $admin_role = json_decode($response_admin_role->getBody(), false);

                if ($role_user->role->id == $admin_role->data->id) {
                    // Select all spams API URL
                    $url_inbox = 'https://biliap-admin.dev:1443/api/message/spams/ROL/' . $role_user->role->id;

                    try {
                        // API response
                        $response_inbox = $this::$client->request('GET', $url_inbox, [
                            'headers' => $headers,
                            'verify'  => false
                        ]);
                        $messages = json_decode($response_inbox->getBody(), false);

                        return view('message', [
                            'messages' => $messages
                        ]);

                    } catch (ClientException $e) {
                        // If API returns some error, display the message in the message page
                        return view('message', [
                            'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                        ]);
                    }
                }

            } catch (ClientException $e) {
                // If API returns some error, display the message in the message page
                return view('message', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }
        endforeach;
    }

    /**
     * GET: View message details
     *
     * @param mixed  $id
     * @return \Illuminate\View\View
     */
    public function showMessage($id)
    {
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Select message API URL
        $url = 'https://biliap-admin.dev:1443/api/message/' . $id;

        try {
            // API response
            $response = $this::$client->request('GET', $url, [
                'headers' => $headers,
                'verify'  => false
            ]);
            $message = json_decode($response->getBody(), false);

            return view('message', [
                'message' => $message
            ]);

        } catch (ClientException $e) {
            // If API returns some error, display the message in the message page
            return view('message', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
            ]);
        }
    }

    /**
     * GET: View "new message" form
     *
     * @return \Illuminate\View\View
     */
    public function newMessage()
    {
        return view('message');
    }

    // ==================================== HTTP POST METHODS ====================================
    /**
     * POST: Create message
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function storeMessage(Request $request)
    {
        // Get form datas
        $inputs = [
            'id' => $request->user_id,
        ];
        // Get header informations
        $headers = [
            'Authorization' => 'Bearer '. Auth::user()->api_token,
            'Accept' => 'application/json',
            'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
        ];
        // Create message API URL
        $url_update = '';

        try {
            // API response
            $response = $this::$client->request('POST', $url_update, [
                'headers' => $headers,
                'form_params' => $inputs,
                'verify'  => false
            ]);
            $message = json_decode($response->getBody(), false);

            return Redirect::route('messages.outbox', [
                'created_message' => $message->message
            ]);

        } catch (ClientException $e) {
            // If API returns some error, display the message in the message page
            return view('message', [
                'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false),
                'inputs' => $inputs
            ]);
        }
    }

    /**
     * GET: Delete a message
     *
     * @return \Illuminate\View\View
     */
    public function deleteMessage()
    {
        return view('message');
    }

    /**
     * GET: Cancel delete a message
     *
     * @return \Illuminate\View\View
     */
    public function cancelDeleteMessage()
    {
        return view('message');
    }
}
