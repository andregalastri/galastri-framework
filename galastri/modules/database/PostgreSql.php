<?php

namespace galastri\modules\database;

class PostgreSql extends MySql
{
    const DRIVER = 'pgsql';

    protected string $port = '';

    public function export(string $filePath, string $specificTable = '')
    {
        throw new Exception(
            sprintf(self::DATABASE_UNAVAILABLE_EXPORT_METHOD[1], __CLASS__),
            self::DATABASE_UNAVAILABLE_EXPORT_METHOD[0]
        );
    }

    protected function resolveLastId(): int
    {
        try {
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            return -1;
        }
    }
}
