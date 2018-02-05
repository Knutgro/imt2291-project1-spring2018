<?php
// Define project globals
define('PROJECT_ROOT', dirname(__FILE__));

// Initialize Composer
require_once PROJECT_ROOT . "/vendor/autoload.php";

// Initialize Twig
$loader = new Twig_Loader_Filesystem(PROJECT_ROOT . "/templates");
$twig = new Twig_Environment($loader);

// Model autoloading
spl_autoload_register(function ($class_name) {
    $path = PROJECT_ROOT . "/models/" . $class_name . ".php";

    if (file_exists($path)) {
        require_once $path;
    }
});
