<?php
declare(strict_types=1);

/**
 * Obtiene la configuración de conexión para PDO.
 *
 * @return array{dsn:string,user:string,password:string,options:array}
 */
function pdoConfig(): array
{
    return [
        'dsn' => 'mysql:host=localhost;dbname=biblioteca;port=3306;charset=utf8mb4',
        'user' => 'root',
        'password' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ];
}

function getPdoConnection(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = pdoConfig();
    $connection = new PDO($config['dsn'], $config['user'], $config['password'], $config['options']);

    return $connection;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}