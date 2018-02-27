<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


// Redirect the user away if they are already logged in
$user = User::loggedIn();
if (is_null($user) || !$user->isLecturer()) {
    header("Location: /");
    die();
}

$errors = [];
$data = [];


// Function for simplifying file moving
function move_file( $field )
{

    $hash = hash_file("sha1", $_FILES[$field]["tmp_name"]);
    $type = explode("/", mime_content_type($_FILES[$field]["tmp_name"]))[1];

    $target = "/assets/${field}/${hash}.${type}";
    $dest = dirname( __FILE__ ) . $target;

    if (move_uploaded_file($_FILES[$field]["tmp_name"], $dest)) {
        return $target;
    }
}


// Handle form submission
if (!empty($_POST)) {
    $data = [
        "title"       => $_POST["title"],
        "subject"     => $_POST["subject"],
        "topic"       => $_POST["topic"],
        "description" => $_POST["description"],
    ];

    // Verify text fields
    foreach ($data as $key => $value) {
        if (empty($value)) {
            $errors[] = ucwords( $key ) . " can not be empty";
        }
    }


    // Verify image
    $thumb_mime = mime_content_type($_FILES["thumbnail"]["tmp_name"]);
    if (strpos($thumb_mime, "image/") !== 0) {
        $errors[] = "Please provide an image as thumbnail";
    }

    // Verify video
    $video_mime = mime_content_type($_FILES["video"]["tmp_name"]);
    if (strpos($video_mime, "video/") !== 0) {
        $errors[] = "Please provide a video file as the video";
    }

    if (empty($errors)) {

        // Move images and get paths
        $videoFile = move_file("video");
        $thumbFile = move_file("thumbnail");

        // Insert into DB
        $video = new Video($user, $data, $videoFile, $thumbFile);
        // Verify and either redirect or error
        if ($video->insert() !== false) {
            header("Location: /watch.php?v=" . $video->getId());
            die();
        } else {
            $errors[] = "Something failed";
        }
    }
}


echo $twig->render('upload.twig', [
    "errors" => $errors,
    "user" => $user,
    "data" => $data,
]);
