<?php

namespace galastri\modules\database;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\types\TypeString;

abstract class Database implements \Language
{
    protected bool $initialized = false;

    protected \PDO $pdo;

    protected ?array $chain = null;
    protected array $bind = [];

    protected array $result = [];

    protected string $host;
    protected string $port;
    protected string $database;
    protected string $username;
    protected string $password;
    protected ?array $options;

    public function setHost(string $value)
    {
        $this->host = $value;
        return $this;
    }

    public function setPort(string $value)
    {
        $this->port = $value;
        return $this;
    }

    public function setDatabase(string $value)
    {
        $this->database = $value;
        return $this;
    }

    public function setUsername(string $value)
    {
        $this->username = $value;
        return $this;
    }

    public function setPassword(string $value)
    {
        $this->password = $value;
        return $this;
    }

    public function setOptions(array $value)
    {
        $this->options = $value;
        return $this;
    }

    public function setLocation(?string $location): self
    {
        $this->location = (new TypeString($location))->realPath()->get();
        return $this;
    }

    abstract public function connect(): self;

    public function begin()
    {
        Debug::setBacklog();

        $this->checkIfInitialized();

        $this->pdo->beginTransaction();

        return $this;
    }

    public function cancel()
    {
        Debug::setBacklog();

        $this->checkIfInitialized();

        $this->pdo->rollBack();

        return $this;

    }

    public function commit()
    {
        Debug::setBacklog();

        $this->checkIfInitialized();

        $this->pdo->commit();

        return $this;
    }

    public function query(string $queryString, ?string $label = null)
    {
        Debug::setBacklog();

        $this->checkIfInitialized();

        $this->chain[] = function () use ($queryString, $label) {
            try {
                $pdo = $this->prepareQuery($queryString);

                foreach ($this->bind as $key => &$value) {
                    switch (gettype($value)) {
                        case 'NULL':
                            $pdo->bindParam($key, $value, \PDO::PARAM_NULL);
                        break;

                        case 'integer':
                            $pdo->bindParam($key, $value, \PDO::PARAM_INT);
                        break;

                        case 'boolean':
                            $pdo->bindParam($key, $value, \PDO::PARAM_BOOL);
                        break;

                        default:
                            if (empty($value) and $value !== 0 and $value !== 0.0 and $value !== "0") {
                                $pdo->bindParam($key, $value, \PDO::PARAM_NULL);
                                $value = null;
                            } else {
                                $pdo->bindParam($key, $value);
                            }
                        break;
                    }
                }
                unset($value);

                if ($pdo->execute($this->bind)) {
                    $result = $this->resolveFetch($pdo);

                    $this->execSetResult($result, $label);
                }


                $this->bind = [];
            } catch (\PDOException $e) {
                throw new Exception(
                    sprintf(self::PDO_QUERY_EXECUTION_FAIL[1], $this->filterPdoExceptionMessage($e->getMessage())),
                    'PDO'.$e->getCode()
                );
            }
        };

        return $this;
    }

    public function bind(/*array|int|string*/ $bind, /*float|int|null|string*/ $value = null)
    {
        Debug::setBacklog();

        $this->checkIfInitialized();

        $this->chain[] = function () use ($bind, $value) {
            switch (gettype($bind)) {
                case 'array':
                    foreach ($bind as $key => $value) {
                        $this->bind[$key] = $value;
                    }
                    break;

                case 'string':
                case 'integer':
                    $this->bind[$bind] = $value;
                    break;

                default:
                    throw new Exception(
                        self::DATABASE_BIND_PARAMETER_TYPE[1],
                        self::DATABASE_BIND_PARAMETER_TYPE[0]
                    );
            }
        };

        return $this;
    }

    public function submit(): self
    {
        $this->checkIfInitialized();

        if (!empty($this->chain)) {
            foreach (array_reverse($this->chain) as $key => $function) {
                $function();
                unset($this->chain[$key]);
            }
        }

        return $this;
    }

    public function get(?string $label = null)// : mixed
    {
        $this->checkIfInitialized();

        $label = $label ?? 0;

        return $this->result[$label] ?? $this->result[array_key_last($this->result)];
    }

    protected function prepareQuery(string $queryString): object
    {
        return $this->pdo->prepare(preg_replace('/[\t\n]+/u', ' ', trim($queryString)));
    }

    protected function execSetResult($result, $label)
    {
        $label = $label ?? 0;

        $this->result[$label] = [
            'label' => $label,
            'data' => $result['data'] ?? [],
            'found' => (bool)($result['found'] ?? false),
            'affectedRows' => (int)($result['affectedRows'] ?? 0),
            'lastId' => (int)$result['lastId'] ?? -1,
        ];
    }

    protected function filterPdoExceptionMessage($message): string
    {
        $message = preg_replace('/[\t\n]+/u', ' ', trim($message));
        return str_replace(['%'], ['%%'], $message);
    }

    protected function checkIfInitialized()
    {
        if (!$this->initialized) {
            throw new Exception(
                self::DATABASE_UNINITIALIZED_CLASS[1],
                self::DATABASE_UNINITIALIZED_CLASS[0]
            );
        }
    }

    protected function validateProperties(...$properties)
    {
        foreach ($properties as $property) {
            if (!isset($this->$property)) {
                throw new Exception(
                    sprintf(self::DATABASE_CONNECTION_FAIL_UNDEFINED_PROPERTY[1], $property),
                    self::DATABASE_CONNECTION_FAIL_UNDEFINED_PROPERTY[0]
                );
            }
        }
    }

    protected function validateConfig($config, ...$properties)
    {
        foreach ($properties as $property) {
            if (!isset(GALASTRI_DATABASE[$config][$property])) {
                throw new Exception(
                    sprintf(self::DATABASE_CONNECTION_FAIL_UNDEFINED_PROPERTY[1], $property),
                    self::DATABASE_CONNECTION_FAIL_UNDEFINED_PROPERTY[0]
                );
            }
        }
    }
}
