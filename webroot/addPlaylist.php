<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


// Redirect the user away if they are already logged in
$user = User::getById(1);
if (is_null($user) || !$user->isLecturer()) {
    header("Location: /");
    die();
}

$result = Video::getByUser(1);

// Handle form submission

if (!empty($_POST)) {
    $title = $_POST["title"];
    $subject = $_POST["subject"];
    $topic = $_POST["topic"];
    $description = $_POST["description"];

    // Insert into DB
    $playlist = new Playlist(1, $title, $description, $subject, $topic);
    // Verify and either redirect or error

    $id = $playlist->insertPlaylist();
    foreach ($_POST["video"] as $video) {
        $playlist->insertVideo($video, $id);
    }
}


echo $twig->render('addPlaylist.twig', [
    "result" => $result,
    "user" => User::loggedIn()

]);
