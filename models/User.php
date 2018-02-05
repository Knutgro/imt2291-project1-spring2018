<?php

class User {
    public $username;
    public $password;

    public function __construct($user, $pass) {
        $this->username = $user;
        $this->password = $pass;
    }
}
