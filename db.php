<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $port = getenv('DB_PORT') ?: '3306';
            $dsn = 'mysql:host=127.0.0.1;port=' . $port . ';dbname=baranggay_recipes;charset=utf8mb4';
            self::$connection = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$connection;
    }
}

$pdo = Database::connection();
