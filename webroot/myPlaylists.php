<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

$error = "";
$msg = "";
$user = User::loggedIn();




// Redirect the user away if they aren't logged in or doesn't have admin privileges
if (is_null($user) || !$user->is(User::ADMIN || $user->is(User::LECTURER))) {
    header("Location: /");
    die();
}

$playlists = Playlist::getPlaylistByUser($user->getId());

if (!empty($_POST)) {

    if (empty($_POST["video"])) {
        $errors[] = "You must select at least one video";
    }

    if (empty($errors)) {
        $_SESSION['playlist'] = $_POST["playlist"];
    }
}



echo $twig->render('myPlaylists.twig', [
    "playlists" => $playlists,
    "userId" => $user->getId(),
    "user" => User::loggedIn()

]);
