<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\AboutContent;
use App\Models\Group;
use App\Models\History;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Reaction;
use App\Models\Seller;
use App\Models\SellerTender;
use App\Models\SellerUser;
use App\Models\Status;
use App\Models\Tender;
use App\Models\TenderInvoice;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Invoice as ResourcesInvoice;

class InvoiceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices = Invoice::all();

        return $this->handleResponse(ResourcesInvoice::collection($invoices), __('notifications.find_all_invoices_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $member_group = Group::where('group_name', 'Membre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $member_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'invoice_number' => $request->invoice_number,
            'invoiced_period' => $request->invoiced_period,
            'tolerated_delay' => $request->tolerated_delay,
            'publishing_date' => $request->publishing_date,
            'seller_user_id' => $request->seller_user_id,
            'subscribed_seller_id' => $request->subscribed_seller_id,
            'seller_id' => $request->seller_id,
            'status_id' => $request->status_id
        ];
        // Validate required fields
        $validator = Validator::make($inputs, [
            'invoice_number' => ['required'],
            'seller_id' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $invoice = Invoice::create($inputs);

        // Associate tender to invoice
		if ($request->seller_tender_id != null) {
			TenderInvoice::create([
				'seller_tender_id' => $request->seller_tender_id,
				'invoice_id' => $invoice->id,
				'price_at_that_time' => $request->seller_tender_price,
				'used_quantity' => $request->used_quantity,
                'currency_id' => $request->currency_id
			]);
		}

        // Associate tenders to invoice
		if ($request->seller_tenders_ids != null) {
            foreach ($request->seller_tenders_ids as $seller_tender_id):
                TenderInvoice::create([
                    'seller_tender_id' => $seller_tender_id,
                    'invoice_id' => $invoice->id,
                    'price_at_that_time' => $request->seller_tender_price,
                    'used_quantity' => $request->used_quantity,
                    'currency_id' => $request->currency_id
                ]);
            endforeach;
		}

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $seller_user = SellerUser::find($inputs['seller_user_id']);
        $user = User::find($seller_user->user_id);
        $seller = Seller::find($inputs['seller_id']);

        // If seller publish the invoice
        if ($inputs['publishing_date'] != null) {
            History::create([
                'history_url' => 'invoices/' . $invoice->id,
                'history_content' => __('notifications.you_published_invoice'),
                'seller_id' => $seller->id,
                'type_id' => $activities_history_type->id
            ]);

            $user_stopped_seller = Reaction::where('user_id', $user->id)->where('reacted_by', 'user')->where('seller', $inputs['seller_id'])->where('status_id', $stopped_status->id)->first();

            // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
            if ($user_stopped_seller == null) {
                Notification::create([
                    'notification_url' => 'invoices/' . $invoice->id,
                    'notification_content' => $seller->seller_name . ' ' . __('notifications.sent_invoice'),
                    'user_id' => $user->id,
                    'status_id' => $unread_status->id
                ]);
            }

        } else {
            History::create([
                'history_url' => 'invoices/' . $invoice->id,
                'history_content' => __('notifications.you_added_invoice'),
                'seller_id' => $seller->id,
                'type_id' => $activities_history_type->id
            ]);
        }

        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.create_invoice_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::find($id);

        if (is_null($invoice)) {
            return $this->handleError(__('notifications.find_invoice_404'));
        }

        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.find_invoice_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'invoice_number' => $request->invoice_number,
            'invoiced_period' => $request->invoiced_period,
            'tolerated_delay' => $request->tolerated_delay,
            'publishing_date' => $request->publishing_date,
            'seller_user_id' => $request->seller_user_id,
            'subscribed_seller_id' => $request->subscribed_seller_id,
            'seller_id' => $request->seller_id,
            'status_id' => $request->status_id,
            'updated_at' => now()
        ];
        $validator = Validator::make($inputs, [
            'invoice_number' => ['required'],
            'seller_id' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }

        $invoice->update($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $seller = Seller::find($inputs['seller_id']);

        History::create([
            'history_url' => 'invoices/' . $invoice->id,
            'history_content' => __('notifications.you_updated_invoice'),
            'seller_id' => $seller->id,
            'type_id' => $activities_history_type->id
        ]);

        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.update_invoice_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        $you_deleted_about_content = AboutContent::where('subtitle', 'Vous avez supprimé l\'information')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $seller = Seller::find($invoice->seller_id);

        History::create([
            'history_url' => 'about_contents/' . $you_deleted_about_content->id,
            'history_content' => __('notifications.you_deleted_invoice'),
            'seller_id' => $seller->id,
            'type_id' => $activities_history_type->id
        ]);

        $invoice->delete();

        $invoices = Invoice::all();

        return $this->handleResponse(ResourcesInvoice::collection($invoices), __('notifications.delete_invoice_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Publish previously created invoice.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function publish($id)
    {
        $member_group = Group::where('group_name', 'Membre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $member_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // find invoice by given ID
        $invoice = Invoice::find($id);

        // update "tranfer_code" column
        $invoice->update([
            'publishing_date' => now(),
            'updated_at' => now()
        ]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $seller_user = SellerUser::find($invoice->seller_user_id);
        $user = User::find($seller_user->user_id);
        $seller = Seller::find($invoice->seller->id);
        $user_stopped_seller = Reaction::where('user_id', $user->id)->where('reacted_by', 'user')->where('seller', $invoice->seller_id)->where('status_id', $stopped_status->id)->first();

        History::create([
            'history_url' => 'invoices/' . $invoice->id,
            'history_content' => __('notifications.you_published_invoice'),
            'seller_id' => $seller->id,
            'type_id' => $activities_history_type->id
        ]);

        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
        if ($user_stopped_seller == null) {
            Notification::create([
                'notification_url' => 'invoices/' . $invoice->id,
                'notification_content' => $seller->seller_name . ' ' . __('notifications.sent_invoice'),
                'user_id' => $user->id,
                'status_id' => $unread_status->id
            ]);
        }

        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.update_invoice_success'));
    }

    /**
     * Check tolerated delay for the invoice.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function checkToleratedDelay($id)
    {
        $member_group = Group::where('group_name', 'Membre')->first();
        $notification_group = Group::where('group_name', 'Notification')->first();
        $stopped_status = Status::where([['status_name', 'Stop'], ['group_id', $member_group->id]])->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        // find invoice by given ID
        $invoice = Invoice::find($id);
        // get days, months and years to send good notifications
        $publishing_date_day = $invoice->publishing_date->format('d');
        $publishing_date_month = $invoice->publishing_date->format('m');
        $publishing_date_year = $invoice->publishing_date->format('Y');
        $now_day = now()->format('d');
        $now_month = now()->format('m');
        $now_year = now()->format('Y');

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        if ($invoice->subscribed_seller_id != null) {
            $addressee_seller = Seller::find($invoice->subscribed_seller_id);
            $other_seller_stopped_seller = Reaction::where('other_seller_id', $addressee_seller->id)->where('reacted_by', 'other_seller')->where('seller', $invoice->seller_id)->where('status_id', $stopped_status->id)->first();

            if ($now_year == $publishing_date_year) {
                if ($now_month == $publishing_date_month) {
                    $interval_between_days = $now_day - $publishing_date_day;

                    if (($invoice->tolerated_delay - $interval_between_days) == 7) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_in_one_week'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . $invoice->tolerated_delay - $interval_between_days . ' ' . __('miscellaneous.day_plural'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) == 2) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_tomorrow'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . $invoice->tolerated_delay - $interval_between_days . ' ' . __('miscellaneous.day_plural'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) == 1) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_today'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . $invoice->tolerated_delay - $interval_between_days . ' ' . __('miscellaneous.day_singular'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) == 0) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_now'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) < 0) {
                        if (($now_day - $invoice->tolerated_delay) == 1) {
                            // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                            if ($other_seller_stopped_seller == null) {
                                Notification::create([
                                    'notification_url' => 'invoices/' . $invoice->id,
                                    'notification_content' => __('notifications.expiration_yesterday'),
                                    'seller_id' => $addressee_seller->id,
                                    'status_id' => $unread_status->id
                                ]);
                            }

                            return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));

                        } else {
                            // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                            if ($other_seller_stopped_seller == null) {
                                Notification::create([
                                    'notification_url' => 'invoices/' . $invoice->id,
                                    'notification_content' => __('notifications.expiration_since') . ' ' . $now_day - $invoice->tolerated_delay . ' ' . __('miscellaneous.day_plural'),
                                    'seller_id' => $addressee_seller->id,
                                    'status_id' => $unread_status->id
                                ]);
                            }

                            return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                        }
                    }
                }

                if ($now_month > $publishing_date_month) {
                    if (($now_month - $publishing_date_month) == 1) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since') . ' ' . $now_month - $publishing_date_month . ' ' . __('miscellaneous.month_singular'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));

                    } else {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since') . ' ' . $now_month - $publishing_date_month . ' ' . __('miscellaneous.month_plural'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }
                }
            }

            if ($now_year > $publishing_date_year) {
                if (($now_year - $publishing_date_year) == 1) {
                    if (($now_month + $publishing_date_month) == 13) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since_a_month'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                    if (($now_month + $publishing_date_month) >= 23) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since') . ' ' . $now_month . ' ' . __('miscellaneous.month_plural'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                    if (($now_month + $publishing_date_month) == 24) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($other_seller_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since_a_year'),
                                'seller_id' => $addressee_seller->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                } else {
                    // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                    if ($other_seller_stopped_seller == null) {
                        Notification::create([
                            'notification_url' => 'invoices/' . $invoice->id,
                            'notification_content' => __('notifications.expiration_since') . ' ' . $now_year - $publishing_date_year . __('miscellaneous.year_plural'),
                            'seller_id' => $addressee_seller->id,
                            'status_id' => $unread_status->id
                        ]);
                    }

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                }
            }

        } else {
            $seller_user = SellerUser::find($invoice->seller_user_id);
            $addressee_user = User::find($seller_user->user_id);
            $user_stopped_seller = Reaction::where('user_id', $addressee_user->id)->where('reacted_by', 'user')->where('seller', $invoice->seller_id)->where('status_id', $stopped_status->id)->first();

            if ($now_year == $publishing_date_year) {
                if ($now_month == $publishing_date_month) {
                    $interval_between_days = $now_day - $publishing_date_day;

                    if (($invoice->tolerated_delay - $interval_between_days) == 7) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_in_one_week'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . $invoice->tolerated_delay - $interval_between_days . ' ' . __('miscellaneous.day_plural'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) == 2) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_tomorrow'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . $invoice->tolerated_delay - $interval_between_days . ' ' . __('miscellaneous.day_plural'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) == 1) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_today'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . $invoice->tolerated_delay - $interval_between_days . ' ' . __('miscellaneous.day_singular'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) == 0) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_now'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                    if (($invoice->tolerated_delay - $interval_between_days) < 0) {
                        if (($now_day - $invoice->tolerated_delay) == 1) {
                            // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                            if ($user_stopped_seller == null) {
                                Notification::create([
                                    'notification_url' => 'invoices/' . $invoice->id,
                                    'notification_content' => __('notifications.expiration_yesterday'),
                                    'user_id' => $addressee_user->id,
                                    'status_id' => $unread_status->id
                                ]);
                            }

                            return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));

                        } else {
                            // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                            if ($user_stopped_seller == null) {
                                Notification::create([
                                    'notification_url' => 'invoices/' . $invoice->id,
                                    'notification_content' => __('notifications.expiration_since') . ' ' . $now_day - $invoice->tolerated_delay . ' ' . __('miscellaneous.day_plural'),
                                    'user_id' => $addressee_user->id,
                                    'status_id' => $unread_status->id
                                ]);
                            }

                            return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                        }
                    }
                }

                if ($now_month > $publishing_date_month) {
                    if (($now_month - $publishing_date_month) == 1) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since') . ' ' . $now_month - $publishing_date_month . ' ' . __('miscellaneous.month_singular'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));

                    } else {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since') . ' ' . $now_month - $publishing_date_month . ' ' . __('miscellaneous.month_plural'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }
                }
            }

            if ($now_year > $publishing_date_year) {
                if (($now_year - $publishing_date_year) == 1) {
                    if (($now_month + $publishing_date_month) == 13) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since_a_month'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                    if (($now_month + $publishing_date_month) >= 23) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since') . ' ' . $now_month . ' ' . __('miscellaneous.month_plural'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                    if (($now_month + $publishing_date_month) == 24) {
                        // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                        if ($user_stopped_seller == null) {
                            Notification::create([
                                'notification_url' => 'invoices/' . $invoice->id,
                                'notification_content' => __('notifications.expiration_since_a_year'),
                                'user_id' => $addressee_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }

                } else {
                    // Send a notification to the addressee, ensuring that addressee didn't stop seller notifications
                    if ($user_stopped_seller == null) {
                        Notification::create([
                            'notification_url' => 'invoices/' . $invoice->id,
                            'notification_content' => __('notifications.expiration_since') . ' ' . $now_year - $publishing_date_year . __('miscellaneous.year_plural'),
                            'user_id' => $addressee_user->id,
                            'status_id' => $unread_status->id
                        ]);
                    }

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                }
            }
        }
    }

    /**
     * Associate tenders to seller in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function associateTenders(Request $request, $id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $invoice = Invoice::find($id);

		if ($request->tender_name != null) {
			$tender = Tender::create([
				'tender_name' => $request->tender_name,
				'category_id' => $request->category_id,
				'subcategory_id' => $request->subcategory_id,
				'type_id' => $request->type_id
			]);
			$seller_tender = SellerTender::create([
				'seller_id' => $invoice->seller_id,
				'tender_id' => $tender->id,
				'tender_price' => $request->tender_price,
				'tender_description' => $request->tender_description,
				'stored_quantity' => $request->stored_quantity,
				'tender_url' => $request->tender_url,
				'currency_id' => $request->currency_id,
				'visibility_id' => $request->visibility_id
			]);
            TenderInvoice::create([
				'seller_tender_id' => $seller_tender->_id,
				'invoice_id' => $invoice->id,
				'price_at_that_time' => $request->seller_tender_price,
				'used_quantity' => $request->used_quantity,
                'currency_id' => $request->currency_id
			]);
		}

		if ($request->tender_id != null) {
			$seller_tender = SellerTender::create([
				'seller_id' => $invoice->seller_id,
				'tender_id' => $request->tender_id,
				'tender_price' => $request->tender_price,
				'tender_description' => $request->tender_description,
				'stored_quantity' => $request->stored_quantity,
				'tender_url' => $request->tender_url,
				'currency_id' => $request->currency_id,
				'visibility_id' => $request->visibility_id
			]);
            TenderInvoice::create([
				'seller_tender_id' => $seller_tender->_id,
				'invoice_id' => $invoice->id,
				'price_at_that_time' => $request->seller_tender_price,
				'used_quantity' => $request->used_quantity,
                'currency_id' => $request->currency_id
			]);
		}

		if ($request->seller_tender_id != null) {
            TenderInvoice::create([
				'seller_tender_id' => $request->seller_tender_id,
				'invoice_id' => $invoice->id,
				'price_at_that_time' => $request->seller_tender_price,
				'used_quantity' => $request->used_quantity,
                'currency_id' => $request->currency_id
			]);
		}

		/*
			HISTORY AND/OR NOTIFICATION MANAGEMENT
		*/
		History::create([
			'history_url' => 'invoices/' . $invoice->id,
			'history_content' => __('notifications.you_added_invoice_tenders'),
			'seller_id' => $invoice->seller_id,
			'type_id' => $activities_history_type->id
		]);

        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.update_invoice_success'));
    }

    /**
     * Withdraw tenders from seller in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function withdrawTenders(Request $request, $id)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $invoice = Invoice::find($id);

		if ($request->seller_tenders_ids != null) {
			foreach ($request->seller_tenders_ids as $seller_tender_id):
				$tender_invoice = TenderInvoice::where([['invoice_id', $invoice->id], ['seller_tender_id', $seller_tender_id]])->first();

				$tender_invoice->delete();
			endforeach;
		}

		if ($request->seller_tender_id != null) {
			$tender_invoice = TenderInvoice::where([['invoice_id', $invoice->id], ['seller_tender_id', $request->seller_tender_id]])->first();

			$tender_invoice->delete();
		}

		/*
			HISTORY AND/OR NOTIFICATION MANAGEMENT
		*/
		History::create([
			'history_url' => 'invoices/' . $invoice->id,
			'history_content' => __('notifications.you_deleted_invoice_tenders'),
			'seller_id' => $invoice->seller_id,
			'type_id' => $activities_history_type->id
		]);

        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.update_invoice_success'));
    }
}
