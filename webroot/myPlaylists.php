<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

$error = "";
$msg = "";
$user = User::getById(1);
//$user = User::loggedIn();


// Redirect the user away if they aren't logged in or doesn't have admin privileges
if (is_null($user) || !$user->is(User::ADMIN || $user->is(User::LECTURER))) {
    header("Location: /");
    die();
}

//Stores the playlists of the given user.
$playlists = Playlist::getPlaylistByUser($user->getId());




echo $twig->render('myPlaylists.twig', [
    "playlists" => $playlists,
    "userId" => $user->getId(),
    "user" => User::loggedIn()

]);
