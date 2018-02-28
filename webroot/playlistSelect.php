<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

// Extract data
$search = $_GET["v"];
$result = Playlist::getVideosByPlaylistId($search);


echo $twig->render('search.twig', [
    "result" => $result,
    "searchTerm" => $search,
    "user" => User::loggedIn()
]);