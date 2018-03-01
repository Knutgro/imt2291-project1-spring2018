<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

// Extract data
$search = $_GET["v"];
$currentPlaylist = Playlist::getPlaylistById($search);
$result = Playlist::getVideosByPlaylistId($search);


echo $twig->render('playlistSelect.twig', [
    "result" => $result,
    "playlistSearch" => $search,
    "currentPlaylist" => $currentPlaylist,
    "user" => User::loggedIn()
]);