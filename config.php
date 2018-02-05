<?php
$config = array(

    /**
     * Configure exception handling for the website
     *
     * false: Disables exception printing
     * true: Enables exception printing for all clients
     * array(): Enables exception printing only for the listed clients.
     *
     * Example value: array("127.0.0.1")
     */
    "debug" => array("127.0.0.1"),

    /**
     * Database configuration
     */
    "pdo" => array(
        "dsn" => "mysql:host=127.0.0.1;dbname=test",
        "username" => "root",
        "password" => "",
    ),
);
