<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";
$user = User::loggedIn();
$playlistResult = Subscription::getSubscriptionsByUserId($user->getid());

echo $twig->render('index.twig', ['user' => $user, 'playlistResult' => $playlistResult]);
