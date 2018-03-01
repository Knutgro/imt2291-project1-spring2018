<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


// Redirect the user away if they are already logged in
$user = User::loggedIn();
if (is_null($user) || !$user->isLecturer()) {
    header("Location:; /");
    die();
}

$userVideos = Video::getByUser($user->getId());

// Handle form submission
$errors = [];
if (!empty($_POST)) {
    $title = $_POST["title"];
    $subject = $_POST["subject"];
    $topic = $_POST["topic"];
    $description = $_POST["description"];

    if (empty($title)) {
        $errors[] = "Title can't be empty";
    }

    if (empty($subject)) {
        $errors[] = "Subject can't be empty";
    }

    if (empty($topic)) {
        $errors[] = "Topic can't be empty";
    }

    if (empty($description)) {
        $errors[] = "Description can't be empty";
    }

    if (empty($_POST["video"])) {
        $errors[] = "You must select at least one video";
    }

    // Insert into DB
    $playlist = new Playlist(1, $title, $description, $subject, $topic);

    // Verify and either redirect or error
    if (empty($errors)) {

        $id = $playlist->insertPlaylist();
        foreach ($_POST["video"] as $video) {
            if ($playlist->insertVideo($video, $id) === false) {
                $errors[] = "Unable to add video " . $video->getTitle() . " to the playlist.";
            }
        }

    }

    if (empty($errors)) {
        header("Location: /playlistSelect.php?v=${id}");
        die();
    }
}


echo $twig->render('addPlaylist.twig', [
    "videos" => $userVideos,
    "errors" => $errors,
    "user" => User::loggedIn()
]);
