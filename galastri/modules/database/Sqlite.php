<?php

namespace galastri\modules\database;

use galastri\core\Debug;
use galastri\extensions\Exception;

/**
 * This class is related to Sqlite library.
 */
class Sqlite extends Database
{
    /**
     * The driver that is used by the PDO class.
     */
    const DRIVER = 'sqlite';

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
            $this->setLocation(GALASTRI_DATABASE[$config]['location'] ?? null);
        }
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
        $this->validateProperties('location');

        try {
            /**
             * Creates an instante of the PDO class in the $pdo property and establish the
             * connection. Any error here will throw an PDOException.
             */
            $this->pdo = new \PDO(
                static::DRIVER.':'.$this->location
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
        while ($found = $pdo->fetch(\PDO::FETCH_ASSOC)) {
            $result['data'][] = $found;
        }

        /**
         * PDO sqlite driver doesn't always return the rowCount. To make shure that the 'found'
         * property return the right data, it needs to do a double check
         *
         * 1. If the rowCount is bigger then zero, it means that there is data found.
         * 2. If not, another test is made: if the returned 'data' property isn't empty, this means
         *    that there is data found. If not, then it means that there is no data found.
         */
        $result['found'] = $pdo->rowCount() > 0 ? true : !empty($result['data'] ?? null);

        /**
         * The same code above applies to the 'affectedRows' property. The difference is that
         * instead of storing true or false, it stores the number of data found.
         */
        $result['affectedRows'] = $pdo->rowCount() > 0 ? $pdo->rowCount() : count($result['data'] ?? []);
        $result['lastId'] = $this->resolveLastId();

        return $result;
    }

    /**
     * This method returns the last inserted id after insert data in a table.
     *
     * @return int
     */
    public function resolveLastId(): int
    {
        try {
            $lastId = $this->pdo->lastInsertId();
            return $lastId == 0 ? -1 : $lastId;
        } catch (\PDOException $e) {
            return -1;
        }
    }
}
