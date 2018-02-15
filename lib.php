<?php
declare(strict_types=1);

session_start();

// Define project globals
define('PROJECT_ROOT', dirname(__FILE__));

require_once PROJECT_ROOT . "/config.php";


// Initialize Composer
require_once PROJECT_ROOT . "/vendor/autoload.php";

// Initialize Twig
$loader = new Twig_Loader_Filesystem(PROJECT_ROOT . "/templates");
$twig = new Twig_Environment($loader);


// Error handling
function error_message($msg, $exc) {
    global $twig, $config;

    // Remove any previous output that might have been buffered. Ensures that
    // the error page doesn't contain anything other than an error message.
    ob_clean();

    if ($config["debug"] === true) {
        // Debugging is forced on, so let's just save the exception
        $debug_exc = $exc;

    } elseif (is_array($config["debug"])) {
        // Ensure that the current host is whitelisted
        if (in_array($_SERVER["REMOTE_ADDR"], $config["debug"])) {
            $debug_exc = $exc;
        }
    }

    echo $twig->render("error.html", [
        "msg" => $msg,
        "exc" => $debug_exc,
    ]);
    die();

}

set_exception_handler(function ($exc) {
    error_message("An internal server error has occurred", $exc);
});


// Initialize DB connection
require_once PROJECT_ROOT . "/db.php";

// Model autoloading
spl_autoload_register(function ($class_name) {
    $path = PROJECT_ROOT . "/models/" . $class_name . ".php";

    if (file_exists($path)) {
        require_once $path;
    }
});
