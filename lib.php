<?php
// Define project globals
define('PROJECT_ROOT', dirname(__FILE__));

// Initialize Composer
require_once PROJECT_ROOT . "/vendor/autoload.php";

// Initialize Twig
$loader = new Twig_Loader_Filesystem(PROJECT_ROOT . "/templates");
$twig = new Twig_Environment($loader);