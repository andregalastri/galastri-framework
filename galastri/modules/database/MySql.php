<?php

namespace galastri\modules\database;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\types\TypeString;

/**
 * This class is related to MySql SGDB.
 */
class MySql extends Database
{
    /**
     * The driver that is used by the PDO class.
     */
    const DRIVER = 'mysql';

    /**
     * Stores the port of database to establish connection.
     *
     * @var null|string
     */
    protected string $port = '';

    /**
     * The constructor of the class can configure the connection attributes using a configuration
     * from the database configuration file. The configuration is a multidimensional array, which
     * the key is a label and its value stores another array with the parameters that will be used
     * here. When the label is informed here, all the configurations are automatically imported to
     * the properties of the instance.
     *
     * @param  mixed $config                        Name of the key used in the database config file
     *                                              that contains the parameters to configure the
     *                                              instance.
     *
     * @return void
     */
    public function __construct(?string $config = null)
    {
        Debug::setBacklog();

        if ($config) {
            $this->setHost(GALASTRI_DATABASE[$config]['host'] ?? null);
            $this->setPort(GALASTRI_DATABASE[$config]['port'] ?? null);
            $this->setDatabase(GALASTRI_DATABASE[$config]['database'] ?? null);
            $this->setUsername(GALASTRI_DATABASE[$config]['username'] ?? null);
            $this->setPassword(GALASTRI_DATABASE[$config]['password'] ?? null);
            $this->setOptions(GALASTRI_DATABASE[$config]['options'] ?? []);
        }
    }

    /**
     * This method stores the port property. It overrides the inherited setPort method from the
     * Database class because the PDO connection needs that the port is declared in the string
     * statment with the format 'port=<num>' (when a port is defined), which isn't the default
     * behavior of the inherited method.
     *
     * @param  null|string $value                   Number of the port.
     *
     * @return self
     */
    public function setPort(?string $port): self
    {
        $this->port = empty($port) ? '' : 'port='.$port;
        return $this;
    }

    /**
     * This method executes the connection with the database using the values configured in its
     * properties.
     *
     * The properties that are required to do the connection are checked before the execution.
     * If there are required properties that are not defined, an exception is thrown.
     *
     * @return self
     */
    public function connect(): self
    {
        Debug::setBacklog();

        /**
         * Checks if the required properties are defined
         */
        $this->validateProperties('host', 'port', 'database', 'username', 'password');

        try {
            /**
             * Creates an instante of the PDO class in the $pdo property and establish the
             * connection. Any error here will throw an PDOException.
             */
            $this->pdo = new \PDO(
                static::DRIVER.':host='.$this->host.';'.$this->port.' dbname='.$this->database,
                $this->username,
                $this->password,
                $this->options
            );

            /**
             * Definition of PDO attributes that sets the error mode, to throw exceptions, and
             * disables the PDO prepare emulation, to return data types as they are stored in the
             * database instead of return everything as string.
             */
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            $this->connected = true;
            return $this;

        /**
         * When an error occurs, the message from the PDOException is filtered and returned.
         */
        } catch (\PDOException $e) {
            throw new Exception(
                sprintf(self::PDO_CONNECTION_FAIL[1], $this->filterPdoExceptionMessage($e->getMessage())),
                'PDO'.$e->getCode()
            );
        }
    }

    /**
     * This method exports a database data in a file. It is useful to create backups. By default, it
     * exports the entire database, but a specific table can be set to be exported.
     *
     * @param  mixed $filePath                      Path where the file will be stored (including
     *                                              the file name and its extension, which normally
     *                                              is the .sql extension).
     *
     * @param  mixed $specificTable                 When declared, exports only the specific table
     *                                              to the file.
     *
     * @return array
     */
    public function export(string $filePath, string $specificTable = ''): array
    {
        $this->checkIfConnected();

        /**
         * Checks if the required properties are defined
         */
        $this->validateProperties('host', 'database', 'username', 'password');

        /**
         * Creates every directory if the path doesn't have them.
         */
        $directory = new TypeString($filePath);
        $directory->concat('/..')->createDirectory(0777);

        /**
         * Gets the file absolute path.
         */
        $filePath = (new TypeString($filePath))->realPath()->get();

        /**
         * Execute the mysqldump that gets database data and stores it in the given file.
         */
        $mySqlDump = 'mysqldump --user='.$this->username.' --password='.$this->password.' --host='.$this->host.' '.$this->database.' '.$specificTable.' --result-file="'.$filePath.'" 2>&1';
        exec($mySqlDump, $result);

        /**
         * Return an array with the returning value of the the execution.
         */
        return [
            'filePath' => $filePath,
            'directory' => $directory->get(),
            'result' => $result,
        ];
    }

    /**
     * This method gets the result of the query and stores it temporarily in an array that is
     * returned to the query method to be stored.
     *
     * @param  PDOStatement $pdo                    The PDO statement with the result of the
     *                                              execution of the query.
     *
     * @return array
     */
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

    /**
     * This method returns the last inserted id after insert data in a table.
     *
     * @return int
     */
    protected function resolveLastId(): int
    {
        try {
            $lastId = $this->pdo->lastInsertId();
            return $lastId == 0 ? -1 : $lastId;
        } catch (\PDOException $e) {
            return -1;
        }
    }
}
