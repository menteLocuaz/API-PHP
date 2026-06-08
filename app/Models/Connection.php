<?php

namespace Arancamon\ApiPhp\Models;

class Connection
{
    // informacion de la base de datos
    public static function InfoDatabase()
    {
        $InfoDB = array(
            'host' => 'localhost',
            'database' => 'arctic',
            'user' => 'sa', // Cambia según tu configuración
            'pass' => '52UYT', // Cambia según tu configuración
        );

        return $InfoDB;
    }

    // Conexion a la base de datos
    public static function Connect()
    {
        try {
            $infoDB = self::InfoDatabase();
            $link = new \PDO(
                'pgsql:host=' . $infoDB['host'] . ';dbname=' . $infoDB['database'],
                $infoDB['user'],
                $infoDB['pass'],
            );
            $link->exec("SET NAMES 'UTF8'");
        } catch (\PDOException $e) {
            die('Error: ' . $e->getMessage());
        }

        return $link;
    }
}
