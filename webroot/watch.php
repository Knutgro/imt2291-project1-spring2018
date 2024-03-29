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

    // Add to playlist
    if ( $_POST["action"] == "playlist" )
    {
        $playlist = Playlist::getPlaylistById( $_POST["playlist"] );

        if (!$playlist) {
            $error = "Unknown playlist";

        } else if ($video->getUser() != $user->getId()) {
            $error = "You can only add your own videos to playlists";

        } else {
            $result = $playlist->insertVideo($video->getId());

            if ($result) {
                header("Location: playlistSelect.php?v=" . $playlist->getId());
                die();
            } else {
                $error = "This video is already in that playlist";
            }
        }
    }
}


$playlist = null;
$nextVideo = null;
$playIndex = isset($_GET["i"]) ? $_GET["i"] : 0;
$isSubscribed = false;
if (!empty($_GET["p"])) {
    $playlist = Playlist::getPlaylistById($_GET["p"]);

    if ($playlist) {
        $videos = $playlist->getVideos();
        if ( count($videos) > $playIndex ) {
            $nextVideo = $videos[ $playIndex ]; // playIndex is one-indexed, so no need to increment
        }
    }
}

$sub = null;
if ($user && $playlist)  {
    $sub = Subscription::getSubscription($user->getId(),
        $playlist->getId());
}


echo $twig->render('watch.twig', [
    "user"     => $user,

    "video"    => $video,
    "comments" => Comment::getCommentsByVideoId($video->getId()),
    "rating"   => Rating::getTotalRating($video->getId()),
    "myrating" => $user ? Rating::getUserRating($user->getId(), $video->getId()) : null,

    "playlist" => $playlist,
    "videoInd" => $playIndex,
    "upnext"   => $nextVideo,
    "sub"      => $sub,
    "myPlaylists" => $user ? Playlist::getPlaylistByUser($user->getId()) : null,

    "error"    => $error,
]);
