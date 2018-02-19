<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

$dbh = DB::getPDO();

$stmt = $dbh->query("SELECT * FROM user", PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "User");

echo "<h2>Manual query</h2>";
foreach($stmt as $row) {
    var_dump($row);
    echo "<br /><br />";
}

echo "<h2>User::getById()</h2>";
var_dump(User::getById(1));
