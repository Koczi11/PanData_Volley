<?php

declare(strict_types=1);

namespace App\Config;

final class Database
{
    private ?\PDO $connection = null;

    public function __construct(private readonly Env $env)
    {
    }

    public function getConnection(): \PDO
    {
        if ($this->connection instanceof \PDO) {
            return $this->connection;
        }

        $this->env->requireKeys(
            'DB_HOST',
            'DB_PORT',
            'POSTGRES_DB',
            'POSTGRES_USER',
            'POSTGRES_PASSWORD'
        );

        $host = trim($this->env->require('DB_HOST'));
        $port = $this->env->getInt('DB_PORT');
        $databaseName = trim($this->env->require('POSTGRES_DB'));
        $username = trim($this->env->require('POSTGRES_USER'));
        $password = $this->env->require('POSTGRES_PASSWORD');

        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $databaseName);

        try {
            $this->connection = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $exception) {
            error_log(sprintf(
                'Database connection error (%s:%d/%s): %s',
                $host,
                $port,
                $databaseName,
                $exception->getMessage()
            ));

            throw new \RuntimeException('Database connection failed.', 0, $exception);
        }

        return $this->connection;
    }
}
