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

    // Comment
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

    // Rating
    if ( $_POST["action"] == "rate" && array_key_exists("rating", $_POST)
        && $_POST["rating"] >= 0 && $_POST["rating"] <= 5 )
    {
        $rating = new Rating( $user->getId(), $video->getId(), $_POST["rating"] );
        if ( $rating->insert() !== false) {
            header("Location: ${_SERVER["REQUEST_URI"]}");
            die();
        } else {
            $error = "Unable to submit rating";
        }
    }
}


echo $twig->render('watch.twig', [
    "user"     => $user,

    "video"    => $video,
    "comments" => Comment::getCommentsByVideoId($video->getId()),
    "rating"   => Rating::getTotalRating($video->getId()),
    "myrating" => Rating::getUserRating($user->getId(), $video->getId()),

    "error"    => $error,
]);
