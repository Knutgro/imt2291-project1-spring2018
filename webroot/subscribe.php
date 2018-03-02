<?php

require_once dirname(dirname(__FILE__)) . "/lib.php";

$user = User::loggedIn();

// Redirect the user away if they aren't logged in
if (is_null($user)) {
    header("Location: .");
    die();
}



// Extract data
$playlistId = $_GET["p"];
$playlist = Playlist::getPlaylistById($playlistId);

if (!$playlist) {
    http_404_page("Playlist");
}


$sub = Subscription::getSubscription($user->getId(), $playlist->getId());
if ( $sub ) {
    $sub->delete();
} else {
    $sub = new Subscription($user->getId(), $playlist->getId());
    $sub->insert();
}


header("Location: ${_SERVER["HTTP_REFERER"]}");
die();
