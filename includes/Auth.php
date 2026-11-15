<?php

class Auth {
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;

    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int {
        return isset($_SESSION['user']) ? (int) $_SESSION['user']['user_id'] : null;
    }

    public static function check(): bool {
        return isset($_SESSION['user']);
    }

    public static function isAdmin(): bool {
        return self::check() && $_SESSION['user']['role_name'] === 'Administrator';
    }

    public static function lockedSeconds(): int {
        $until = $_SESSION['login_lock_until'] ?? 0;
        return max(0, $until - time());
    }

    public static function attempt(PDO $db, string $username, string $password): bool {
        if (self::lockedSeconds() > 0) {
            return false;
        }
        $sql = "SELECT u.user_id, u.username, u.full_name, u.password_hash, u.is_active, r.role_name
                FROM app_user u
                JOIN app_role r ON r.role_id = u.role_id
                WHERE u.username = :username";
        $stmt = $db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsalt$' . str_repeat('x', 31);
        $valid = password_verify($password, $hash);

        if ($user && $valid && $user['is_active']) {
            session_regenerate_id(true);
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            unset($_SESSION['login_fail'], $_SESSION['login_lock_until']);
            return true;
        }

        $_SESSION['login_fail'] = ($_SESSION['login_fail'] ?? 0) + 1;
        if ($_SESSION['login_fail'] >= self::MAX_ATTEMPTS) {
            $_SESSION['login_lock_until'] = time() + self::LOCK_SECONDS;
            $_SESSION['login_fail'] = 0;
        }
        return false;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function require(): void {
        if (!self::check()) {
            redirect('login.php');
        }
    }

    public static function requireAdmin(): void {
        self::require();
        if (!self::isAdmin()) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
}
