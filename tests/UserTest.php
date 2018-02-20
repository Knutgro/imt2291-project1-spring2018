<?php

declare(strict_types=1);

require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;


final class UserTest extends TestCase
{
    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();
    }

    public function tearDown()
    {
        $dbh = DB::getPDO();
        $dbh->rollBack();
    }

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
        $this->assertEquals("my@email.no", $user->getEmail());
    }

    public function testType()
    {
        $user = new User(null, null, "student");
        $this->assertEquals("student", $user->getType());

        $user->setType("wrong");
        $this->assertEquals("student", $user->getType());

        $user->setType("admin");
        $this->assertEquals("admin", $user->getType());

        $user->setType("lecturer");
        $this->assertEquals("lecturer", $user->getType());

        $user->setType("student");
        $this->assertEquals("student", $user->getType());
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
        $this->assertEquals(1, $user->getId());

        $this->assertNull(User::getById(-1));
    }

    public function testGetUserByEmail()
    {
        $email = "video-admin@ntnu.no";
        $user = User::getByEmail($email);  // The admin user should have ID 1 in the DB

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());

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

    public function testValidate()
    {
        $errors = User::validate("asdf", "aaa", "bbb", "admin");

        $this->assertContains("Invalid email", $errors);
        $this->assertContains("Password should be stronger", $errors);
        $this->assertContains("Passwords do not match", $errors);
        $this->assertContains("Invalid user type", $errors);

        $errors = User::validate("video-admin@ntnu.no", "a23456789", "a23456789",
            "lecturer");
        $this->assertContains("Email already exists", $errors);

        $errors = User::validate("test@email.no", "a23456789", "a23456789", "student");
        $this->assertEmpty($errors);
    }

    public function testVerifiedFlag()
    {
        $user = new User();

        $this->assertFalse($user->isVerified());

        $user->setVerified(true);
        $this->assertTrue($user->isVerified());

        $user->setVerified(false);
        $this->assertFalse($user->isVerified());
    }

    public function testAccessLevel()
    {
        // Verify student levels
        $user = new User(null, null, "student");

        $user->setVerified(false);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);
        $user->setVerified(true);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);

        // Verify student levels
        $user = new User(null, null, "lecturer");

        $user->setVerified(false);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);
        $user->setVerified(true);
        $this->assertEquals($user->getAccessLevel(), User::LECTURER);

        // Verify student levels
        $user = new User(null, null, "admin");

        $user->setVerified(false);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);
        $user->setVerified(true);
        $this->assertEquals($user->getAccessLevel(), User::ADMIN);
    }

    public function testHasAccess()
    {
        $user = new User(null, null, "admin");

        $this->assertTrue($user->is(User::STUDENT));
        $this->assertTrue($user->isStudent());

        $this->assertFalse($user->is(User::LECTURER));
        $this->assertFalse($user->isLecturer());

        $this->assertFalse($user->is(User::ADMIN));
        $this->assertFalse($user->isAdmin());

        $user->setVerified(true);

        $this->assertTrue($user->is(User::STUDENT));
        $this->assertTrue($user->isStudent());

        $this->assertTrue($user->is(User::LECTURER));
        $this->assertTrue($user->isLecturer());

        $this->assertTrue($user->is(User::ADMIN));
        $this->assertTrue($user->isLecturer());
    }

    public function testInsert()
    {
        $user = new User("test@user", "test-pass", "student");
        $id = $user->insert();
        $this->assertNotEquals(false, $id);

        $fetchedUser = User::getById($id);

        $this->assertInstanceOf(User::class, $fetchedUser);
        $this->assertEquals($user->getEmail(), $fetchedUser->getEmail());
    }

}
