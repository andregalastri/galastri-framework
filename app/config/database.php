<?php

/**
 * This is the database configuration file. This file stores an array with database connection data.
 *
 * The data here needs to follow this format:
 *
 *      <label> => [
 *           <property1> => <value1>,
 *           <property2> => <value2>,
 *           ...
 *      ];
 *
 * <label>          int|string                      Label that identifies the configuration. It is
 *                                                  used in the database class contructors to import
 *                                                  the configuration right in the properties of the
 *                                                  database instance.
 *
 * <property>       string                          Identifies the property that will be configured.
 *                                                  Each database driver can have different
 *                                                  properties.
 *
 * <value>          array|string                    The value of the property. The only property
 *                                                  that is an array is the 'option' property, all
 *                                                  the others are strings.
 */

return [
    /**
     * These are the most common properties for MySql connection. The required ones are the 'host',
     * 'database', 'username' and 'password' properties.
     */
    'mysql' => [
        'host'          => '',
        'port'          => '',
        'database'      => '',
        'username'      => '',
        'password'      => '',
        'options'       => [],
    ],

    /**
     * These are the most common properties for PostgreSql connection. The required ones are the
     * 'host', 'database', 'username' and 'password' properties.
     */
    'postgre' => [
        'host'          => '',
        'port'          => '',
        'database'      => '',
        'username'      => '',
        'password'      => '',
        'options'       => [],
    ],

    /**
     * These are the most common properties for Sqlite connection. The required one is the
     * 'location' property.
     */
    'sqlite' => [
        'location'      => '',
    ],
];
