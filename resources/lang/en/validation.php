<?php
/**
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'This field must be true or false.',
    'confirmed' => 'The field confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'This field must be a valid email address.',
    'ends_with' => 'This field must end with one of the following: :values.',
    'exists' => 'The selected field is invalid.',
    'file' => 'This field must be a file.',
    'filled' => 'This field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'string' => 'The :attribute must not be greater than :max characters.',
        'array' => 'The :attribute must not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'This field must be present.',
    'prohibited' => 'This field is prohibited.',
    'prohibited_if' => 'This field is prohibited when :other is :value.',
    'prohibited_unless' => 'This field is prohibited unless :other is in :values.',
    'prohibits' => 'This field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'This field is required.',
    'required_if' => 'This field is required when :other is :value.',
    'required_unless' => 'This field is required unless :other is in :values.',
    'required_with' => 'This field is required when :values is present.',
    'required_with_all' => 'This field is required when :values are present.',
    'required_without' => 'This field is required when :values is not present.',
    'required_without_all' => 'This field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'email' => [
            'incorrect' => 'Write a valid e-mail address please',
            'exists' => 'The provided e-mail address already exists',
            'user_or_company_or_office' => [
                'required' => 'The user or the company or rather the office must be defined'
            ]
        ],
        'phone' => [
            'incorrect' => 'Write a valid phone number please',
            'exists' => 'The provided phone number already exists',
            'user_or_company_or_office' => [
                'required' => 'The user or the company or rather the office must be defined'
            ]
        ],
        'email_or_phone' => [
            'required' => 'The email address or the phone number must be defined'
        ],
        'user_or_company' => [
            'required' => 'The user or the company must be defined'
        ],
        'group_name' => [
            'exists' => 'This group name already exists'
        ],
        'role_name' => [
            'exists' => 'This role name already exists'
        ],
        'status_name' => [
            'exists' => 'This status name already exists'
        ],
        'service_name' => [
            'exists' => 'This service name already exists'
        ],
        'content' => [
            'exists' => 'This content already exists'
        ],
        'subject' => [
            'exists' => 'This subject already exists'
        ],
        'title' => [
            'exists' => 'This title already exists'
        ],
        'description' => [
            'exists' => 'This description already exists'
        ],
        'continent_name' => [
            'exists' => 'This continent already exists'
        ],
        'region_name' => [
            'exists' => 'This region already exists at the chosen continent'
        ],
        'country_name' => [
            'exists' => 'This country already exists at the chosen region'
        ],
        'province_name' => [
            'exists' => 'This province already exists at the chosen country'
        ],
        'city_name' => [
            'exists' => 'This city already exists at the chosen province'
        ],
        'area_name' => [
            'exists' => 'This area already exists at the chosen city'
        ],
        'neighborhood_name' => [
            'exists' => 'This neighborhood already exists at the chosen area'
        ],
        'currency_name' => [
            'exists' => 'This currency already exists'
        ],
        'address' => [
            'exists' => 'This address already exists at the chosen neighborhood'
        ],
        'company_name' => [
            'exists' => 'This company name already exists'
        ],
        'card_number' => [
            'exists' => 'This card number already exists'
        ],
        'network_url' => [
            'exists' => 'This URL already exists'
        ],
        'code' => [
            'exists' => 'This code already exists'
        ],
        'billing_method' => [
            'exists' => 'This billing method already exists'
        ],
        'album_name' => [
            'exists' => 'This album name already exists'
        ],
        'file' => [
            'exists' => 'This file already exists in this album'
        ],
        'icon_name' => [
            'exists' => 'This icon name already exists'
        ],
        'owner' => [
            'required' => 'What entity does it belong?'
        ],
        'type_name' => [
            'exists' => 'This type name already exists'
        ],
        'area_name' => [
            'exists' => 'This coverage area name already exists'
        ],
        'invoice_number' => [
            'required' => 'This invoice number already exists'
        ],
        'invoiced_period' => [
            'required' => 'The billed period for this year is already created for this customer'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
