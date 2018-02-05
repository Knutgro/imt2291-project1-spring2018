<?php

function getPDO($pdo_config) {
    global $twig;

    try {
        return new PDO($pdo_config["dsn"], $pdo_config["username"],
            $pdo_config["password"]);

    } catch (PDOException $exc) {
        error_message("Unable to connect to database", $exc);
    }
}
