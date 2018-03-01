<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

$error = "";
$msg = "";
$user = User::loggedIn();




// Redirect the user away if they aren't logged in or doesn't have admin privileges
if (is_null($user) || !$user->isLecturer()) {
    header("Location: /");
    die();
}


echo $twig->render('myPlaylists.twig', [
    "user" => $user,

    "playlists" => Playlist::getPlaylistByUser($user->getId()),
    "videos"    => Video::getByUser($user->getId()),
]);
