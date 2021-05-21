<?php

namespace galastri\modules\database;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\types\TypeString;

/**
 * This abstract class has the base methods shared by the database classes.
 */
abstract class Database implements \Language
{
    /**
     * Defines if the connection with the database was established.
     *
     * @var bool
     */
    protected bool $connected = false;

    /**
     * Stores the PDO instance.
     *
     * @var PDO
     */
    protected \PDO $pdo;

    /**
     * Stores an array of closures with database functions that will be executed right after the
     * method submit is called.
     *
     * @var array|null
     */
    protected ?array $chain = null;

    /**
     * Stores an array with data that will be used in the PDO method 'bind'. Needs to follow the
     * following format:
     *
     * - Key: The flag name used in the query.
     * - Value: The value that will replace the flag.
     *
     * @var array
     */
    protected array $bind = [];

    /**
     * Stores an array with the results of the database query.
     *
     * @var array
     */
    protected array $result = [];

    /**
     * Stores the host of database to establish connection.
     *
     * @var null|string
     */
    protected ?string $host = null;

    /**
     * Stores the port of database to establish connection.
     *
     * @var null|string
     */
    protected ?string $port = null;

    /**
     * Stores the database name that will be connected.
     *
     * @var null|string
     */
    protected ?string $database = null;

    /**
     * Stores the username of database to establish connection.
     *
     * @var null|string
     */
    protected ?string $username = null;

    /**
     * Stores the password of database to establish connection.
     *
     * @var null|string
     */
    protected ?string $password = null;

    /**
     * Stores options used by the PDO class.
     *
     * @var array|null
     */
    protected ?array $options = null;

    /**
     * Stores the location of the database file (used by the Sqlite class).
     *
     * @var null|string
     */
    protected ?string $location = null;

    /**
     * This method stores the host property.
     *
     * @param  null|string $value                   Host name or IP.
     *
     * @return self
     */
    public function setHost(?string $value): self
    {
        $this->host = $value;
        return $this;
    }

    /**
     * This method stores the port property.
     *
     * @param  null|string $value                   Number of the port.
     *
     * @return self
     */
    public function setPort(?string $value): self
    {
        $this->port = $value;
        return $this;
    }

    /**
     * This method stores the database property.
     *
     * @param  null|string $value                   Database name which will be connected.
     *
     * @return self
     */
    public function setDatabase(?string $value): self
    {
        $this->database = $value;
        return $this;
    }

    /**
     * This method stores the username property.
     *
     * @param  null|string $value                   The username to connect to the database.
     *
     * @return self
     */
    public function setUsername(?string $value): self
    {
        $this->username = $value;
        return $this;
    }

    /**
     * This method stores the password property.
     *
     * @param  null|string $value                   The password to connect to the database.
     *
     * @return self
     */
    public function setPassword(?string $value): self
    {
        $this->password = $value;
        return $this;
    }

    /**
     * This method stores the options property.
     *
     * @param  array|null $value                    An array with the PDO options.
     *
     * @return self
     */
    public function setOptions(?array $value): self
    {
        $this->options = $value;
        return $this;
    }

    /**
     * This method stores the location property.
     *
     * @param  array|null $value                    Path to the database file.
     *
     * @return self
     */
    public function setLocation(?string $location): self
    {
        $this->location = (new TypeString($location))->realPath()->get();
        return $this;
    }

    /**
     * Defines that the child database class needs to have a connect method.
     *
     * @return self
     */
    abstract public function connect(): self;


    /**
     * This method is a transaction type method. It defines the beginning of the transaction and, if
     * an error occurs while performing the SQL query, every transaction done until the error will
     * be reverted.
     *
     * @return self
     */
    public function begin(): self
    {
        Debug::setBacklog();

        $this->checkIfConnected();

        $this->pdo->beginTransaction();

        return $this;
    }

    /**
     * This method is a transaction type method. It defines that a transaction needs to be cancelled
     * and every transaction done until this execution will be reverted.
     *
     * @return self
     */
    public function cancel(): self
    {
        Debug::setBacklog();

        $this->checkIfConnected();

        $this->pdo->rollBack();

        return $this;

    }

    /**
     * This method is a transaction type method. It defines that every transaction done until this
     * execution can be concluded.
     *
     * @return self
     */
    public function commit(): self
    {
        Debug::setBacklog();

        $this->checkIfConnected();

        $this->pdo->commit();

        return $this;
    }

    /**
     * This method creates a link in the chain that prepares the SQL query using the PDO class,
     * formats and stores bind parameters, and executes the query and stores its result.
     *
     * @param  mixed $queryString                   The SQL query.
     *
     * @param  mixed $label                         A label to store the result.
     *
     * @return self
     */
    public function query(string $queryString, ?string $label = null): self
    {
        Debug::setBacklog();

        $this->checkIfConnected();

        $this->chain[] = function () use ($queryString, $label) {
            try {
                /**
                 * Executes the prepareQuery method, informing to that method the SQL query.
                 */
                $pdo = $this->prepareQuery($queryString);

                /**
                 * Filters each bind parameter, executing the bindParam method from the PDO class to
                 * store the flag and its value in the right type of the value.
                 */
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

                /**
                 * Execute the SQL query and store the value by executing the resolveFetch method.
                 * The result is stored, with its label, by the execSetResult method.
                 */
                if ($pdo->execute($this->bind)) {
                    $result = $this->resolveFetch($pdo);

                    $this->execSetResult($result, $label);
                }

                /**
                 * The bind property is resetted.
                 */
                $this->bind = [];

            /**
             * If PDO return an exception, an exception from the framework is thrown.
             */
            } catch (\PDOException $e) {
                throw new Exception(
                    sprintf(self::PDO_QUERY_EXECUTION_FAIL[1], $this->filterPdoExceptionMessage($e->getMessage())),
                    'PDO'.$e->getCode()
                );
            }
        };

        return $this;
    }

    /**
     * This method creates a link in the chain that stores a flag and a value used in the PDO
     * bindParam.
     *
     * @param  array|int|string $bind               When the $bind parameter is an array, the array
     *                                              needs to follow this format:
     *
     *                                              - Key label: The flag with the same name used in
     *                                                the SQL query.
     *                                              - Value: The value that will replace the flag.
     *
     *                                              When the $bind parameter is a string or an
     *                                              integer, this string needs to be the flag, with
     *                                              the same name used in the SQL query.
     *
     * @param  float|int|null|string $value         When the $bind parameter is a string or an
     *                                              integer, this parameter is the value that will
     *                                              replace the flag.
     *
     * @return void
     */
    public function bind(/*array|int|string*/ $bind, /*float|int|null|string*/ $value = null)
    {
        Debug::setBacklog();

        $this->checkIfConnected();

        $this->chain[] = function () use ($bind, $value) {

            /**
             * The $bind parameter type is checked.
             */
            switch (gettype($bind)) {
                /**
                 * If it is an array, each key will be stored as flag and each value as its value.
                 */
                case 'array':
                    foreach ($bind as $key => $value) {
                        $this->bind[$key] = $value;
                    }
                    break;

                /**
                 * If it is a string or an integer, the $bind parameter will be the flag and the
                 * $value parameter will be its value.
                 */
                case 'string':
                case 'integer':
                    $this->bind[$bind] = $value;
                    break;

                /**
                 * If the $bind parameter is from a different type, an exception will be thrown.
                 */
                default:
                    throw new Exception(
                        self::DATABASE_BIND_PARAMETER_TYPE[1],
                        self::DATABASE_BIND_PARAMETER_TYPE[0]
                    );
            }
        };

        return $this;
    }

    /**
     * This method executes the closures stored in the $chain property. The execution occurs in
     * reversed order, from the last added to the first.
     *
     * Each link executed is removed from the chain.
     *
     * @return self
     */
    public function submit(): self
    {
        $this->checkIfConnected();

        if (!empty($this->chain)) {
            foreach (array_reverse($this->chain) as $key => $function) {
                $function();
                unset($this->chain[$key]);
            }
        }

        return $this;
    }

    /**
     * This method gets the result from the $result property.
     *
     * @param  mixed $label                         When informed, returns a specific result from a
     *                                              stored query with the informed label. When null,
     *                                              gets the last result.
     *
     * @return void
     */
    public function get(?string $label = null)// : mixed
    {
        $this->checkIfConnected();

        $label = $label ?? 0;

        return $this->result[$label] ?? $this->result[array_key_last($this->result)];
    }

    /**
     * This method executes the prepare method, informing the querystring from the query method.
     *
     * @param  string $queryString                  The SQL query.
     *
     * @return PDO
     */
    protected function prepareQuery(string $queryString): \PDO
    {
        return $this->pdo->prepare(preg_replace('/[\t\n]+/u', ' ', trim($queryString)));
    }

    /**
     * This method stores the result of a query execution. When a label is defined in the query
     * method, it can be recovered because the result is stored in an array which uses the label as
     * its key name.
     *
     * @param  mixed $result                        The result from the query execution.
     *
     * @param  mixed $label                         The label that will store the result.
     *
     * @return void
     */
    protected function execSetResult($result, $label): void
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

    /**
     * This method filters the PDO exception message, to return it properly when an PDOException is
     * thrwn.
     *
     * @param  mixed $message                       The PDO exception message.
     *
     * @return string
     */
    protected function filterPdoExceptionMessage($message): string
    {
        $message = preg_replace('/[\t\n]+/u', ' ', trim($message));
        return str_replace(['%'], ['%%'], $message);
    }

    /**
     * This method is used to check if the connection is established. There are many methods that
     * requires the connection with a database to work. This method will throw an exception if there
     * is no connect established yet.
     *
     * @return void
     */
    protected function checkIfConnected()
    {
        if (!$this->connected) {
            throw new Exception(
                self::DATABASE_UNINITIALIZED_CLASS[1],
                self::DATABASE_UNINITIALIZED_CLASS[0]
            );
        }
    }

    /**
     * This method is used by the child classes to check if the properties that are needed to make
     * them work are setted. If not, an exception is thrown.
     *
     * @param  string $properties                   Names of the properties the is required to make
     *                                              the method work.
     *
     * @return void
     */
    protected function validateProperties(string ...$properties)
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
}
