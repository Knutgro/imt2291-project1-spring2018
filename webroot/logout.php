<?php
require_once dirname(dirname(__FILE__)) . "/lib.php";


unset($_SESSION["user_id"]);
header("Location: /");

