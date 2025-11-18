<?php

namespace Src\Shared\Infrastructure;

use \PDO;
use \PDOException;

final class Database
{
    private static ?PDO $connection = null;

    
    private string $host = '127.0.0.1'; 
    private string $db_name = 'hotel_management'; 
    private string $username = 'root';    // Tu usuario de BD
    private string $password = ''; // Tu contraseña

    /**
     * Retorna una instancia única de la conexión PDO.
     */
    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';
                
                self::$connection = new PDO($dsn, $this->username, $this->password);
                
                // Configurar PDO para que lance excepciones en caso de error
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Configurar para que devuelva los resultados como arrays asociativos
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch(PDOException $e) {
                // En un sistema real, esto debería ir a un log, no a un 'echo'.
                throw new \RuntimeException('Error de Conexión: ' . $e->getMessage());
            }
        }
        return self::$connection;
    }
}