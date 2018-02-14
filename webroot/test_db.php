<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

$dbh = getPDO($config["pdo"]);

$stmt = $dbh->query("SELECT * FROM user", PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "User");

foreach($stmt as $row) {
    var_dump($row);
}


var_dump(new User("test"));
