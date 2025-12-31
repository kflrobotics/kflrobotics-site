<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        // ENV'den oku
        $host    = env('DB_HOST', 'localhost');
        $db      = env('DB_NAME', '');
        $user    = env('DB_USER', '');
        $pass    = env('DB_PASS', '');
        $charset = env('DB_CHARSET', 'utf8mb4');

        if ($db === '' || $user === '') {
            throw new RuntimeException('Database ENV variables are missing.');
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $host,
            $db,
            $charset
        );

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            error_log('[DB] ' . $e->getMessage());
            http_response_code(500);
            exit('Database connection error.');
            exit;
        }

        return self::$pdo;
    }

    public static function prepare(string $sql): PDOStatement
    {
        return self::connection()->prepare($sql);
    }
}
