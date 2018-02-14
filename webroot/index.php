<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

$user = new User("Philip", "hei");

echo $twig->render('index.html', ['user' => $user]);
