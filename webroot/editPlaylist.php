<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


// Redirect the user away if they are already logged in
$user = User::loggedIn();
if (is_null($user) || !$user->isLecturer()) {
    header("Location: /");
    die();
}

$errors = [];
$playlistObject = Playlist::getPlaylistById($_GET["playlist"]);
$videos = $playlistObject->getVideosByPlaylistId($_GET["playlist"]);

if (!empty($_POST)) {
    if (isset($_POST['remove'])) {
        if ($_POST["video"]) {
            foreach ($_POST["video"] as $video) {
                $playlistObject->removeVideoFromPlaylist($video);
            }
        } else {
            $errors = "Please select a video to remove";
        }
    } elseif (isset($_POST['swap'])) {
        $videoSwap = $_POST["video"];
        if (count($videoSwap) === 2) {
            $playlistObject->changeVideoOrder($videoSwap[0], $videoSwap[1]);
        } else {
            $errors = "Please select two videos to swap";
        }

    }

    if (empty($errors)) {
        header("Location: /myPlaylists.php");
        die();
    }
}

echo $twig->render('editPlaylist.twig', [
    "videos" => $videos,
    "errors" => $errors,
    "playlist" => $playlistObject,
    "user" => User::loggedIn()

]);
