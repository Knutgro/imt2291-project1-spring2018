<?php

require_once dirname(dirname(__FILE__)) . "/lib.php";

// Extract data
$watch = $_GET["v"];
$result = Video::getById($watch);

echo $twig->render('watch.twig', [
    "result"=> $result,
]);