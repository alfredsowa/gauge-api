<?php

return [

    // Business Types
    'business_type' => [
        "Sole Proprietorship",
        "Partnership",
        "Limited Liability Company (LLC)",
        "Corporation",
        "Nonprofit Organization"
    ],

    // Business Sizes
    'business_size' => [
        "1 - 5 Employees",
        "6 - 10 Employees",
        "11 - 40 Employees",
        "41 - 100 Employees",
        "100+ Employees",
    ],

    //Business Industry
    'business_industry' => [
        "Food and Beverage",
        "Retail",
        "Fashion",
        "Wood Work",
        "Other",
    ],

    //Material Type
    'material_type' => [
        "In-house",
        "Sourced",
    ],

    //Languages
    'language' => [
        "English",
    ],

    //Timezones
    'timezone' => [
        "UTC",
        "GMT",
        "Eastern Time (ET)",
        "Central Time (CT)",
        "Mountain Time (MT)",
        "Pacific Time (PT)",
        "Australian Eastern Time (AET)"
    ],

    'currency' => [
        ['value' => 'GHS','label' => 'Ghana Cedi - GHS'],
        ['value' => 'NGN','label' => 'Nigeria Naira - NGN'],
        ['value' => 'USD','label' => 'United States Dollar - USD'],
    ],

    'currency_symbol' => [
        "GHS" => "GH₵",
        "NGN" => "₦",
        "USD" => "$",
    ],

    //Countries
    'country' => [
        "Ghana",
        "Nigeria",
        "Other",
    ],

    //Priority
    'priority' => [
        "Low",
        "Normal",
        "Critical",
    ],

    'production_status' => ['backlog','staged', 'in_progress', 'completed', 'on_hold','cancel', 'damaged', 'quality_control'],
    'production_ends' => ['cancel', 'damaged', 'completed'],
    'production_type' => ['product','intermediate_good'],
    'production_category' => ['product','sample', 'training'],
    'sales_type' => ['retail','wholesale'],
    'sales_channels' => ['in-person','website','social_media','exhibition'],
    'sales_channels_colors' => ['gauge-primary.1','gauge-primary.4','gauge-primary.5','gauge-primary.7'],
    'payment_status' => [
        "pending",
        "paid",
        "refunded",
        "partially_refunded",
        "on_hold"
    ],
    'payment_method' => [
        "cash",
        "mobile_payment",
        "debit_card",
        "online_banking",
        "cheque",
        "other"
    ],
    'order_status' => [
        "pending",
        "returned",
        "completed",
    ],
    'modules' => [
        "dashboard",
        "materials",
        "products",
        "sales",
        "productions",
        "purchases",
        "suppliers",
        "employees",
        "reconciliations",
    ]
];
