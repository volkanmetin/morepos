<?php

return [
    'title' => 'Settings',
    'save' => 'Save',
    'saving' => 'Saving...',
    'saved' => 'Settings saved successfully.',

    'groups' => [
        'company' => [
            'title' => 'Company Information',
            'name' => 'Company Name',
            'address' => 'Company Address',
            'phone' => 'Company Phone',
            'email' => 'Company Email',
            'tax_office' => 'Tax Office',
            'tax_number' => 'Tax Number',
        ],
        'sales' => [
            'title' => 'Sales Settings',
            'tax_rate' => 'Tax Rate (%)',
            'default_payment_method' => 'Default Payment Method',
            'allow_negative_stock' => 'Allow Negative Stock',
            'show_stock_warning' => 'Show Stock Warning',
            'stock_warning_limit' => 'Stock Warning Limit',
        ],
        'invoice' => [
            'title' => 'Invoice Settings',
            'prefix' => 'Invoice Prefix',
            'starting_number' => 'Invoice Starting Number',
            'footer_text' => 'Invoice Footer Text',
        ],
    ],

    'payment_methods' => [
        'cash' => 'Cash',
        'pos1' => 'POS 1',
        'pos2' => 'POS 2',
    ],
]; 