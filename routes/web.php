<?php

/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\ContinentController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MessageController;
use App\Http\Controllers\Web\MiscellaneousController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTES FOR EVERY ROLES
|--------------------------------------------------------------------------
*/
// Home
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/language/{locale}', [HomeController::class, 'changeLanguage'])->name('change_language');
// Account
Route::get('/account', [AccountController::class, 'account'])->name('account');
Route::get('/account/update_password', [AccountController::class, 'editPassword'])->name('account.update.password');
Route::get('/account/albums', [AccountController::class, 'account'])->name('account.albums');
Route::get('/account/albums/new', [AccountController::class, 'newAlbum'])->name('account.album.new');
Route::get('/account/albums/{id}', [AccountController::class, 'albumDatas'])->whereNumber('id')->name('account.album.datas');
Route::get('/account/albums/images/{id}', [AccountController::class, 'imageDatas'])->whereNumber('id')->name('account.album.image.datas');
Route::post('/account', [AccountController::class, 'updateAccount'])->name('account');
Route::post('/account/update_password', [AccountController::class, 'updatePassword'])->name('account.update.password');
// Message
Route::get('/message', [MessageController::class, 'receivedMessages'])->name('message.inbox');
Route::get('/message/sent', [MessageController::class, 'sentMessages'])->name('message.outbox');
Route::get('/message/drafts', [MessageController::class, 'draftsMessages'])->name('message.draft');
Route::get('/message/spams', [MessageController::class, 'spamsMessages'])->name('message.spams');
Route::get('/message/{id}', [MessageController::class, 'showMessage'])->whereNumber('id')->name('message.datas');
Route::get('/message/new', [MessageController::class, 'newMessage'])->name('message.new');
Route::get('/message/search/{data}', [MessageController::class, 'search'])->name('message.search');
Route::get('/message/delete/{id}', [MessageController::class, 'deleteMessage'])->name('message.delete');
Route::post('/message/create', [MessageController::class, 'storeMessage'])->name('message.create');
Route::post('/message/{id}', [MessageController::class, 'updateMessage'])->whereNumber('id')->name('message.datas');

/*
|--------------------------------------------------------------------------
| ROUTES FOR EVERY ROLES EXCEPT "Super administrateur" AND "Développeur"
|--------------------------------------------------------------------------
*/
// Notification
Route::get('/notification', [HomeController::class, 'notification'])->name('notification.home');
// About us
Route::get('/about_us', [HomeController::class, 'aboutUs'])->name('about_us.home');
Route::get('/about_us/company', [HomeController::class, 'aboutCompany'])->name('about_us.company');
Route::get('/about_us/app', [HomeController::class, 'aboutApplication'])->name('about_us.app');
Route::get('/about_us/terms_of_use', [HomeController::class, 'termsOfUse'])->name('about_us.terms_of_use');
Route::get('/about_us/privacy_policy', [HomeController::class, 'privacyPolicy'])->name('about_us.privacy_policy');
Route::get('/about_us/help', [HomeController::class, 'help'])->name('about_us.help');
Route::get('/about_us/faq', [HomeController::class, 'faq'])->name('about_us.faq');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Administrateur" AND "Agent"
|--------------------------------------------------------------------------
*/
// Home
Route::get('/search/customers/{data}', [HomeController::class, 'searchCustomer'])->name('search.customer');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Agent" AND "Client"
|--------------------------------------------------------------------------
*/
// Home
Route::get('/invoice', [HomeController::class, 'invoice'])->name('invoice.home');
Route::get('/invoice/{id}', [HomeController::class, 'invoiceDatas'])->whereNumber('id')->name('invoice.datas');
Route::get('/provider/{id}', [HomeController::class, 'providerDatas'])->whereNumber('id')->name('provider.datas');
Route::get('/provider/{provider_id}/prepaid_card', [HomeController::class, 'prepaidCard'])->whereNumber('provider_id')->name('provider.prepaid_card.home');
Route::get('/provider/{provider_id}/prepaid_card/{id}', [HomeController::class, 'prepaidCardDatas'])->whereNumber(['provider_id', 'id'])->name('provider.prepaid_card.datas');
Route::get('/post', [HomeController::class, 'post'])->name('post.home');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Super administrateur"
|--------------------------------------------------------------------------
*/
// About// About
Route::get('/about', [AboutController::class, 'index'])->name('about.home');
Route::get('/about/{id}', [AboutController::class, 'show'])->whereNumber('id')->name('about.datas');
Route::get('/about/{entity}', [AboutController::class, 'indexEntity'])->name('about.entity.home');
Route::get('/about/{entity}/{id}', [AboutController::class, 'showEntity'])->whereNumber('id')->name('about.entity.datas');
Route::get('/about/search/{data}', [AboutController::class, 'search'])->name('about.search');
Route::get('/about/{entity}/search/{data}', [AboutController::class, 'searchEntity'])->name('about.entity.search');
Route::get('/about/delete/{id}', [AboutController::class, 'delete'])->name('about.delete');
Route::get('/about/{entity}/delete/{id}', [AboutController::class, 'deleteEntity'])->name('about.entity.delete');
Route::post('/about', [AboutController::class, 'store'])->name('about.home');
Route::post('/about/{id}', [AboutController::class, 'update'])->whereNumber('id')->name('about.datas');
Route::post('/about/{entity}', [AboutController::class, 'storeEntity'])->name('about.entity.home');
Route::post('/about/{entity}/{id}', [AboutController::class, 'updateEntity'])->whereNumber('id')->name('about.entity.datas');
// Continent
Route::get('/continent', [ContinentController::class, 'index'])->name('continent.home');
Route::get('/continent/{id}', [ContinentController::class, 'show'])->whereNumber('id')->name('continent.datas');
Route::get('/continent/{entity}', [ContinentController::class, 'indexEntity'])->name('continent.entity.home');
Route::get('/continent/{entity}/{id}', [ContinentController::class, 'showEntity'])->whereNumber('id')->name('continent.entity.datas');
Route::get('/continent/search/{data}', [ContinentController::class, 'search'])->name('continent.search');
Route::get('/continent/{entity}/search/{data}', [ContinentController::class, 'searchEntity'])->name('continent.entity.search');
Route::get('/continent/delete/{id}', [ContinentController::class, 'delete'])->name('continent.delete');
Route::get('/continent/{entity}/delete/{id}', [ContinentController::class, 'deleteEntity'])->name('continent.entity.delete');
Route::post('/continent', [ContinentController::class, 'store'])->name('continent.home');
Route::post('/continent/{id}', [ContinentController::class, 'update'])->whereNumber('id')->name('continent.datas');
Route::post('/continent/{entity}', [ContinentController::class, 'storeEntity'])->name('continent.entity.home');
Route::post('/continent/{entity}/{id}', [ContinentController::class, 'updateEntity'])->whereNumber('id')->name('continent.entity.datas');
// Miscellaneous
Route::get('/miscellaneous', [MiscellaneousController::class, 'index'])->name('miscellaneous.home');
Route::get('/miscellaneous/{id}', [MiscellaneousController::class, 'show'])->whereNumber('id')->name('miscellaneous.datas');
Route::get('/miscellaneous/{entity}', [MiscellaneousController::class, 'indexEntity'])->name('miscellaneous.entity.home');
Route::get('/miscellaneous/{entity}/{id}', [MiscellaneousController::class, 'showEntity'])->whereNumber('id')->name('miscellaneous.entity.datas');
Route::get('/miscellaneous/search/{data}', [MiscellaneousController::class, 'search'])->name('miscellaneous.search');
Route::get('/miscellaneous/{entity}/search/{data}', [MiscellaneousController::class, 'searchEntity'])->name('miscellaneous.entity.search');
Route::get('/miscellaneous/delete/{id}', [MiscellaneousController::class, 'delete'])->name('miscellaneous.delete');
Route::get('/miscellaneous/{entity}/delete/{id}', [MiscellaneousController::class, 'deleteEntity'])->name('miscellaneous.entity.delete');
Route::post('/miscellaneous', [MiscellaneousController::class, 'store'])->name('miscellaneous.home');
Route::post('/miscellaneous/{id}', [MiscellaneousController::class, 'update'])->whereNumber('id')->name('miscellaneous.datas');
Route::post('/miscellaneous/{entity}', [MiscellaneousController::class, 'storeEntity'])->name('miscellaneous.entity.home');
Route::post('/miscellaneous/{entity}/{id}', [MiscellaneousController::class, 'updateEntity'])->whereNumber('id')->name('miscellaneous.entity.datas');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Développeur"
|--------------------------------------------------------------------------
*/
// API
Route::get('/apis', [APIController::class, 'index'])->name('apis.home');
Route::get('/apis/{entity}', [APIController::class, 'apisEntity'])->name('apis.entity');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Administrateur"
|--------------------------------------------------------------------------
*/
// Company
Route::get('/company', [CompanyController::class, 'index'])->name('company.home');
Route::get('/company/pricing', [CompanyController::class, 'pricing'])->name('company.pricing');
Route::get('/company/office', [CompanyController::class, 'office'])->name('company.office');
Route::get('/company/agent', [CompanyController::class, 'agent'])->name('company.agent');
Route::get('/company/search/administrators/{data}', [CompanyController::class, 'searchAdmin'])->name('search.admin');
Route::get('/company/search/agents/{data}', [CompanyController::class, 'searchAgent'])->name('search.agent');
Route::get('/company/agent/{id}', [CompanyController::class, 'agentDatas'])->whereNumber('id')->name('company.agent.datas');
Route::get('/company/invoice', [CompanyController::class, 'invoice'])->name('company.invoice');
Route::get('/company/invoice/{id}', [CompanyController::class, 'invoiceDatas'])->whereNumber('id')->name('company.invoice.datas');
Route::get('/company/customer', [CompanyController::class, 'customer'])->name('company.customer');
Route::get('/company/customer/new', [CompanyController::class, 'newCustomer'])->name('company.customer.new');
Route::get('/company/customer/{id}', [CompanyController::class, 'customerDatas'])->whereNumber('id')->name('company.customer.datas');
Route::get('/company/prepaid_card', [CompanyController::class, 'prepaidCard'])->name('company.prepaid_card');
Route::get('/company/prepaid_card/{id}', [CompanyController::class, 'prepaidCardDatas'])->whereNumber('id')->name('company.prepaid_card.datas');
Route::get('/company/communique', [CompanyController::class, 'communique'])->name('company.communique');
Route::get('/company/communique/{id}', [CompanyController::class, 'communiqueDatas'])->name('company.communique.datas');
Route::get('/company/other_admin', [CompanyController::class, 'communique'])->name('company.other_admin');
Route::get('/company/other_admin/{id}', [CompanyController::class, 'otherAdminDatas'])->name('company.other_admin.datas');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Agent"
|--------------------------------------------------------------------------
*/
// Office
Route::get('/customer', [OfficeController::class, 'customer'])->name('customer.home');
Route::get('/customer/{id}', [OfficeController::class, 'customerDatas'])->whereNumber('id')->name('customer.home');
Route::get('/communique', [OfficeController::class, 'communique'])->name('communique.home');
Route::get('/communique/{id}', [OfficeController::class, 'communiqueDatas'])->whereNumber('id')->name('communique.home');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Client"
|--------------------------------------------------------------------------
*/
// Home
Route::get('/cart', [HomeController::class, 'cart'])->name('cart.home');
Route::get('/cart/receipt', [HomeController::class, 'receipt'])->name('cart.receipt.home');
Route::get('/cart/receipt/{cart_id}', [HomeController::class, 'receiptDatas'])->whereNumber('cart_id')->name('cart.receipt.datas');
Route::get('/provider', [HomeController::class, 'provider'])->name('provider.home');
Route::get('/search/providers/{data}', [HomeController::class, 'searchProvider'])->name('search.provider');

require __DIR__ . '/auth.php';
