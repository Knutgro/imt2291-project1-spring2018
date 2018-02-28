<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

// Extract data
$search = $_GET["q"];
$result = Video::getBySearch($search);
$playlistResult = Playlist::searchPlaylistsByKeyword($search);


echo $twig->render('search.twig', [
    "result" => $result,
    "searchTerm" => $search,
    "playlistResult" => $playlistResult,
    "user" => User::loggedIn()
    ]);