<?php

declare(strict_types=1);

require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;


final class UserTest extends TestCase
{

    public function testPasswordSetVerify()
    {
        $password = "testPasswordForTesting";
        $user = new User();

        // Set our password
        $user->setPassword($password);

        // Verify that our password works
        $this->assertTrue($user->verifyPassword($password));

        // Verify that an invalid password doesn't work
        $password .= "ThisIsNowInvalid";
        $this->assertFalse($user->verifyPassword($password));
    }

    public function testGetEmail()
    {
        $user = new User("my@email.no");
        $this->assertEquals($user->getEmail(), "my@email.no");
    }

    public function testGetLoggedInUser()
    {
        unset($_SESSION["user_id"]);

        $this->assertNull(User::loggedIn());

        $_SESSION["user_id"] = 1;  // The admin user should have ID 1 in the DB

        $this->assertInstanceOf(User::class, User::loggedIn());
    }

    public function testGetUserById()
    {
        $user = User::getById(1);  // The admin user should have ID 1 in the DB

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getId(), 1);

        $this->assertNull(User::getById(-1));
    }

    public function testGetUserByEmail()
    {
        $email = "video-admin@ntnu.no";
        $user = User::getByEmail($email);  // The admin user should have ID 1 in the DB

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getEmail(), $email);

        $this->assertNull(User::getByEmail("invalid"));
    }

    public function testDoLogin()
    {
        // Invalid email
        $this->assertFalse(User::doLogin("nonexistant", "lol"));

        // Invalid password
        $this->assertFalse(User::doLogin("video-admin@ntnu.no", "lol"));

        // Valid everything
        $this->assertTrue(User::doLogin("video-admin@ntnu.no",
            "do not use in production"));
    }
}
