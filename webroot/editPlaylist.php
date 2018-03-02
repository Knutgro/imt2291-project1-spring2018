<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


// Redirect the user away if they are already logged in
$user = User::loggedIn();
// Checking if user has permissions
if (is_null($user) || !$user->isLecturer()) {
    header("Location: .");
    die();
}

// Handle form submission
$errors = [];

// Getting playlist object from playlist id sent from form
$playlistObject = Playlist::getPlaylistById($_GET["playlist"]);
if (!$playlistObject) {
    http_404_page("Playlist");
}

if ($playlistObject->getUser() != $user->getId()) {
    header("Location: .");
    die();
}

// Getting video objects from playlist id.
$videos = $playlistObject->getVideosByPlaylistId($_GET["playlist"]);

// Performs video removal or video swap depending on which button is activated.
if (!empty($_POST)) {

    // Remove video from playlist
    if (isset($_POST['remove'])) {

        // Verify that the user selected a video at all
        if ($_POST["video"]) {

            // Loop over all videos and remove them
            foreach ($_POST["video"] as $video) {
                $playlistObject->removeVideoFromPlaylist($video);
            }

        } else {
            $errors = "Please select a video to remove";
        }

    // Swap two videos
    } elseif (isset($_POST['swap'])) {
        $videoSwap = $_POST["video"];

        // Check that there's only two videos selected, wwapping them if that is
        // the case
        if (count($videoSwap) === 2) {
            $playlistObject->changeVideoOrder($videoSwap[0], $videoSwap[1]);

        } else {
            $errors = "Please select two videos to swap";
        }

    }
    // Landing page if no errors
    if (empty($errors)) {
        header("Location: editPlaylist.php?playlist=" . $playlistObject->getId());
        die();
    }
}

echo $twig->render('editPlaylist.twig', [
    "videos" => $videos,
    "errors" => $errors,
    "playlist" => $playlistObject,
    "user" => User::loggedIn()

]);
