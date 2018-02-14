<?php

class User {
    private $id;
    private $email;
    private $password;
    private $type;


    public function __construct($user=null, $pass=null, $type=null) {
        $this->email = $user;
        $this->password = $pass;
        $this->type = $type;
    }


}
