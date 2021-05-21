<?php

namespace galastri\modules\database;

/**
 * This class is related to PostgreSql SGBD, and extends the MySql class because it uses most of the
 * methods from that class.
 */
class PostgreSql extends MySql
{
    /**
     * The driver that is used by the PDO class.
     */
    const DRIVER = 'pgsql';

    /**
     * The MySql class has an export method to create SQL files from the database. The PostgreSql
     * also have ways to create such files, executed with the 'pg_dump'.
     *
     * The execution of this program, however, seemed complex to be executed by the framework,
     * because it needs special user permissions. Because of this, it was decided to not allow the
     * usage of this method with the PostgreSql class.
     *
     * @param  mixed $filePath                      The path of the SQL file that will be exported.
     *
     * @param  mixed $specificTable                 Create a SQL file from a specific table.
     *
     * @return void
     */
    public function export(string $filePath, string $specificTable = ''): void
    {
        throw new Exception(
            sprintf(self::DATABASE_UNAVAILABLE_EXPORT_METHOD[1], __CLASS__),
            self::DATABASE_UNAVAILABLE_EXPORT_METHOD[0]
        );
    }

    /**
     * This method returns the last inserted id after insert data in a table.
     *
     * This method overwrites the method from the MySQl class because the way that the last ID is
     * returned by the PostgreSql is different from the MySql.
     *
     * @return int
     */
    protected function resolveLastId(): int
    {
        try {
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            return -1;
        }
    }
}
