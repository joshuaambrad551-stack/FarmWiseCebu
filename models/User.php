<?php

class User extends BaseModel {
    protected string $table = 'app_user';
    protected string $pk = 'user_id';
    protected string $alias = 'u';
    protected bool $audit = false;

    public function fields(): array {
        return [
            'username' => [
                'label' => 'Username',
                'type' => 'text',
                'max' => 50,
                'pattern' => '/^[A-Za-z0-9_.]{3,50}$/',
                'pattern_message' => 'Username must be 3 to 50 characters: letters, numbers, dot or underscore.',
            ],
            'full_name' => ['label' => 'Full name', 'type' => 'text', 'max' => 100],
            'role_id' => ['label' => 'Role', 'type' => 'select', 'lookup' => 'app_role'],
            'password' => ['label' => 'Password', 'type' => 'password', 'required' => 'create'],
            'is_active' => ['label' => 'Active', 'type' => 'checkbox'],
        ];
    }

    protected function selectSql(): string {
        return 'u.user_id, u.username, u.full_name, u.role_id, r.role_name, u.is_active, u.created_at, u.updated_at';
    }

    protected function fromSql(): string {
        return 'app_user u JOIN app_role r ON r.role_id = u.role_id';
    }

    protected function searchSql(): string {
        return "(u.username || ' ' || u.full_name)";
    }

    protected function filterSql(): array {
        return [
            'role_id' => ['sql' => 'u.role_id = :role_id', 'type' => 'int'],
        ];
    }

    protected function sortMap(): array {
        return [
            'id' => 'u.user_id',
            'username' => 'u.username',
            'name' => 'u.full_name',
            'role' => 'r.role_name',
        ];
    }

    protected function prepareData(array $clean): array {
        if (isset($clean['password']) && $clean['password'] !== '') {
            $clean['password_hash'] = password_hash($clean['password'], PASSWORD_DEFAULT);
        }
        unset($clean['password']);
        return $clean;
    }

    public function crossCheck(array $clean, array &$errors, ?int $id): void {
        if (isset($clean['username']) && $this->valueExists('username', $clean['username'], $id)) {
            $errors['username'] = 'That username is already taken.';
        }
        if ($id !== null && $id === Auth::id()) {
            if (isset($clean['is_active']) && !$clean['is_active']) {
                $errors['is_active'] = 'You cannot deactivate your own account.';
            }
            if (isset($clean['role_id']) && $clean['role_id'] !== 1) {
                $errors['role_id'] = 'You cannot remove your own Administrator role.';
            }
        }
    }
}
