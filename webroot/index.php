<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


echo $twig->render('index.twig', ['user' => User::loggedIn()]);
