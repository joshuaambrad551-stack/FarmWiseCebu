<?php

return [
    'model' => 'Farm',
    'title' => 'Farms',
    'singular' => 'Farm',
    'page' => 'farms.php',
    'pk' => 'farm_id',
    'delete_label' => 'farm_name',
    'search_placeholder' => 'Farm name, location or farmer',
    'columns' => [
        ['key' => 'farm_id', 'label' => 'ID', 'sort' => 'id'],
        ['key' => 'farm_name', 'label' => 'Farm', 'sort' => 'name'],
        ['key' => 'farmer_name', 'label' => 'Farmer', 'sort' => 'farmer'],
        ['key' => 'location', 'label' => 'Location', 'sort' => 'location'],
        ['key' => 'area_hectares', 'label' => 'Area (ha)', 'sort' => 'area', 'format' => 'decimal'],
        ['key' => 'soil_type_name', 'label' => 'Soil', 'sort' => 'soil'],
        ['key' => 'irrigation_type_name', 'label' => 'Irrigation', 'sort' => 'irrigation'],
    ],
    'filters' => [
        ['name' => 'irrigation_type_id', 'label' => 'Irrigation', 'type' => 'select', 'lookup' => 'irrigation_type'],
        ['name' => 'soil_type_id', 'label' => 'Soil type', 'type' => 'select', 'lookup' => 'soil_type'],
        ['name' => 'farmer_id', 'label' => 'Farmer', 'type' => 'select', 'lookup' => 'farmer'],
    ],
    'view' => [
        ['key' => 'farm_name', 'label' => 'Farm name'],
        ['key' => 'farmer_name', 'label' => 'Farmer'],
        ['key' => 'location', 'label' => 'Location'],
        ['key' => 'area_hectares', 'label' => 'Area (hectares)', 'format' => 'decimal'],
        ['key' => 'soil_type_name', 'label' => 'Soil type'],
        ['key' => 'irrigation_type_name', 'label' => 'Irrigation type'],
        ['key' => 'planting_count', 'label' => 'Planting records', 'format' => 'int'],
    ],
];
