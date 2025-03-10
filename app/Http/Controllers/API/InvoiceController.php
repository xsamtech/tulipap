<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\Group;
use App\Models\History;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Status;
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
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'invoice_number' => $request->invoice_number,
            'invoiced_period' => $request->invoiced_period,
            'tolerated_delay' => $request->tolerated_delay,
            'publishing_date' => $request->publishing_date,
            'used_quantity' => $request->used_quantity,
            'company_id' => $request->company_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id
        ];
        // Select all invoices to check unique constraint
        $invoice_for_numbers = Invoice::where([['company_id', $inputs['company_id']], ['user_id', $inputs['user_id']]])->get();
        $invoice_for_periods = Invoice::where([['invoiced_period', $inputs['invoiced_period']], ['company_id', $inputs['company_id']], ['user_id', $inputs['user_id']]])->get();
        // Validate required fields
        $validator = Validator::make($inputs, [
            'invoice_number' => ['required'],
            'company_id' => ['required'],
            'status_id' => ['required'],
            'user_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        // Check if invoice number or period already exists
        foreach ($invoice_for_numbers as $another_invoice):
            if ($another_invoice->invoice_number == $inputs['invoice_number']) {
                return $this->handleError($inputs['invoice_number'], __('validation.custom.invoice_number.exists'), 400);
            }
        endforeach;
        foreach ($invoice_for_periods as $another_invoice):
            if ($another_invoice->invoiced_period == $inputs['invoiced_period'] AND $another_invoice->created_at->format('Y-m-d') == date('Y-m-d')) {
                return $this->handleError($inputs['invoiced_period'], __('validation.custom.invoiced_period.exists'), 400);
            }
        endforeach;

        $invoice = Invoice::create($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $admin_role = Role::where('role_name', 'Administrateur')->first();
        // Current customer
        $user = User::find($inputs['user_id']);
        // The invoice creator
        $creator = User::find($request->creator_id);
        $company = Company::find($inputs['company_id']);
        // All company users to get admins
        $company_users = User::where('company_id', $company->id)->get();

        // If company publish the invoice
        if ($inputs['publishing_date'] != null) {
            History::create([
                'history_url' => 'company/invoice/' . $invoice->id,
                'history_content' => __('notifications.you_published_invoice'),
                'user_id' => $creator->id,
                'type_id' => $activities_history_type->id
            ]);

            foreach ($company_users as $company_user):
                $other_admins = RoleUser::where('role_id', $admin_role->id)->get();

                foreach ($other_admins as $other_admin):
                    if ($company_user->id == $other_admin->id AND $company_user->id != $creator->id) {
                        Notification::create([
                            'notification_url' => 'company/invoice/' . $invoice->id,
                            'notification_content' => $creator->firstname . ' ' . $creator->lastname . ' ' . __('notifications.published_invoice'),
                            'user_id' => $company_user->id,
                            'status_id' => $unread_status->id
                        ]);
                    }
                endforeach;
            endforeach;

            // Send a notification to the customer
            Notification::create([
                'notification_url' => 'invoice/' . $invoice->id,
                'notification_content' => $company->company_name . ' ' . __('notifications.sent_invoice'),
                'user_id' => $user->id,
                'status_id' => $unread_status->id
            ]);

        } else {
            History::create([
                'history_url' => 'company/invoice/' . $invoice->id,
                'history_content' => __('notifications.you_added_invoice'),
                'user_id' => $creator->id,
                'type_id' => $activities_history_type->id
            ]);

            foreach ($company_users as $company_user):
                $other_admins = RoleUser::where('role_id', $admin_role->id)->get();

                foreach ($other_admins as $other_admin):
                    if ($company_user->id == $other_admin->id AND $company_user->id != $creator->id) {
                        Notification::create([
                            'notification_url' => 'company/invoice/' . $invoice->id,
                            'notification_content' => $creator->firstname . ' ' . $creator->lastname . ' ' . __('notifications.added_invoice'),
                            'user_id' => $company_user->id,
                            'status_id' => $unread_status->id
                        ]);
                    }
                endforeach;
            endforeach;
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
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $inputs = [
            'id' => $request->id,
            'invoice_number' => $request->invoice_number,
            'invoiced_period' => $request->invoiced_period,
            'tolerated_delay' => $request->tolerated_delay,
            'publishing_date' => $request->publishing_date,
            'used_quantity' => $request->used_quantity,
            'company_id' => $request->company_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'updated_at' => now()
        ];
        $invoice_for_numbers = Invoice::where([['company_id', $inputs['company_id']], ['user_id', $inputs['user_id']]])->get();
        $invoice_for_periods = Invoice::where([['invoiced_period', $inputs['invoiced_period']], ['company_id', $inputs['company_id']], ['user_id', $inputs['user_id']]])->get();
        $current_invoice = Invoice::find($inputs['id']);
        $validator = Validator::make($inputs, [
            'invoice_number' => ['required'],
            'company_id' => ['required'],
            'status_id' => ['required'],
            'user_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }

        // Check if invoice number or period already exists
        foreach ($invoice_for_numbers as $another_invoice):
            if ($current_invoice->invoice_number != $inputs['invoice_number']) {
                if ($another_invoice->invoice_number == $inputs['invoice_number']) {
                    return $this->handleError($inputs['invoice_number'], __('validation.custom.invoice_number.exists'), 400);
                }
            }
        endforeach;
        foreach ($invoice_for_periods as $another_invoice):
            if ($current_invoice->invoiced_period != $inputs['invoiced_period']) {
                if ($another_invoice->invoiced_period == $inputs['invoiced_period'] AND $another_invoice->created_at->format('Y-m-d') == date('Y-m-d')) {
                    return $this->handleError($inputs['invoiced_period'], __('validation.custom.invoiced_period.exists'), 400);
                }
            }
        endforeach;

        $invoice->update($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $admin_role = Role::where('role_name', 'Administrateur')->first();
        // The invoice updater
        $updater = User::find($request->updater_id);
        $company = Company::find($inputs['company_id']);
        // All company users to get admins
        $company_users = User::where('company_id', $company->id)->get();

        History::create([
            'history_url' => 'company/invoice/' . $invoice->id,
            'history_content' => __('notifications.you_updated_invoice'),
            'user_id' => $updater->id,
            'type_id' => $activities_history_type->id
        ]);

        foreach ($company_users as $company_user):
            $users_admin = RoleUser::where('role_id', $admin_role->id)->get();

            foreach ($users_admin as $user_admin):
                if ($company_user->id == $user_admin->id AND $company_user->id != $updater->id) {
                    Notification::create([
                        'notification_url' => 'company/invoice/' . $invoice->id,
                        'notification_content' => $updater->firstname . ' ' . $updater->lastname . ' ' . __('notifications.updated_invoice'),
                        'user_id' => $company_user->id,
                        'status_id' => $unread_status->id
                    ]);
                }
            endforeach;
        endforeach;

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
        $invoice->delete();

        $invoices = Invoice::all();

        return $this->handleResponse(ResourcesInvoice::collection($invoices), __('notifications.delete_invoice_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Publish previously created invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $publisher_id
     * @return \Illuminate\Http\Response
     */
    public function publish(Request $request, $publisher_id)
    {
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $admin_role = Role::where('role_name', 'Administrateur')->first();
        $publisher = User::find($publisher_id);

        if ($request->invoices_ids != null) {
            /*
                HISTORY MANAGEMENT
            */
            History::create([
                'history_url' => 'company/invoice',
                'history_content' => __('notifications.you_published_invoices1') . ' ' . count($request->invoices_ids) . ' ' . __('notifications.you_published_invoices2'),
                'user_id' => $publisher->id,
                'type_id' => $activities_history_type->id
            ]);

            foreach ($request->invoices_ids as $invoice_id):
                // find invoice by given ID
                $invoice = Invoice::find($invoice_id);

                // update "publishing_date" column
                $invoice->update([
                    'publishing_date' => now(),
                    'updated_at' => now()
                ]);

                /*
                    NOTIFICATION MANAGEMENT
                */
                $user = User::find($invoice->user_id);
                $company = Company::find($invoice->company->id);
                $company_users = User::where('company_id', $company->id)->get();

                // Send a notification to each company admin
                foreach ($company_users as $company_user):
                    $users_admin = RoleUser::where('role_id', $admin_role->id)->get();

                    foreach ($users_admin as $user_admin):
                        if ($company_user->id == $user_admin->id AND $company_user->id != $publisher->id) {
                            Notification::create([
                                'notification_url' => 'company/invoice/' . $invoice->id,
                                'notification_content' => $publisher->firstname . ' ' . $publisher->lastname . ' ' . __('notifications.published_invoices1') . ' ' . count($request->invoices_ids) . ' ' . __('notifications.published_invoices2'),
                                'user_id' => $company_user->id,
                                'status_id' => $unread_status->id
                            ]);
                        }
                    endforeach;
                endforeach;

                // Send a notification to the customer
                Notification::create([
                    'notification_url' => 'invoice/' . $invoice->id,
                    'notification_content' => $company->company_name . ' ' . __('notifications.sent_invoice'),
                    'user_id' => $user->id,
                    'status_id' => $unread_status->id
                ]);

                return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.update_invoice_success'));
            endforeach;
        }

        if ($request->invoice_id != null) {
            // find invoice by given ID
            $invoice = Invoice::find($request->invoice_id);

            // update "publishing_date" column
            $invoice->update([
                'publishing_date' => now(),
                'updated_at' => now()
            ]);

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            $user = User::find($invoice->user_id);
            $company = Company::find($invoice->company->id);
            $company_users = User::where('company_id', $company->id)->get();

            History::create([
                'history_url' => 'company/invoice/' . $invoice->id,
                'history_content' => __('notifications.you_published_invoice'),
                'user_id' => $publisher->id,
                'type_id' => $activities_history_type->id
            ]);

            // Send a notification to each company admin
            foreach ($company_users as $company_user):
                $users_admin = RoleUser::where('role_id', $admin_role->id)->get();

                foreach ($users_admin as $user_admin):
                    if ($company_user->id == $user_admin->id AND $company_user->id != $publisher->id) {
                        Notification::create([
                            'notification_url' => 'company/invoice/' . $invoice->id,
                            'notification_content' => $publisher->firstname . ' ' . $publisher->lastname . ' ' . __('notifications.published_invoice'),
                            'user_id' => $company_user->id,
                            'status_id' => $unread_status->id
                        ]);
                    }
                endforeach;
            endforeach;

            // Send a notification to the customer
            Notification::create([
                'notification_url' => 'invoice/' . $invoice->id,
                'notification_content' => $company->company_name . ' ' . __('notifications.sent_invoice'),
                'user_id' => $user->id,
                'status_id' => $unread_status->id
            ]);

            return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.update_invoice_success'));
        }
    }

    /**
     * Check tolerated delay for the invoice.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function checkToleratedDelay($id)
    {
        $notification_group = Group::where('group_name', 'Notification')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        // find invoice by given ID
        $invoice = Invoice::find($id);
        // get days, months and years to send good notifications
        $publishing_date_day = $invoice->publishing_date->format('d');
        $publishing_date_month = $invoice->publishing_date->format('m');
        $publishing_date_year = $invoice->publishing_date->format('Y');
        $now_day = date('d');
        $now_month = date('m');
        $now_year = date('Y');

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $customer = User::find($invoice->user_id);

        if ($now_year == $publishing_date_year) {
            if ($now_month == $publishing_date_month) {
                if (date_diff($invoice->publishing_date, $invoice->tolerated_delay) == 7) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_in_one_week'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . date_diff($invoice->publishing_date, $invoice->tolerated_delay) . ' ' . __('miscellaneous.day_plural'));
                }

                if (date_diff($invoice->publishing_date, $invoice->tolerated_delay) == 2) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_tomorrow'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . date_diff($invoice->publishing_date, $invoice->tolerated_delay) . ' ' . __('miscellaneous.day_plural'));
                }

                if (date_diff($invoice->publishing_date, $invoice->tolerated_delay) == 1) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_today'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' .date_diff($invoice->publishing_date, $invoice->tolerated_delay) . ' ' . __('miscellaneous.day_singular'));
                }

                if (date_diff($invoice->publishing_date, $invoice->tolerated_delay) == 0) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_now'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                }

                if (date_diff($invoice->publishing_date, $invoice->tolerated_delay) < 0) {
                    if (date_diff($invoice->tolerated_delay, now()) == 1) {
                        Notification::create([
                            'notification_url' => 'invoice/' . $invoice->id,
                            'notification_content' => __('notifications.expiration_yesterday'),
                            'user_id' => $customer->id,
                            'status_id' => $unread_status->id
                        ]);

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));

                    } else {
                        Notification::create([
                            'notification_url' => 'invoice/' . $invoice->id,
                            'notification_content' => __('notifications.expiration_since') . ' ' . date_diff($invoice->tolerated_delay, now()) . ' ' . __('miscellaneous.day_plural'),
                            'user_id' => $customer->id,
                            'status_id' => $unread_status->id
                        ]);

                        return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                    }
                }
            }

            if ($now_month > $publishing_date_month) {
                if (($now_month - $publishing_date_month) == 1) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_since') . ' ' . $now_month - $publishing_date_month . ' ' . __('miscellaneous.month_singular'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));

                } else {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_since') . ' ' . $now_month - $publishing_date_month . ' ' . __('miscellaneous.month_plural'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                }
            }
        }

        if ($now_year > $publishing_date_year) {
            if (($now_year - $publishing_date_year) == 1) {
                if (($now_month + $publishing_date_month) == 13) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_since_a_month'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                }

                if (($now_month + $publishing_date_month) >= 23) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_since') . ' ' . $now_month . ' ' . __('miscellaneous.month_plural'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                }

                if (($now_month + $publishing_date_month) == 24) {
                    Notification::create([
                        'notification_url' => 'invoice/' . $invoice->id,
                        'notification_content' => __('notifications.expiration_since_a_year'),
                        'user_id' => $customer->id,
                        'status_id' => $unread_status->id
                    ]);

                    return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
                }

            } else {
                Notification::create([
                    'notification_url' => 'invoice/' . $invoice->id,
                    'notification_content' => __('notifications.expiration_since') . ' ' . $now_year - $publishing_date_year . __('miscellaneous.year_plural'),
                    'user_id' => $customer->id,
                    'status_id' => $unread_status->id
                ]);

                return $this->handleResponse(new ResourcesInvoice($invoice), __('notifications.deadline_count') . ' ' . __('notifications.deadline_count_elapsed'));
            }
        }
    }
}
