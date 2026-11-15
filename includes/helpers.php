<?php

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_pull(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool {
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}

function query_string(array $override = []): string {
    $params = array_merge($_GET, $override);
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }
    return http_build_query($params);
}

function format_value($value, string $format = 'text'): string {
    if ($value === null || $value === '') {
        return '-';
    }
    switch ($format) {
        case 'date':
            return e($value);
        case 'decimal':
            return number_format((float) $value, 2, '.', '');
        case 'number':
            return number_format((float) $value, 2, '.', ',');
        case 'money':
            return 'PHP ' . number_format((float) $value, 2, '.', ',');
        case 'int':
            return number_format((int) $value);
        case 'datetime':
            return date('Y-m-d H:i', strtotime((string) $value));
        case 'bool':
            return ($value === true || $value === 't' || $value === '1' || $value === 1) ? 'Yes' : 'No';
        case 'status':
            $classes = [
                'Harvested' => 'success',
                'Growing' => 'primary',
                'Planted' => 'secondary',
                'Failed' => 'danger',
            ];
            $class = $classes[$value] ?? 'secondary';
            return '<span class="badge text-bg-' . $class . '">' . e($value) . '</span>';
        default:
            return e($value);
    }
}

function render(string $view, array $vars = []): void {
    extract($vars);
    require __DIR__ . '/header.php';
    require __DIR__ . '/views/' . $view . '.php';
    require __DIR__ . '/footer.php';
}

function abort(int $code, string $message): void {
    http_response_code($code);
    render('error', ['code' => $code, 'message' => $message, 'pageTitle' => 'Error']);
    exit;
}
