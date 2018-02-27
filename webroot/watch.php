<?php

require_once dirname(dirname(__FILE__)) . "/lib.php";

// Extract data


echo $twig->render('watch.twig', [
    "result"=> $result,
]);