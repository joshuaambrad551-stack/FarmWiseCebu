<?php

return [
    'model' => 'Crop',
    'title' => 'Crops',
    'singular' => 'Crop',
    'page' => 'crops.php',
    'pk' => 'crop_id',
    'delete_label' => 'crop_name',
    'search_placeholder' => 'Crop name or variety',
    'columns' => [
        ['key' => 'crop_id', 'label' => 'ID', 'sort' => 'id'],
        ['key' => 'crop_name', 'label' => 'Crop', 'sort' => 'name'],
        ['key' => 'variety', 'label' => 'Variety', 'sort' => 'variety'],
        ['key' => 'crop_category_name', 'label' => 'Category', 'sort' => 'category'],
        ['key' => 'growing_period_days', 'label' => 'Growing days', 'sort' => 'days', 'format' => 'int'],
        ['key' => 'season_name', 'label' => 'Season', 'sort' => 'season'],
    ],
    'filters' => [
        ['name' => 'crop_category_id', 'label' => 'Category', 'type' => 'select', 'lookup' => 'crop_category'],
        ['name' => 'season_id', 'label' => 'Season', 'type' => 'select', 'lookup' => 'season'],
    ],
    'view' => [
        ['key' => 'crop_name', 'label' => 'Crop name'],
        ['key' => 'variety', 'label' => 'Variety'],
        ['key' => 'crop_category_name', 'label' => 'Category'],
        ['key' => 'growing_period_days', 'label' => 'Growing period (days)', 'format' => 'int'],
        ['key' => 'season_name', 'label' => 'Recommended season'],
        ['key' => 'planting_count', 'label' => 'Planting records', 'format' => 'int'],
    ],
];
