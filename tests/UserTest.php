<?php

declare(strict_types=1);

require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;


final class UserTest extends TestCase
{
    /**
     * Prepare database transaction
     */
    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();
    }

    /**
     * DB cleanup
     */
    public function tearDown()
    {
        $dbh = DB::getPDO();
        $dbh->rollBack();
    }

    /**
     * Verify that password functions work
     */
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

    /**
     * Verify that get email works
     */
    public function testGetEmail()
    {
        $user = new User("my@email.no");
        $this->assertEquals("my@email.no", $user->getEmail());
    }

    /**
     * Verify that get type works
     */
    public function testType()
    {
        $user = new User(null, null, null, "student");
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

    /**
     * Verify that we can look up users by ID
     */
    public function testGetUserById()
    {
        // Insert a video into the DB, avoiding using the insert method as
        // its tests depends on us.
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare(
            "INSERT INTO user (email, name, password, type) "
          . "VALUES ('test@user.nosteal', 'Some User', 'lmao', 'student');"
        );
        $this->assertTrue($stmt->execute());

        // Get the inserted ID form the DB
        $this->id = $dbh->lastInsertId();

        $user = User::getById($this->id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($this->id, $user->getId());

        $this->assertNull(User::getById(-1));
    }

    /**
     * Verify that we can verify users
     */
    public function testVerifiedFlag()
    {
        $user = new User();

        $this->assertFalse($user->isVerified());

        $user->setVerified(true);
        $this->assertTrue($user->isVerified());

        $user->setVerified(false);
        $this->assertFalse($user->isVerified());
    }

    /**
     * Verify that we can insert and update data in the database
     *
     * @depends testGetUserById
     * @depends testType
     * @depends testVerifiedFlag
     */
    public function testInsertUpdate()
    {
        // Prepare a basic user and insert it into the database
        $user = new User("test@user", "Some User", "test-pass", "student");
        $id = $user->insert();
        // Ensure that the insert was successful
        $this->assertNotEquals(false, $id);
        // Verify that the model updated the instance ID.
        $this->assertEquals($id, $user->getId());

        // Fetch the user from hte database
        $fetchedUser = User::getById($id);

        // Verify that at least one field matches as it should, NOT NULL
        // constraints should be enough to verify that the other fields at least
        // holds a value.
        $this->assertInstanceOf(User::class, $fetchedUser);

        // Check that some fields contains defaults before testing update
        $this->assertFalse($fetchedUser->isVerified());
        $this->assertEquals("student", $fetchedUser->getType());

        // Change some values and update
        $user->setVerified(true);
        $user->setType("admin");
        $this->assertTrue($user->update());

        // Refetch the user from the database
        $fetchedUser = User::getById($id);

        // Verify that the fields got changed
        $this->assertTrue($fetchedUser->isVerified());
        $this->assertEquals("admin", $fetchedUser->getType());
    }

    /**
     * Verify that we can get the logged in user from the current session
     *
     * @depends testInsertUpdate
     */
    public function testGetLoggedInUser()
    {
        $user = new User("mock@pls2not.disturb", "Some User", "asdf", "student");
        $user->insert();

        unset($_SESSION["user_id"]);

        $this->assertNull(User::loggedIn());

        $_SESSION["user_id"] = $user->getId();

        $this->assertInstanceOf(User::class, User::loggedIn());
    }

    /**
     * Verify that we can look up users by email
     *
     * @depends testInsertUpdate
     */
    public function testGetUserByEmail()
    {
        $mockUser = new User("mock@pls2not.disturb", "Some User", "asdf", "student");
        $mockUser->insert();

        $user = User::getByEmail($mockUser->getEmail());

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($mockUser->getEmail(), $user->getEmail());

        $this->assertNull(User::getByEmail("invalid"));
    }

    /**
     * Verify that users can log in to their accounts
     *
     * @depends testGetLoggedInUser
     * @depends testInsertUpdate
     * @depends testGetUserByEmail
     */
    public function testDoLogin()
    {
        $mockUser = new User("mock@pls2not.disturb", "Some User", "asdf", "student");
        $mockUser->insert();

        // Invalid email
        $this->assertFalse(User::doLogin("nonexistant", "lol"));

        // Invalid password
        $this->assertFalse(User::doLogin("mock@pls2not.disturb", "lol"));

        // Valid everything
        $this->assertTrue(User::doLogin("mock@pls2not.disturb", "asdf"));
    }

    /**
     * Verify that user registration validation works
     *
     * @depends testInsertUpdate
     */
    public function testValidate()
    {
        // Add user to check for the existing user case
        $user = new User("test-existing@donot.steal", "Some User", "asdfasdf", "student");
        $this->assertNotFalse($user->insert());

        // Some basic missing fields
        $errors = User::validate("asdf", "", "aaa", "bbb", "admin");

        $this->assertContains("Invalid email", $errors);
        $this->assertContains("Name cannot be empty", $errors);
        $this->assertContains("Password should be stronger", $errors);
        $this->assertContains("Passwords do not match", $errors);
        $this->assertContains("Invalid user type", $errors);

        // Existing email
        $errors = User::validate($user->getEmail(), "Some Name", "a23456789", "a23456789",
            "lecturer");
        $this->assertContains("Email already exists", $errors);

        // Successful registration
        $errors = User::validate("test@email.no", "Some Name", "a23456789", "a23456789", "student");
        $this->assertEmpty($errors);
    }

    /**
     * Verify that the user only gets higher acess levels if they're verified
     */
    public function testAccessLevel()
    {
        // Verify student levels
        $user = new User(null, null, null, "student");

        $user->setVerified(false);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);
        $user->setVerified(true);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);

        // Verify lecturer levels
        $user = new User(null, null, null, "lecturer");

        $user->setVerified(false);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);
        $user->setVerified(true);
        $this->assertEquals($user->getAccessLevel(), User::LECTURER);

        // Verify admin levels
        $user = new User(null, null, null, "admin");

        $user->setVerified(false);
        $this->assertEquals($user->getAccessLevel(), User::STUDENT);
        $user->setVerified(true);
        $this->assertEquals($user->getAccessLevel(), User::ADMIN);
    }

    /**
     * Verify that access privilege checking works properly
     */
    public function testHasAccess()
    {
        $user = new User(null, null, null, "admin");

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

}
