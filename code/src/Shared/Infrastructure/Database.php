<?php

namespace Src\Shared\Infrastructure;

use \PDO;
use \PDOException;

final class Database
{
    private static ?PDO $connection = null;

    private string $host = '127.0.0.1'; 
    private string $db_name = 'hotel_management';
    private string $username = 'root';
    private string $password = '';

    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                // PUERTO AGREGADO AQUÍ 👇
                $dsn = 'mysql:host=' . $this->host . ';port=3307;dbname=' . $this->db_name . ';charset=utf8mb4';

                self::$connection = new PDO($dsn, $this->username, $this->password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch(PDOException $e) {
                throw new \RuntimeException('Error de Conexión: ' . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
