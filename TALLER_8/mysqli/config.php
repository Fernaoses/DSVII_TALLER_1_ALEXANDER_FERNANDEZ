<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Returns the configuration array for the MySQL connection.
 *
 * @return array{host:string,username:string,password:string,database:string,port:int,charset:string}
 */
function mysqliConfig(): array
{
    return [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'biblioteca',
        'port' => 3306,
        'charset' => 'utf8mb4',
    ];
}

/**
 * Provides a shared MySQLi connection using the configuration above.
 */
function getMysqliConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $config = mysqliConfig();

    $connection = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port']
    );

    if (! $connection->set_charset($config['charset'])) {
        throw new RuntimeException('No fue posible configurar el charset de la conexión.');
    }

    return $connection;
}

/**
 * Sanitiza cadenas para su uso seguro en salidas HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}