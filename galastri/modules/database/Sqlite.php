<?php

namespace galastri\modules\database;

use galastri\core\Debug;
use galastri\extensions\Exception;

class Sqlite extends Database
{
    const DRIVER = 'sqlite';

    protected string $location;

    public function __construct(?string $config = null)
    {
        Debug::setBacklog();

        if ($config) {
            $this->validateConfig($config, 'location');

            $this->setLocation(GALASTRI_DATABASE[$config]['location']);
        }
    }

    public function connect(): self
    {
        Debug::setBacklog();

        $this->validateProperties('location');

        try {
            $this->pdo = new \PDO(
                static::DRIVER.':'.$this->location
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->initialized = true;

            return $this;
        } catch (\PDOException $e) {
            throw new Exception(
                sprintf(self::PDO_CONNECTION_FAIL[1], $this->filterPdoExceptionMessage($e->getMessage())),
                'PDO'.$e->getCode()
            );
        }
    }

    public function resolveLastId(): int
    {
        try {
            $lastId = $this->pdo->lastInsertId();
            return $lastId == 0 ? -1 : $lastId;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    protected function resolveFetch(\PDOStatement $pdo): array
    {
        while ($found = $pdo->fetch(\PDO::FETCH_ASSOC)) {
            $result['data'][] = $found;
        }

        $result['found'] = $pdo->rowCount() > 0 ? true : !empty($result['data'] ?? null);

        $result['affectedRows'] = $pdo->rowCount() > 0 ? $pdo->rowCount() : count($result['data'] ?? []);
        $result['lastId'] = $this->resolveLastId();

        return $result;
    }
}
