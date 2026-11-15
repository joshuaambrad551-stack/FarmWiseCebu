<?php

return [
    'model' => 'Farmer',
    'title' => 'Farmers',
    'singular' => 'Farmer',
    'page' => 'farmers.php',
    'pk' => 'farmer_id',
    'delete_label' => 'farmer_name',
    'search_placeholder' => 'Name, contact number or address',
    'columns' => [
        ['key' => 'farmer_id', 'label' => 'ID', 'sort' => 'id'],
        ['key' => 'farmer_name', 'label' => 'Name', 'sort' => 'name'],
        ['key' => 'contact_number', 'label' => 'Contact', 'sort' => 'contact'],
        ['key' => 'address', 'label' => 'Address', 'sort' => 'address'],
        ['key' => 'date_registered', 'label' => 'Registered', 'sort' => 'registered', 'format' => 'date'],
        ['key' => 'farm_count', 'label' => 'Farms', 'sort' => 'farms', 'format' => 'int'],
    ],
    'filters' => [
        ['name' => 'address', 'label' => 'Address contains', 'type' => 'text'],
    ],
    'view' => [
        ['key' => 'farmer_name', 'label' => 'Name'],
        ['key' => 'contact_number', 'label' => 'Contact number'],
        ['key' => 'address', 'label' => 'Address'],
        ['key' => 'date_registered', 'label' => 'Date registered', 'format' => 'date'],
        ['key' => 'farm_count', 'label' => 'Number of farms', 'format' => 'int'],
    ],
];
