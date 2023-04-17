<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Default API resource
Route::middleware(['auth:api', 'localization'])->group(function () {
    Route::apiResource('about_subject', 'App\Http\Controllers\API\AboutSubjectController');
    Route::apiResource('about_title', 'App\Http\Controllers\API\AboutTitleController');
    Route::apiResource('about_content', 'App\Http\Controllers\API\AboutContentController');
    Route::apiResource('type', 'App\Http\Controllers\API\TypeController');
    Route::apiResource('album', 'App\Http\Controllers\API\AlbumController');
    Route::apiResource('file', 'App\Http\Controllers\API\FileController');
    Route::apiResource('icon', 'App\Http\Controllers\API\IconController');
    Route::apiResource('group', 'App\Http\Controllers\API\GroupController');
    Route::apiResource('status', 'App\Http\Controllers\API\StatusController');
    Route::apiResource('service', 'App\Http\Controllers\API\ServiceController');
    Route::apiResource('continent', 'App\Http\Controllers\API\ContinentController');
    Route::apiResource('region', 'App\Http\Controllers\API\RegionController');
    Route::apiResource('country', 'App\Http\Controllers\API\CountryController');
    Route::apiResource('province', 'App\Http\Controllers\API\ProvinceController');
    Route::apiResource('city', 'App\Http\Controllers\API\CityController');
    Route::apiResource('area', 'App\Http\Controllers\API\AreaController');
    Route::apiResource('neighborhood', 'App\Http\Controllers\API\NeighborhoodController');
    Route::apiResource('address', 'App\Http\Controllers\API\AddressController');
    Route::apiResource('phone', 'App\Http\Controllers\API\PhoneController');
    Route::apiResource('email', 'App\Http\Controllers\API\EmailController');
    Route::apiResource('bank_code', 'App\Http\Controllers\API\BankCodeController');
    Route::apiResource('social_network', 'App\Http\Controllers\API\SocialNetworkController');
    Route::apiResource('currency', 'App\Http\Controllers\API\CurrencyController');
    Route::apiResource('exchange_rate', 'App\Http\Controllers\API\ExchangeRateController');
    Route::apiResource('billing_method', 'App\Http\Controllers\API\BillingMethodController');
    Route::apiResource('role', 'App\Http\Controllers\API\RoleController');
    Route::apiResource('user', 'App\Http\Controllers\API\UserController');
    Route::apiResource('role_user', 'App\Http\Controllers\API\RoleUserController');
    Route::apiResource('password_reset', 'App\Http\Controllers\API\PasswordResetController');
    Route::apiResource('session', 'App\Http\Controllers\API\SessionController');
    Route::apiResource('company', 'App\Http\Controllers\API\CompanyController');
    Route::apiResource('office', 'App\Http\Controllers\API\OfficeController');
    Route::apiResource('cart', 'App\Http\Controllers\API\CartController');
    Route::apiResource('invoice', 'App\Http\Controllers\API\InvoiceController');
    Route::apiResource('prepaid_card', 'App\Http\Controllers\API\PrepaidCardController');
    Route::apiResource('message', 'App\Http\Controllers\API\MessageController');
    Route::apiResource('notification', 'App\Http\Controllers\API\NotificationController');
    Route::apiResource('history', 'App\Http\Controllers\API\HistoryController');
    Route::apiResource('preference', 'App\Http\Controllers\API\PreferenceController');
    Route::apiResource('email_notification', 'App\Http\Controllers\API\EmailNotificationController');
    Route::apiResource('sms_notification', 'App\Http\Controllers\API\SmsNotificationController');
});
// Custom API resource
Route::group(['middleware' => ['api', 'localization']], function () {
    Route::resource('user', 'App\Http\Controllers\API\UserController');

    Route::get('user/get_api_token', 'App\Http\Controllers\API\UserController@getApiToken')->name('user.get_api_token');
    Route::put('user/update_api_token', 'App\Http\Controllers\API\UserController@updateApiToken')->name('user.update_api_token');
    Route::post('user/login', 'App\Http\Controllers\API\UserController@login')->name('user.login');
});
Route::group(['middleware' => ['api', 'auth:api', 'localization']], function () {
    Route::resource('about_subject', 'App\Http\Controllers\API\AboutSubjectController');
    Route::resource('about_title', 'App\Http\Controllers\API\AboutTitleController');
    Route::resource('about_content', 'App\Http\Controllers\API\AboutContentController');
    Route::resource('type', 'App\Http\Controllers\API\TypeController');
    Route::resource('album', 'App\Http\Controllers\API\AlbumController');
    Route::resource('file', 'App\Http\Controllers\API\FileController');
    Route::resource('icon', 'App\Http\Controllers\API\IconController');
    Route::resource('group', 'App\Http\Controllers\API\GroupController');
    Route::resource('status', 'App\Http\Controllers\API\StatusController');
    Route::resource('service', 'App\Http\Controllers\API\ServiceController');
    Route::resource('region', 'App\Http\Controllers\API\RegionController');
    Route::resource('country', 'App\Http\Controllers\API\CountryController');
    Route::resource('province', 'App\Http\Controllers\API\ProvinceController');
    Route::resource('city', 'App\Http\Controllers\API\CityController');
    Route::resource('area', 'App\Http\Controllers\API\AreaController');
    Route::resource('neighborhood', 'App\Http\Controllers\API\NeighborhoodController');
    Route::resource('address', 'App\Http\Controllers\API\AddressController');
    Route::resource('phone', 'App\Http\Controllers\API\PhoneController');
    Route::resource('email', 'App\Http\Controllers\API\EmailController');
    Route::resource('currency', 'App\Http\Controllers\API\CurrencyController');
    Route::resource('role', 'App\Http\Controllers\API\RoleController');
    Route::resource('user', 'App\Http\Controllers\API\UserController');
    Route::resource('company', 'App\Http\Controllers\API\CompanyController');
    Route::resource('cart', 'App\Http\Controllers\API\CartController');
    Route::resource('invoice', 'App\Http\Controllers\API\InvoiceController');
    Route::resource('prepaid_card', 'App\Http\Controllers\API\PrepaidCardController');
    Route::resource('message', 'App\Http\Controllers\API\MessageController');
    Route::resource('notification', 'App\Http\Controllers\API\NotificationController');
    Route::resource('history', 'App\Http\Controllers\API\HistoryController');
    Route::resource('email_notification', 'App\Http\Controllers\API\EmailNotificationController');
    Route::resource('sms_notification', 'App\Http\Controllers\API\SmsNotificationController');

    // AboutSubject
    Route::get('about_subject/search/{data}', 'App\Http\Controllers\API\AboutSubjectController@search')->name('about_subject.search');
    Route::put('about_subject/switch_status/{id}/{data}', 'App\Http\Controllers\API\AboutSubjectController@switchStatus')->name('about_subject.switch_status');
    // AboutTitle
    Route::get('about_title/search/{data}', 'App\Http\Controllers\API\AboutTitleController@search')->name('about_title.search');
    // AboutContent
    Route::get('about_content/search/{data}', 'App\Http\Controllers\API\AboutContentController@search')->name('about_content.search');
    Route::put('about_content/update_picture/{id}', 'App\Http\Controllers\API\AboutContentController@updatePicture')->name('about_content.update_picture');
    // Type
    Route::get('type/search/{data}', 'App\Http\Controllers\API\TypeController@search')->name('type.search');
    // Album
    Route::get('album/select_by_entity/{entity}/{id}', 'App\Http\Controllers\API\AlbumController@selectByEntity')->name('album.select_by_entity');
    // File
    Route::put('file/mark_as_main/{id}', 'App\Http\Controllers\API\FileController@markAsMain')->name('file.mark_as_main');
    Route::put('file/mark_as_secondary/{id}', 'App\Http\Controllers\API\FileController@markAsSecondary')->name('file.mark_as_secondary');
    // Icon
    Route::get('icon/select_by_entity/{entity}/{id}', 'App\Http\Controllers\API\IconController@selectByEntity')->name('icon.select_by_entity');
    // Group
    Route::get('group/search/{data}', 'App\Http\Controllers\API\GroupController@search')->name('group.search');
    // Status
    Route::get('status/search/{data}', 'App\Http\Controllers\API\StatusController@search')->name('status.search');
    Route::get('status/statuses_by_group/{group_id}', 'App\Http\Controllers\API\StatusController@showStatusesByGroup')->name('status.statuses_by_group');
    // Service
    Route::get('service/search/{data}', 'App\Http\Controllers\API\ServiceController@search')->name('service.search');
    Route::get('service/search_with_group/{group_name}/{data}', 'App\Http\Controllers\API\ServiceController@searchWithGroup')->name('service.search_with_group');
    Route::put('service/update_logo_picture/{id}', 'App\Http\Controllers\API\ServiceController@updateLogoPicture')->name('service.update_logo_picture');
    // Region
    Route::get('region/search/{data}', 'App\Http\Controllers\API\RegionController@search')->name('region.search');
    // Country
    Route::get('country/search/{data}', 'App\Http\Controllers\API\CountryController@search')->name('country.search');
    Route::put('country/associate_languages/{id}', 'App\Http\Controllers\API\CountryController@associateLanguages')->name('country.associate_languages');
    Route::put('country/withdraw_languages/{id}', 'App\Http\Controllers\API\CountryController@withdrawLanguages')->name('country.withdraw_languages');
    // Province
    Route::get('province/search/{data}', 'App\Http\Controllers\API\ProvinceController@search')->name('province.search');
    Route::get('province/search_with_country/{country_name}/{data}', 'App\Http\Controllers\API\ProvinceController@searchWithCountry')->name('province.search_with_country');
    // City
    Route::get('city/search/{data}', 'App\Http\Controllers\API\CityController@search')->name('city.search');
    Route::get('city/search_with_province/{province_name}/{data}', 'App\Http\Controllers\API\CityController@searchWithProvince')->name('city.search_with_province');
    // Area
    Route::get('area/search/{data}', 'App\Http\Controllers\API\AreaController@search')->name('area.search');
    Route::get('area/search_with_city/{city_name}/{data}', 'App\Http\Controllers\API\AreaController@searchWithCity')->name('area.search_with_city');
    // Neighborhood
    Route::get('neighborhood/search/{data}', 'App\Http\Controllers\API\NeighborhoodController@search')->name('neighborhood.search');
    Route::get('neighborhood/search_with_area_and_city/{area_name}/{city_name}/{data}', 'App\Http\Controllers\API\NeighborhoodController@searchWithAreaAndCity')->name('neighborhood.search_with_area_and_city');
    // Address
    Route::put('address/mark_as_main/{id}/{entity}/{entity_id}', 'App\Http\Controllers\API\AddressController@markAsMain')->name('address.mark_as_main');
    Route::put('address/mark_as_secondary/{id}', 'App\Http\Controllers\API\AddressController@markAsSecondary')->name('address.mark_as_secondary');
    // Phone
    Route::put('phone/mark_as_main/{id}/{entity}/{entity_id}', 'App\Http\Controllers\API\PhoneController@markAsMain')->name('phone.mark_as_main');
    Route::put('phone/mark_as_secondary/{id}', 'App\Http\Controllers\API\PhoneController@markAsSecondary')->name('phone.mark_as_secondary');
    // Email
    Route::put('email/mark_as_main/{id}/{entity}/{entity_id}', 'App\Http\Controllers\API\EmailController@markAsMain')->name('email.mark_as_main');
    Route::put('email/mark_as_secondary/{id}', 'App\Http\Controllers\API\EmailController@markAsSecondary')->name('email.mark_as_secondary');
    // Currency
    Route::get('currency/search/{data}', 'App\Http\Controllers\API\CurrencyController@search')->name('currency.search');
    // Role
    Route::get('role/search/{data}', 'App\Http\Controllers\API\RoleController@search')->name('role.search');
    // User
    Route::get('user/search/{visitor_user_id}/{data}', 'App\Http\Controllers\API\UserController@search')->name('user.search');
    Route::put('user/switch_status/{id}/{status_name}', 'App\Http\Controllers\API\UserController@switchStatus')->name('user.switch_status');
    Route::put('user/subscribe_to_comapny/{id}', 'App\Http\Controllers\API\UserController@subscribeToCompany')->name('user.subscribe_to_comapny');
    Route::put('user/associate_roles/{id}', 'App\Http\Controllers\API\UserController@associateRoles')->name('user.associate_roles');
    Route::put('user/withdraw_roles/{id}', 'App\Http\Controllers\API\UserController@withdrawRoles')->name('user.withdraw_roles');
    Route::put('user/update_prefered_role/{id}/{role_id}', 'App\Http\Controllers\API\UserController@updatePreferedRole')->name('user.update_prefered_role');
    Route::put('user/update_password/{id}', 'App\Http\Controllers\API\UserController@updatePassword')->name('user.update_password');
    Route::put('user/update_avatar_picture/{id}', 'App\Http\Controllers\API\UserController@updateAvatarPicture')->name('user.update_avatar_picture');
    // Company
    Route::get('company/search/{visitor_user_id}/{data}', 'App\Http\Controllers\API\CompanyController@search')->name('company.search');
    Route::get('company/search_admin/{visitor_user_id}/{id}/{data}', 'App\Http\Controllers\API\CompanyController@searchAdmin')->name('company.search_admin');
    Route::get('company/search_agent/{visitor_user_id}/{id}/{data}', 'App\Http\Controllers\API\CompanyController@searchAgent')->name('company.search_member');
    Route::get('company/search_customer/{visitor_user_id}/{id}/{data}', 'App\Http\Controllers\API\CompanyController@searchCustomer')->name('company.search_customer');
    Route::put('company/update_user_status/{id}/{user_id}', 'App\Http\Controllers\API\CompanyController@updateUserStatus')->name('company.update_user_status');
    Route::put('company/update_user_role/{id}/{user_id}', 'App\Http\Controllers\API\CompanyController@updateUserRole')->name('company.update_user_role');
    Route::put('company/update_logo_picture/{id}', 'App\Http\Controllers\API\CompanyController@updateLogoPicture')->name('company.update_logo_picture');
    // Cart
    Route::put('cart/update_payment_code/{id}', 'App\Http\Controllers\API\CartController@updatePaymentCode')->name('cart.update_payment_code');
    Route::put('cart/add_prepaid_cards/{id}', 'App\Http\Controllers\API\CartController@addPrepaidCards')->name('cart.add_prepaid_cards');
    Route::put('cart/withdraw_prepaid_cards/{id}', 'App\Http\Controllers\API\CartController@withdrawPrepaidCards')->name('cart.withdraw_prepaid_cards');
    Route::put('cart/upload_doc/{id}', 'App\Http\Controllers\API\CartController@uploadDoc')->name('cart.upload_doc');
    // Invoice
    Route::put('invoice/publish/{id}', 'App\Http\Controllers\API\InvoiceController@publish')->name('invoice.publish');
    Route::put('invoice/check_tolerated_delay/{id}', 'App\Http\Controllers\API\InvoiceController@checkToleratedDelay')->name('invoice.check_tolerated_delay');
    // PrepaidCard
    Route::put('prepaid_card/publish/{id}', 'App\Http\Controllers\API\PrepaidCardController@publish')->name('prepaid_card.publish');
    // Message
    Route::get('message/search/{data}', 'App\Http\Controllers\API\MessageController@search')->name('message.search');
    Route::get('message/inbox/{entity}', 'App\Http\Controllers\API\MessageController@inbox')->name('message.inbox');
    Route::get('message/unread_inbox/{entity}', 'App\Http\Controllers\API\MessageController@unreadInbox')->name('message.unread_inbox');
    Route::get('message/spams/{entity}', 'App\Http\Controllers\API\MessageController@spams')->name('message.spams');
    Route::get('message/outbox/{user_id}', 'App\Http\Controllers\API\MessageController@outbox')->name('message.outbox');
    Route::get('message/drafts/{user_id}', 'App\Http\Controllers\API\MessageController@drafts')->name('message.drafts');
    Route::get('message/answers/{message_id}', 'App\Http\Controllers\API\MessageController@answers')->name('message.answers');
    Route::put('message/switch_status/{id}/{user_id}/{status_name}', 'App\Http\Controllers\API\MessageController@switchStatus')->name('message.switch_status');
    Route::put('message/mark_all_read/{entity}', 'App\Http\Controllers\API\MessageController@markAllRead')->name('message.mark_all_read');
    Route::put('message/upload_doc/{id}', 'App\Http\Controllers\API\MessageController@uploadDoc')->name('message.upload_doc');
    Route::put('message/upload_audio/{id}', 'App\Http\Controllers\API\MessageController@uploadAudio')->name('message.upload_audio');
    Route::put('message/update_video/{id}', 'App\Http\Controllers\API\MessageController@updateVideo')->name('message.update_video');
    Route::put('message/update_picture/{id}', 'App\Http\Controllers\API\MessageController@updatePicture')->name('message.update_picture');
    // Notification
    Route::get('notification/select_by_user/{user_id}', 'App\Http\Controllers\API\NotificationController@selectByUser')->name('notification.select_by_user');
    Route::get('notification/select_unread_by_user/{user_id}', 'App\Http\Controllers\API\NotificationController@selectUnreadByUser')->name('notification.select_unread_by_user');
    Route::get('notification/switch_status/{id}', 'App\Http\Controllers\API\NotificationController@switchStatus')->name('notification.switch_status');
    // History
    Route::get('history/select_by_type/{user_id}/{type_id}', 'App\Http\Controllers\API\HistoryController@selectByType')->name('history.select_by_type');
    // EmailNotification
    Route::put('email_notification/switch_activation/{id}/{data}', 'App\Http\Controllers\API\EmailNotificationController@switchActivation')->name('email_notification.switch_activation');
    // SmsNotification
    Route::put('sms_notification/switch_activation/{id}/{data}', 'App\Http\Controllers\API\SmsNotificationController@switchActivation')->name('sms_notification.switch_activation');
});
