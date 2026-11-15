<?php

class Database {
    private string $host;
    private string $port;
    private string $dbname;
    private string $username;
    private string $password;
    private string $sslmode;

    public function __construct() {
        $this->loadEnv(dirname(__DIR__) . '/.env');
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->port = getenv('DB_PORT') ?: '5432';
        $this->dbname = getenv('DB_NAME') ?: 'postgres';
        $this->username = getenv('DB_USER') ?: 'postgres';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->sslmode = getenv('DB_SSLMODE') ?: 'prefer';
    }

    private function loadEnv(string $path): void {
        if (!is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            if (getenv($name) === false) {
                putenv($name . '=' . trim($value));
            }
        }
    }

    public function connect(): PDO {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode={$this->sslmode}";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            return $pdo;
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Unable to connect to the database.');
        }
    }
}
