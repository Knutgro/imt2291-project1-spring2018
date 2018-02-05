<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";

echo $twig->render('index.html', array('name' => 'Fabien'));