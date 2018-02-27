<?php

require_once dirname(dirname(__FILE__)) . "/lib.php";

// Extract data
$videoId = $_GET["v"];
$video = Video::getById($videoId);

if (!$video) {
    http_404_page("Video");
}


echo $twig->render('watch.twig', [
    "video"    => $video,
    "comments" => Comment::getCommentsByVideoId($video->getId()),
    "user"     => User::loggedIn(),
]);
