<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains the company information used for generating
    | invoices and other business documents.
    |
    */

    'invoice' => [
        'company_name'    => env('COMPANY_NAME', 'Highbase Market'),
        'company_logo'    => env('COMPANY_LOGO', '/highbase.png'),
        'company_email'   => env('COMPANY_EMAIL', 'support@highbasemarket.com'),
        'company_phone'   => env('COMPANY_PHONE', '+973 1330 0833'),
        'company_address' => env('COMPANY_ADDRESS', 'HIGHBASE TRADING W.L.L Road 2845 Seef, Kingdom of Bahrain'),
        'tax_number'      => env('COMPANY_TAX_NUMBER'),
        'cr_number'       => env('COMPANY_CR_NUMBER'),
        'currency'        => env('COMPANY_CURRENCY', 'BHD'),
    ],
    'branch_id'                      => env('HIGHBASE_BRANCH_ID', 1),
    'account_id'                     => env('HIGHBASE_ACCOUNT_ID', 1),
    'catalog_created_by_employee_id' => env('HIGHBASE_CATALOG_CREATED_BY_EMPLOYEE_ID', 1),
    'visits'                         => [
        'default_display_timezone' => env('VISIT_DEFAULT_DISPLAY_TIMEZONE', 'Asia/Bahrain'),
    ],

    'supervisors' => [
        '939',
        '940',
        '941',
        '942',
        '943',
        '1029',
        '1034',
        '1035',
        '1036',
    ],

    'supervisors_employees' => [
        '939' => ['940', '941', '942', '943'],
        '940' => ['940', '941', '942', '943'],
        '941' => ['940', '941', '942', '943'],
        '942' => ['940', '941', '942', '943'],
        '943' => ['940', '941', '942', '943'],
        '1029' => ['940', '941', '942', '943'],
        '1034' => ['940', '941', '942', '943'],
        '1035' => ['940', '941', '942', '943'],
        '1036' => ['940', '941', '942', '943'],
    ],

    'new_employees' => ['940', '941', '942', '943'],
];
