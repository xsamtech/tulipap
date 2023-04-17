<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\Web;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\ClientException;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['changeLanguage', 'index', 'aboutUs', 'help']);
    }

    // ==================================== HTTP GET METHODS ====================================
    /**
     * GET: Change language
     *
     * @param  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLanguage($locale)
    {
        app()->setLocale($locale);
        session()->put('locale', $locale);

        return redirect()->back();
    }

    /**
     * GET: View dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!empty(Auth::user())) {
            // Client used for accessing API | Use authorization key
            $client = new Client();
            $headers = [
                'Authorization' => 'Bearer '. Auth::user()->api_token,
                'Accept' => 'application/json',
                'X-localization' => !empty(Session::get('locale')) ? Session::get('locale') : App::getLocale()
            ];
            // Select current user API URL
            $url_user = 'https://biliap-admin.dev:1443/api/user/' . Auth::user()->id;
            // Select all received messages API URL
            $url_message = 'https://biliap-admin.dev:1443/api/message/inbox/' . Auth::user()->id;

            try {
                // Select current user API response
                $response_user = $client->request('GET', $url_user, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $user = json_decode($response_user->getBody(), false);

                // Select all received messages API response
                $response_message = $client->request('GET', $url_message, [
                    'headers' => $headers,
                    'verify'  => false
                ]);
                $messages = json_decode($response_message->getBody(), false);

                return view('home', [
                    'user' => $user,
                    'messages' => $messages,
                ]);

            } catch (ClientException $e) {
                // If Select all received API returns some error, get it,
                // return to the message page and display its message
                return view('home', [
                    'response_error' => json_decode($e->getResponse()->getBody()->getContents(), false)
                ]);
            }

        } else {
            return view('welcome');
        }
    }

    /**
     * Display the About page.
     *
     * @return \Illuminate\View\View
     */
    public function aboutUs()
    {
        return view('about_us');
    }

    /**
     * Display the Help page.
     *
     * @return \Illuminate\View\View
     */
    public function help()
    {
        return view('help');
    }
}
