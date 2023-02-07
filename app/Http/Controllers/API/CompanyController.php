<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

namespace App\Http\Controllers\API;

use App\Models\AboutContent;
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
use App\Models\Phone;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\SocialNetwork;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Company as ResourcesCompany;
use App\Http\Resources\User as ResourcesUser;

class CompanyController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::orderByDesc('company_name')->get();

        return $this->handleResponse(ResourcesCompany::collection($companies), __('notifications.find_all_companies_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
		$main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'company_name' => $request->company_name,
            'company_acronym' => $request->company_acronym,
            'website_url' => $request->website_url,
            'status_id' => $request->status_id
        ];
        // Select all companies to check unique constraint
        $companies = Company::all();

        // Validate required fields
        if ($inputs['company_name'] == null OR $inputs['company_name'] == ' ') {
            return $this->handleError($inputs['company_name'], __('validation.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

		if ($request->user_id == null OR $request->user_id == ' ') {
            return $this->handleError($request->user_id, __('validation.required'), 400);
		}

		// Check if company name already exists
        foreach ($companies as $another_company):
            if ($another_company->company_name == $inputs['company_name']) {
                return $this->handleError($inputs['company_name'], __('validation.custom.company_name.exists'), 400);
            }
        endforeach;

		$company = Company::create($inputs);

		// Link the company to the user who created it
		$current_user = User::find($request->user_id);
		$current_user_roles = RoleUser::where(['user_id', $request->user_id])->get();
		$role_admin = Role::where(['role_name', 'Administrateur'])->first();

		// Add a new role to user, set its "selected" column to "1" and set value for other roles to "0"
		if ($current_user_roles != null) {
			foreach ($current_user_roles as $user_role):
				$user_role->update([
					'selected' => 0,
					'updated_at' => now()
				]);
			endforeach;
		}

		$current_user->update([
			'company_id' => $company->id,
			'updated_at' => now()
		]);

		RoleUser::create([
			'user_id' => $current_user->id,
			'role_id' => $role_admin->id,
			'selected' => 1
		]);

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
				'company_id' => $company->id
			]);
		}

		// If user want to add an e-mail for the company
		if ($request->email_content != null AND $request->email_content == ' ') {
			// Select all company e-mails to check unique constraint
			$emails = Email::where('company_id', $company->id)->get();

			if ($request->email_status_id == null OR $request->email_status_id == ' ') {
				return $this->handleError($request->email_status_id, __('validation.required'), 400);
			}

			// Check if e-mail already exists
			foreach ($emails as $another_email):
				if ($another_email->email_content == $request->email_content) {
					return $this->handleError($request->email_content, __('validation.custom.email.exists'), 400);
				}
			endforeach;

			Email::create([
				'email_content' => $request->email_content,
				'company_id' => $company->id,
				'status_id' => $main_status->id
			]);
		}

		// If user want to add a phone number for the company
		if ($request->phone_number != null AND $request->phone_number == ' ') {
			// Select all company phones to check unique constraint
			$phones = Phone::where('company_id', $company->id)->get();

			if ($request->phone_code == null OR $request->phone_code == ' ') {
				return $this->handleError($request->phone_number, __('validation.required'), 400);
			}

			if ($request->service_id == null OR $request->service_id == ' ') {
				return $this->handleError($request->service_id, __('validation.required'), 400);
			}

			if ($request->phone_status_id == null OR $request->phone_status_id == ' ') {
				return $this->handleError($request->phone_status_id, __('validation.required'), 400);
			}

			// Check if phone number already exists
			foreach ($phones as $another_phone):
				if ($another_phone->phone_code == $request->phone_code AND $another_phone->phone_number == $request->phone_number) {
					return $this->handleError($request->phone_number, __('validation.custom.phone.exists'), 400);
				}
			endforeach;

			Phone::create([
				'phone_code' => $request->phone_code,
				'phone_number' => $request->phone_number,
				'service_id' => $request->service_id,
				'company_id' => $company->id,
				'status_id' => $main_status->id
			]);
		}

		// If user want to add a bank code for the company
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
				'company_id' => $company->id
			]);
		}

		// If user want to add a social network account for the company
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
				'company_id' => $company->id
			]);
		}

		/*
			HISTORY AND/OR NOTIFICATION MANAGEMENT
		*/
		History::create([
			'history_url' => 'company',
			'history_content' => __('notifications.you_added_company'),
			'user_id' => $current_user->id,
			'type_id' => $activities_history_type->id
		]);

		return $this->handleResponse(new ResourcesCompany($company), __('notifications.create_company_success'));
	}

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::find($id);

        if (is_null($company)) {
            return $this->handleError(__('notifications.find_company_404'));
        }

        return $this->handleResponse(new ResourcesCompany($company), __('notifications.find_company_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'company_name' => $request->company_name,
            'company_acronym' => $request->company_acronym,
            'website_url' => $request->website_url,
            'status_id' => $request->status_id,
            'updated_at' => now()
        ];
        // Select all companies specific company to check unique constraint
        $companies = Company::all();
        $current_company = Company::find($inputs['id']);

        if ($inputs['company_name'] == null OR $inputs['company_name'] == ' ') {
            return $this->handleError($inputs['company_name'], __('validation.required'), 400);
        }

        if ($inputs['status_id'] == null OR $inputs['status_id'] == ' ') {
            return $this->handleError($inputs['status_id'], __('validation.required'), 400);
        }

        // Check if company name already exists
        foreach ($companies as $another_company):
            if ($current_company->company_name != $inputs['company_name']) {
                if ($another_company->company_name == $inputs['company_name']) {
                    return $this->handleError($inputs['company_name'], __('validation.custom.company_name.exists'), 400);
                }
            }
        endforeach;

		$company->update($inputs);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        History::create([
            'history_url' => 'company',
            'history_content' => __('notifications.you_updated_company'),
            'company_id' => $company->id,
            'type_id' => $activities_history_type->id
        ]);

		return $this->handleResponse(new ResourcesCompany($company), __('notifications.update_company_success'));
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $notification_group = Group::where('group_name', 'Notification')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $owner_deleted_about_content = AboutContent::where('subtitle', 'Information supprimée par le propriétaire')->first();

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
		$users = User::where('company_id', $company->id)->get();

		foreach ($users as $user):
			Notification::create([
				'notification_url' => 'about/privacy_policy/' . $owner_deleted_about_content->id,
				'notification_content' => __('notifications.company_deleted1') . $company->company_name . __('notifications.company_deleted2'),
				'user_id' => $user->id,
				'status_id' => $unread_status->id
			]);
		endforeach;

		$company->delete();

        $companies = Company::orderByDesc('company_name')->get();

        return $this->handleResponse(ResourcesCompany::collection($companies), __('notifications.delete_company_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a company by its name.
     *
     * @param  int $visitor_user_id
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($visitor_user_id, $data)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $search_history_type = Type::where([['type_name', 'Historique de recherche'], ['group_id', $history_type_group->id]])->first();
        $companies = Company::search($data)->get();

		History::create([
            'history_url' => 'search/companies/' . $data,
            'history_content' => $data,
            'user_id' => $visitor_user_id,
            'type_id' => $search_history_type->id
        ]);

        return $this->handleResponse(ResourcesCompany::collection($companies), __('notifications.find_all_companies_success'));
    }

    /**
     * Search an administrator by his firstname / lastname / surname / email / phone.
     *
     * @param  int $visitor_user_id
     * @param  int $id
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchAdmin($visitor_user_id, $id, $data)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $search_history_type = Type::where([['type_name', 'Historique de recherche'], ['group_id', $history_type_group->id]])->first();
		// Find role to compare with user result
		$admin_role = Role::where('role_name', 'Administrateur')->first();
		// Start by search by user firstname
		$users_by_firstname = User::where('firstname', 'like', '%' . $data . '%')->get();
		$company = Company::find($id);

		if ($users_by_firstname == null) {
			$users_by_lastname = User::where('lastname', 'like', '%' . $data . '%')->get();

			if ($users_by_lastname == null) {
				$users_by_surname = User::where('surname', 'like', '%' . $data . '%')->get();

				if ($users_by_surname == null) {
					$users_by_email = User::where('email', 'like', '%' . $data . '%')->get();

					if ($users_by_email == null) {
						$users_by_phone = User::where('phone', 'like', '%' . $data . '%')->get();

						if ($users_by_phone == null) {
							return $this->handleError(__('notifications.find_admin_404'));

						} else {
							foreach ($users_by_phone as $user):
								foreach ($company->users as $company_user):
									if ($user->id == $company_user->id) {
										$user_roles = RoleUser::where('user_id', $user->id)->get();

										foreach ($user_roles as $user_role):
											if ($user_role->id == $admin_role->id) {
												return $this->handleResponse(ResourcesCompany::collection($users_by_phone), __('notifications.find_all_admins_success'));
				
											} else {
												return $this->handleError(__('notifications.find_admin_404'));
											}
										endforeach;
				
									} else {
										return $this->handleError(__('notifications.find_admin_404'));
									}
								endforeach;
							endforeach;
						}

					} else {
						foreach ($users_by_email as $user):
							foreach ($company->users as $company_user):
								if ($user->id == $company_user->id) {
									$user_roles = RoleUser::where('user_id', $user->id)->get();

									foreach ($user_roles as $user_role):
										if ($user_role->id == $admin_role->id) {
											return $this->handleResponse(ResourcesCompany::collection($users_by_email), __('notifications.find_all_admins_success'));
			
										} else {
											return $this->handleError(__('notifications.find_admin_404'));
										}
									endforeach;
			
								} else {
									return $this->handleError(__('notifications.find_admin_404'));
								}
							endforeach;
						endforeach;
					}

				} else {
					foreach ($users_by_surname as $user):
						foreach ($company->users as $company_user):
							if ($user->id == $company_user->id) {
								$user_roles = RoleUser::where('user_id', $user->id)->get();

								foreach ($user_roles as $user_role):
									if ($user_role->id == $admin_role->id) {
										return $this->handleResponse(ResourcesCompany::collection($users_by_surname), __('notifications.find_all_admins_success'));
		
									} else {
										return $this->handleError(__('notifications.find_admin_404'));
									}
								endforeach;
		
							} else {
								return $this->handleError(__('notifications.find_admin_404'));
							}
						endforeach;
					endforeach;
				}

			} else {
				foreach ($users_by_lastname as $user):
					foreach ($company->users as $company_user):
						if ($user->id == $company_user->id) {
							$user_roles = RoleUser::where('user_id', $user->id)->get();

							foreach ($user_roles as $user_role):
								if ($user_role->id == $admin_role->id) {
									return $this->handleResponse(ResourcesCompany::collection($users_by_lastname), __('notifications.find_all_admins_success'));
	
								} else {
									return $this->handleError(__('notifications.find_admin_404'));
								}
							endforeach;
	
						} else {
							return $this->handleError(__('notifications.find_admin_404'));
						}
					endforeach;
				endforeach;
			}

		} else {
			foreach ($users_by_firstname as $user):
				foreach ($company->users as $company_user):
					if ($user->id == $company_user->id) {
						$user_roles = RoleUser::where('user_id', $user->id)->get();

						foreach ($user_roles as $user_role):
							if ($user_role->id == $admin_role->id) {
								return $this->handleResponse(ResourcesCompany::collection($users_by_firstname), __('notifications.find_all_admins_success'));

							} else {
								return $this->handleError(__('notifications.find_admin_404'));
							}
						endforeach;

					} else {
						return $this->handleError(__('notifications.find_admin_404'));
					}
				endforeach;
			endforeach;
		}

		History::create([
			'history_url' => 'search/administrators/' . $data,
			'history_content' => $data,
			'user_id' => $visitor_user_id,
			'type_id' => $search_history_type->id
		]);
	}

    /**
     * Search a customer by his firstname / lastname / surname / email / phone.
     *
     * @param  int $visitor_user_id
     * @param  int $id
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchCustomer($visitor_user_id, $id, $data)
    {
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $search_history_type = Type::where([['type_name', 'Historique de recherche'], ['group_id', $history_type_group->id]])->first();
		// Find role to compare with user result
		$customer_role = Role::where('role_name', 'Client')->first();
		// Start by search user by firstname
		$users_by_firstname = User::where('firstname', 'like', '%' . $data . '%')->get();
		$company = Company::find($id);

		if ($users_by_firstname == null) {
			$users_by_lastname = User::where('lastname', 'like', '%' . $data . '%')->get();

			if ($users_by_lastname == null) {
				$users_by_surname = User::where('surname', 'like', '%' . $data . '%')->get();

				if ($users_by_surname == null) {
					$users_by_email = User::where('email', 'like', '%' . $data . '%')->get();

					if ($users_by_email == null) {
						$users_by_phone = User::where('phone', 'like', '%' . $data . '%')->get();

						if ($users_by_phone == null) {
							return $this->handleError(__('notifications.find_customer_404'));

						} else {
							foreach ($users_by_phone as $user):
								foreach ($company->users as $company_user):
									if ($user->id == $company_user->id) {
										$user_roles = RoleUser::where('user_id', $user->id)->get();

										foreach ($user_roles as $user_role):
											if ($user_role->id == $customer_role->id) {
												return $this->handleResponse(ResourcesCompany::collection($users_by_phone), __('notifications.find_all_customers_success'));
				
											} else {
												return $this->handleError(__('notifications.find_customer_404'));
											}
										endforeach;
				
									} else {
										return $this->handleError(__('notifications.find_customer_404'));
									}
								endforeach;
							endforeach;
						}

					} else {
						foreach ($users_by_email as $user):
							foreach ($company->users as $company_user):
								if ($user->id == $company_user->id) {
									$user_roles = RoleUser::where('user_id', $user->id)->get();

									foreach ($user_roles as $user_role):
										if ($user_role->id == $customer_role->id) {
											return $this->handleResponse(ResourcesCompany::collection($users_by_email), __('notifications.find_all_customers_success'));
			
										} else {
											return $this->handleError(__('notifications.find_customer_404'));
										}
									endforeach;
			
								} else {
									return $this->handleError(__('notifications.find_customer_404'));
								}
							endforeach;
						endforeach;
					}

				} else {
					foreach ($users_by_surname as $user):
						foreach ($company->users as $company_user):
							if ($user->id == $company_user->id) {
								$user_roles = RoleUser::where('user_id', $user->id)->get();

								foreach ($user_roles as $user_role):
									if ($user_role->id == $customer_role->id) {
										return $this->handleResponse(ResourcesCompany::collection($users_by_surname), __('notifications.find_all_customers_success'));

									} else {
										return $this->handleError(__('notifications.find_customer_404'));
									}
								endforeach;
		
							} else {
								return $this->handleError(__('notifications.find_customer_404'));
							}
						endforeach;
					endforeach;
				}

			} else {
				foreach ($users_by_lastname as $user):
					foreach ($company->users as $company_user):
						if ($user->id == $company_user->id) {
							$user_roles = RoleUser::where('user_id', $user->id)->get();

							foreach ($user_roles as $user_role):
								if ($user_role->id == $customer_role->id) {
									return $this->handleResponse(ResourcesCompany::collection($users_by_lastname), __('notifications.find_all_customers_success'));

								} else {
									return $this->handleError(__('notifications.find_customer_404'));
								}
							endforeach;
	
						} else {
							return $this->handleError(__('notifications.find_customer_404'));
						}
					endforeach;
				endforeach;
			}

		} else {
			foreach ($users_by_firstname as $user):
				foreach ($company->users as $company_user):
					if ($user->id == $company_user->id) {
						$user_roles = RoleUser::where('user_id', $user->id)->get();

						foreach ($user_roles as $user_role):
							if ($user_role->id == $customer_role->id) {
								return $this->handleResponse(ResourcesCompany::collection($users_by_firstname), __('notifications.find_all_customers_success'));

							} else {
								return $this->handleError(__('notifications.find_customer_404'));
							}
						endforeach;

					} else {
						return $this->handleError(__('notifications.find_customer_404'));
					}
				endforeach;
			endforeach;
		}

		History::create([
			'history_url' => 'search/customers/' . $data,
			'history_content' => $data,
			'user_id' => $visitor_user_id,
			'type_id' => $search_history_type->id
		]);
    }

    /**
     * Update status of a user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @param  Integer  $user_id
     * @param  Integer  $status_id
     * @return \Illuminate\Http\Response
     */
    public function updateUserStatus(Request $request, $id, $user_id)
    {
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $company = Company::find($request->company_id);
		$agent_role = Role::where(['role_name', 'Agent'])->first();
		$customer_role = Role::where(['role_name', 'Client'])->first();
        $admin = User::find($id);
        $user = User::find($user_id);
		$user_roles = RoleUser::where(['user_id', $user->id])->get();
		// Get the given status to ensure the good history to register and the good notification to send
		$new_status = Status::find($request->status_id);

		$user->update([
			'status_id' => $request->status_id,
			'updated_at' => now()
		]);

		/*
			HISTORY AND/OR NOTIFICATION MANAGEMENT
		*/
		foreach ($user_roles as $user_role):
			switch ($new_status->status_name) {
				case 'Activé':
					if ($user_role->role_id == $customer_role->id) {
						History::create([
							'history_url' => 'company/customer/' . $user->id,
							'history_content' => __('notifications.you_updated_customer_status'),
							'user_id' => $admin->id,
							'type_id' => $activities_history_type->id
						]);

						Notification::create([
							'notification_url' => 'account',
							'notification_content' => $company->company_name . ' ' . __('notifications.activated_you'),
							'user_id' => $user->id,
							'status_id' => $unread_status->id
						]);
					}

					if ($user_role->role_id == $agent_role->id) {
						History::create([
							'history_url' => 'company/agent/' . $user->id,
							'history_content' => __('notifications.you_updated_agent_status'),
							'user_id' => $admin->id,
							'type_id' => $activities_history_type->id
						]);

						Notification::create([
							'notification_url' => 'account',
							'notification_content' => $company->company_name . ' ' . __('notifications.activated_you'),
							'user_id' => $user->id,
							'status_id' => $unread_status->id
						]);
					}
					break;

				case 'Désactivé':
					if ($user_role->role_id == $customer_role->id) {
						History::create([
							'history_url' => 'company/customer/' . $user->id,
							'history_content' => __('notifications.you_updated_customer_status'),
							'user_id' => $admin->id,
							'type_id' => $activities_history_type->id
						]);

						Notification::create([
							'notification_url' => 'account',
							'notification_content' => $company->company_name . ' ' . __('notifications.deactivated_you'),
							'user_id' => $user->id,
							'status_id' => $unread_status->id
						]);
					}

					if ($user_role->role_id == $agent_role->id) {
						History::create([
							'history_url' => 'company/agent/' . $user->id,
							'history_content' => __('notifications.you_updated_agent_status'),
							'user_id' => $admin->id,
							'type_id' => $activities_history_type->id
						]);

						Notification::create([
							'notification_url' => 'account',
							'notification_content' => $company->company_name . ' ' . __('notifications.deactivated_you'),
							'user_id' => $user->id,
							'status_id' => $unread_status->id
						]);
					}
					break;

				case 'Bloqué':
					if ($user_role->role_id == $customer_role->id) {
						History::create([
							'history_url' => 'company/customer/' . $user->id,
							'history_content' => __('notifications.you_updated_customer_status'),
							'user_id' => $admin->id,
							'type_id' => $activities_history_type->id
						]);

						Notification::create([
							'notification_url' => 'account',
							'notification_content' => $company->company_name . ' ' . __('notifications.blocked_you'),
							'user_id' => $user->id,
							'status_id' => $unread_status->id
						]);
					}
	
					if ($user_role->role_id == $agent_role->id) {
						History::create([
							'history_url' => 'company/agent/' . $user->id,
							'history_content' => __('notifications.you_updated_agent_status'),
							'user_id' => $admin->id,
							'type_id' => $activities_history_type->id
						]);
	
						Notification::create([
							'notification_url' => 'account',
							'notification_content' => $company->company_name . ' ' . __('notifications.blocked_you'),
							'user_id' => $user->id,
							'status_id' => $unread_status->id
						]);
					}
					break;
	
				default:
					break;
			}
		endforeach;

		return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Update role of a company user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @param  Integer  $user_id
     * @param  Integer  $role_id
     * @return \Illuminate\Http\Response
     */
    public function updateUserRole(Request $request, $id, $user_id)
    {
        $notification_group = Group::where('group_name', 'Notification')->first();
        $history_type_group = Group::where('group_name', 'Type d\'historique')->first();
        $unread_status = Status::where([['status_name', 'Non lue'], ['group_id', $notification_group->id]])->first();
        $activities_history_type = Type::where([['type_name', 'Historique des activités'], ['group_id', $history_type_group->id]])->first();
        $company = Company::find($request->company_id);
		$agent_role = Role::where(['role_name', 'Agent'])->first();
		$customer_role = Role::where(['role_name', 'Client'])->first();
		$admin = User::find($id);
		$user = User::find($user_id);
		$user_roles = RoleUser::where(['user_id', $user->id])->get();

		foreach ($user_roles as $user_role):
			$user_role->update([
				'selected' => 0,
				'updated_at' => now()
			]);

			if ($user_role->id != $request->role_id) {
				RoleUser::create([
					'role_id' => $request->role_id,
					'user_id' => $user->id,
					'selected' => 1
				]);

			} else {
				$current_user_role = RoleUser::where(['role_id', $request->role_id])->first();

				$current_user_role->update([
					'selected' => 1,
					'updated_at' => now()
				]);
			}

			/*
				HISTORY AND/OR NOTIFICATION MANAGEMENT
			*/
			if ($user_role->role_id == $customer_role->id) {
				History::create([
					'history_url' => 'company/customer/' . $user->id,
					'history_content' => __('notifications.you_updated_user_role'),
					'user_id' => $admin->id,
					'type_id' => $activities_history_type->id
				]);

				Notification::create([
					'notification_url' => 'account',
					'notification_content' => $company->company_name . ' ' . __('notifications.updated_your_role'),
					'user_id' => $user->id,
					'status_id' => $unread_status->id
				]);
			}
	
			if ($user_role->role_id == $agent_role->id) {
				History::create([
					'history_url' => 'company/agent/' . $user->id,
					'history_content' => __('notifications.you_updated_user_role'),
					'user_id' => $admin->id,
					'type_id' => $activities_history_type->id
				]);

				Notification::create([
					'notification_url' => 'account',
					'notification_content' => $company->company_name . ' ' . __('notifications.updated_your_role'),
					'user_id' => $user->id,
					'status_id' => $unread_status->id
				]);
			}
		endforeach;

		return $this->handleResponse(new ResourcesCompany($company), __('notifications.update_company_success'));
    }

    /**
     * Update company logo picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updateLogoPicture(Request $request, $id)
    {
        $inputs = [
            'company_id' => $request->entity_id,
            'image_64' => $request->base64image
        ];
        // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
        $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
        // Find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $inputs['image_64']);
        $image = str_replace(' ', '+', $image);
		// Find type by name to get its ID
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
		$photo_type = Type::where([['type_name', 'Photo'], ['group_id', $file_type_group->id]])->first();
		// Find album by name to get its ID
		$logo_album = Album::where('album_name', 'Logos')->where('company_id', $inputs['company_id'])->first();
		// Find status by name to store its ID in "files" table
        $functioning_group = Group::where('group_name', 'Fonctionnement')->first();
		$main_status = Status::where([['status_name', 'Principal'], ['group_id', $functioning_group->id]])->first();
		$secondary_status = Status::where([['status_name', 'Secondaire'], ['group_id', $functioning_group->id]])->first();

		if ($logo_album != null) {
            // Select all files to update their statuses
            $logo_images = File::where('album_id', $logo_album->id)->where('status_id', $main_status->id)->get();

			// If status with given name exist
			if ($secondary_status != null) {
                foreach ($logo_images as $logo):
                    $logo->update([
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

                    foreach ($logo_images as $logo):
                        $logo->update([
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

                    foreach ($logo_images as $logo):
                        $logo->update([
                            'status_id' => $status->id,
                            'updated_at' => now()
                        ]);
                    endforeach;
                }
			}

			// Create file name
			$file_name = 'images/companies/' . $inputs['company_id'] . '/' . $logo_album->id . '/' . Str::random(50) . '.png';

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, base64_decode($image)));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $logo_album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $logo_album->id,
                            'status_id' => $main_status->id
                        ]);

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
                            'album_id' => $logo_album->id,
                            'status_id' => $main_status->id
                        ]);
                    }
                }

			// Otherwhise, create status with necessary name
			} else {
				$status = Status::create([
					'status_name' => 'Principal',
					'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
					'group_id' => $functioning_group->id
				]);

                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $logo_album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
                        $type = Type::create([
                            'type_name' => 'Photo',
                            'type_description' => 'Uploadez des photos depuis les dossiers de votre appareil ou en utilisant votre caméra.',
                            'group_id' => $file_type_group->id
                        ]);

                        File::create([
                            'file_content' => '/' . $file_name,
                            'type_id' => $type->id,
                            'album_id' => $logo_album->id,
                            'status_id' => $main_status->id
                        ]);

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
                            'album_id' => $logo_album->id,
                            'status_id' => $main_status->id
                        ]);
                    }
                }
			}

		} else {
			// Store album name in "albums" table
			$album = Album::create([
				'album_name' => 'Logos',
				'company_id' => $inputs['company_id']
			]);
			// Create file name
			$file_name = 'images/companies/' . $inputs['company_id'] . '/' . $album->id . '/' . Str::random(50) . '.png';

			// Upload file
			Storage::url(Storage::disk('public')->put($file_name, base64_decode($image)));

			// If status with given name exist
			if ($main_status != null) {
                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $album->id,
                        'status_id' => $main_status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
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
                }

			// Otherwhise, create status with necessary name
			} else {
				$status = Status::create([
					'status_name' => 'Principal',
					'status_description' => 'Donnée visible en premier (Exemple : Une photo mise en couverture dans un album ou un numéro de téléphone principal parmi plusieurs).',
					'group_id' => $functioning_group->id
				]);

                // Store file name in "files" table with existing status and existing type if it exists, otherwise, create the type
                if ($photo_type != null) {
                    File::create([
                        'file_content' => '/' . $file_name,
                        'type_id' => $photo_type->id,
                        'album_id' => $album->id,
                        'status_id' => $status->id
                    ]);

                } else {
                    if ($file_type_group != null) {
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
                }
			}
		}

		$company = Company::find($id);

        $company->update([
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesCompany($company), __('notifications.update_company_success'));
    }
}
