<?php

namespace galastri\modules\database;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\types\TypeString;

class MySql extends Database
{
    const DRIVER = 'mysql';

    protected string $port = '';

    public function __construct(?string $config = null)
    {
        Debug::setBacklog();

        if ($config) {
            $this->validateConfig($config, 'host', 'database', 'username', 'password');

            $this->setHost(GALASTRI_DATABASE[$config]['host']);
            $this->setPort(GALASTRI_DATABASE[$config]['port'] ?? null);
            $this->setDatabase(GALASTRI_DATABASE[$config]['database']);
            $this->setUsername(GALASTRI_DATABASE[$config]['username']);
            $this->setPassword(GALASTRI_DATABASE[$config]['password']);
            $this->setOptions(GALASTRI_DATABASE[$config]['options'] ?? []);
        }
    }

    public function setPort(?string $port): self
    {
        $this->port = empty($port) ? '' : 'port='.$port;
        return $this;
    }

    public function connect(): self
    {
        Debug::setBacklog();

        $this->validateProperties('host', 'port', 'database', 'username', 'password');

        try {
            $this->pdo = new \PDO(
                static::DRIVER.':host='.$this->host.';'.$this->port.' dbname='.$this->database,
                $this->username,
                $this->password,
                $this->options
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

    public function export(string $filePath, string $specificTable = '')
    {
        $this->checkIfInitialized();

        $this->validateProperties('host', 'database', 'username', 'password');

        $directory = new TypeString($filePath);
        $directory->concat('/..')->createDirectory(0777);

        $filePath = (new TypeString($filePath))->realPath()->get();

        $mySqlDump = 'mysqldump --user='.$this->username.' --password='.$this->password.' --host='.$this->host.' '.$this->database.' '.$specificTable.' --result-file="'.$filePath.'" 2>&1';

        exec($mySqlDump, $result);

        return [
            'filePath' => $filePath,
            'directory' => $directory->get(),
            'result' => $result,
        ];
    }

    protected function resolveLastId(): int
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
        if ($pdo->rowCount() > 0) {
            while ($found = $pdo->fetch(\PDO::FETCH_ASSOC)) {
                $result['data'][] = $found;
            }

            $result['found'] = true;
        }

        $result['affectedRows'] = $pdo->rowCount();
        $result['lastId'] = $this->resolveLastId();

        return $result;
    }
}
