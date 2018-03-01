<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

// Extract data
$search = $_GET["v"];

//Use the ID to find the playlist object, and videos that belong to the playlist.
$currentPlaylist = Playlist::getPlaylistById($search);
$result = Playlist::getVideosByPlaylistId($search);
$user = User::loggedIn();



if ($user && $currentPlaylist)  {
    $sub = Subscription::getSubscription($user->getId(),
        $currentPlaylist->getId());
}



echo $twig->render('playlistSelect.twig', [
    "result" => $result,
    "playlistSearch" => $search,
    "currentPlaylist" => $currentPlaylist,
    "sub" => $sub,
    "user" => $user
]);
