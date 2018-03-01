<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


// Redirect the user away if they are already logged in
if (!is_null(User::loggedIn())) {
    header("Location: .");
    die();
}


$error = "";
// Handle log in request
if (!empty($_POST)) {

    // Extract data
    $email = $_POST["email"];
    $password = $_POST["password"];

    if (User::doLogin($email, $password)) {
        header("Location: .");
        die();
    } else {
        $error = "Wrong email or password";
    }
}


echo $twig->render('login.twig', ["error" => $error]);
