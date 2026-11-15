<?php

return [
    'model' => 'User',
    'title' => 'User Accounts',
    'singular' => 'User',
    'page' => 'users.php',
    'pk' => 'user_id',
    'admin_only' => true,
    'protect_self' => true,
    'delete_label' => 'username',
    'search_placeholder' => 'Username or full name',
    'columns' => [
        ['key' => 'user_id', 'label' => 'ID', 'sort' => 'id'],
        ['key' => 'username', 'label' => 'Username', 'sort' => 'username'],
        ['key' => 'full_name', 'label' => 'Full name', 'sort' => 'name'],
        ['key' => 'role_name', 'label' => 'Role', 'sort' => 'role'],
        ['key' => 'is_active', 'label' => 'Active', 'format' => 'bool'],
    ],
    'filters' => [
        ['name' => 'role_id', 'label' => 'Role', 'type' => 'select', 'lookup' => 'app_role'],
    ],
    'view' => [
        ['key' => 'username', 'label' => 'Username'],
        ['key' => 'full_name', 'label' => 'Full name'],
        ['key' => 'role_name', 'label' => 'Role'],
        ['key' => 'is_active', 'label' => 'Active', 'format' => 'bool'],
    ],
];
