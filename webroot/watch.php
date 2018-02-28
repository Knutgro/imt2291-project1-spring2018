<?php

require_once dirname(dirname(__FILE__)) . "/lib.php";

$user = User::loggedIn();

// Extract data
$videoId = $_GET["v"];
$video = Video::getById($videoId);

if (!$video) {
    http_404_page("Video");
}


$error = "";
if ($user && !empty($_POST)) {

    // Comments
    if ( $_POST["action"] == "comment" && !empty(trim($_POST["comment"])) )
    {
        $comment = new Comment( $user->getId(), $video->getId(), $_POST["comment"] );
        if ( $comment->insert() !== false) {
            header("Location: ${_SERVER["REQUEST_URI"]}");
            die();
        } else {
            $error = "Unable to post comment";
        }
    }
}


echo $twig->render('watch.twig', [
    "video"    => $video,
    "comments" => Comment::getCommentsByVideoId($video->getId()),
    "user"     => $user,
    "error"    => $error,
]);
