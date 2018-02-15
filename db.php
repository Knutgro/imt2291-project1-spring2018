<?php

/** 
 * Database connection handling
 */
class DB
{
    const FETCH_OBJECT = PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE;

    static private $dbh;

    /** 
     * Get a database handle
     *
     * Can be used for running queries against the DB server.
     *
     * @return PDO A PDO database handle instance.
     */
    static function getPDO() {
        global $config;
        $pdo_config = $config["pdo"];

        if (is_null(self::$dbh)) {
            try {
                self::$dbh = new PDO($pdo_config["dsn"], $pdo_config["username"],
                    $pdo_config["password"]);

            } catch (PDOException $exc) {
                error_message("Unable to connect to database", $exc);
            }
        }

        return self::$dbh;
    }
}
