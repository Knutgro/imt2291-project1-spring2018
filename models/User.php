<?php

/**
 * Class representing a single user.
 *
 * This class is responsible for managing user information as well as 
 */
class User {
    private $id;
    private $email;
    private $password;
    private $type;


    /**
     * User constructor.
     *
     * All fields are optional in order to allow for PDO class fetching. If any
     * of these fields are null on database actions, the database will throw an
     * error so everything's A-OK.
     *
     * @param string $email Email address for this user
     * @param string $pass Unhashed password for this user
     */
    public function __construct($email=null, $password=null, $type=null) {
        $this->email = $email;
        $this->type = $type;

        if (!is_null($password)) {
            $this->setPassword($password);
        }
    }

    /**
     * Check whether the given password matches the user's password.
     *
     * @param string The password that should be verified
     * @return bool True if password matches, false otherwise
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Set the user's password
     *
     * This method will hash the given password before storing it in the
     * database. You do not need to do any hashing prior to calling this method.
     *
     * @param string The user's new password
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Get the user's email address
     *
     * @return string The user's email address
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the user's ID
     *
     * @return int The user's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the logged in user based on session dataheaders.
     *
     */
    static function loggedIn()
    {
        // Bail early if there's no user ID tied to this session.
        if ( (int) $_SESSION["user_id"] <= 0 ) {
            return null;
        }

        return self::getById($_SESSION["user_id"]);
    }

    /**
     * Load a user instance from the database, given user ID.
     *
     * @param int $id ID of the user that should be loaded
     */
    static function getById($id)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given ID
        $stmt = $dbh->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $stmt->setFetchMode(DB::FETCH_OBJECT, "User");
        $result = $stmt->fetch();

        if ($result !== false) {
            return $result;
        }

    }

    /**
     * Load a user instance from the database, given user email.
     *
     * @param int $id Email of the user that should be loaded
     */
    static function getByEmail($email)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given ID
        $stmt = $dbh->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $stmt->setFetchMode(DB::FETCH_OBJECT, "User");
        $result = $stmt->fetch();

        if ($result !== false) {
            return $result;
        }

    }


    /**
     * Log in a user, given the user's credentials
     *
     * @param string $email The email of the user that should be logged in
     * @param string $password The user's unhashed password, for verification
     * @return bool True if login was successful, otherwise false
     */
    static function doLogin($email, $password)
    {
        $user = User::getByEmail($email);

        // Return false if user does not exist
        if ($user === null) {
            return false;
        }

        // Return true if password matches
        if ($user->verifyPassword($password) === true) {
            $_SESSION["user_id"] = $user->getId();
            return true;
        }

        // Fallback to false
        return false;
    }

}
