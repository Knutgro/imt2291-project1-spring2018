<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

$error = "";
$msg = "";

$user = User::loggedIn();


// Redirect the user away if they aren't logged in or doesn't have admin privileges
if (is_null($user) || !$user->is(User::ADMIN)) {
    header("Location: /");
    die();
}


// Handle user actions submitted to this page
// Accepted users will be marked as verified
// Rejected users will be left as unverified and changed to students
$action = array_key_exists("action", $_GET) ? $_GET["action"] : null;
if (in_array($action, ["verify", "reject"])) {

    $managedUser = User::getById( $_GET["id"] );
    $email = $managedUser->getEmail();
    $type = $managedUser->getType();

    if ($action == "verify") {
        $managedUser->setVerified(true);

        $_SESSION["flash"] = "The ${type} ${email} has been verified";

    } else if ($action == "reject") {
        $managedUser->setType("student");

        $_SESSION["flash"] = "The ${type} ${email} has been rejected and "
                           . "demoted to student";
    }

    if ($managedUser->update()) {

        header("Location: /admin.php");
        die();

    } else {
        $error = "An error occurred";
    }
}

// Role change
// This forces the user to be verified and will update their role, which differs
// slightly from the use-case above.
if ($action == "role" && $_GET["id"] !== $user->getId()) {
    $role = $_POST["role"];

    $managedUser = User::getById( $_GET["id"] );
    $managedUser->setVerified(true);
    $managedUser->setType($role);

    $_SESSION["flash"] = "The user ${email} has been verified and changed to "
                       . "the ${role} role";

    if ($managedUser->update()) {

        header("Location: /admin.php");
        die();

    } else {
        $error = "An error occurred";
    }
}


if (isset($_SESSION["flash"])) {
    $msg = $_SESSION["flash"];
    unset($_SESSION["flash"]);
}


echo $twig->render('admin.twig', [
    "user" => $user,
    "pendingUsers" => User::getPendingVerification(),
    "users" => User::getRegisteredUsers(),
    "msg" => $msg,
    "error" => $error,
]);
