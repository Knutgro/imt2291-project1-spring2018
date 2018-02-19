<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


// Redirect the user away if they are already logged in
if (!is_null(User::loggedIn())) {
    header("Location: /");
    die();
}


// Handle register request
if (!empty($_POST)) {

    // Extract data
    $email = $_POST["email"];
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    if ($_POST["is_lecturer"] == "on") {
        $type = "lecturer";
    } else {
        $type = "student";
    }

    $errors = User::validate($email, $password, $password2, $type);
    if (empty($errors)) {
        // Initialize the user
        $user = new User($email, $password, $type);

        if ($user->insert() !== false) {
            header("Location: /");
            die();
        } else {
            $error = "Wrong email or password";
        }
    }

}


echo $twig->render('register.twig', ["errors" => $errors]);
