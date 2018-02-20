<?php

/**
 * Class representing a single user.
 *
 * This class is responsible for managing user information as well as 
 */
class User {
    const STUDENT = 1;
    const LECTURER = 2;
    const ADMIN = 3;

    private $id;
    private $email;
    private $password;
    private $type;

    private $verified;


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
     * Get the user's type
     *
     * @return string The user's type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the logged in user based on session dataheaders.
     *
     */
    static function loggedIn()
    {
        // 
        if (!array_key_exists("user_id", $_SESSION)) {
            return null;
        }

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
        $stmt = $dbh->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);

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
        $stmt = $dbh->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $stmt->setFetchMode(DB::FETCH_OBJECT, "User");
        $result = $stmt->fetch();

        if ($result !== false) {
            return $result;
        }

    }

    /**
     * Get a list of users that are pending verification as admins or lecturers.
     *
     * @param int $id Email of the user that should be loaded
     */
    static function getPendingVerification()
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given ID
        $stmt = $dbh->prepare("SELECT * FROM user WHERE type != 'student'"
                            . "AND verified = false;");
        $stmt->execute();
        $stmt->setFetchMode(DB::FETCH_OBJECT, "User");

        $users = [];

        foreach ($stmt as $user) {
            $users[] = $user;
        }

        return $users;
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

    static function validate($email, $password, $password2, $type)
    {
        $errors = [];

        // Verify that email has at least an at sign and a dot after the at
        $tmp = explode("@", $email);
        $count_at = count($tmp);
        if ($count_at > 1) {
            $has_dot = strpos($tmp[1], ".") !== false;
        }

        if ($count_at != 2) {
            $errors[] = "Invalid email";
        } else if (!$has_dot) {
            $errors[] = "Invalid email1";
        }

        // Check that the password is "strong"
        if (strlen($password) < 8) {
            $errors[] = "Password should be stronger";

        } else if (!preg_match("/[0-9]+/", $password)) {
            $errors[] = "Password should be stronger";

        } else if (!preg_match("/[a-zA-Z]+/", $password)) {
            $errors[] = "Password should be stronger";
        }

        // Check that the passwords match
        if ($password !== $password2) {
            $errors[] = "Passwords do not match";
        }

        // Check that the user type is valid
        if (!in_array($type, ["student", "lecturer"])) {
            $errors[] = "Invalid user type";
        }

        // Verify that there doesn't exist a user with that email already
        if (!is_null(User::getByEmail($email))) {
            $errors[] = "Email already exists";
        }

        return $errors;
    }

    /**
     * Insert the current user into the database.
     *
     * This will only take care of fields that are expected on registration!
     * @return mixed False if insertion failed, otherwise the ID of the inserted row.
     */
    public function insert() {
        $sql = "INSERT INTO user (email, password, type) "
            . "VALUES (:email, :password, :type)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam("email", $this->email);
        $stmt->bindParam("password", $this->password);
        $stmt->bindParam("type", $this->type);

        if ($stmt->execute() === false) {
            return false;
        }

        return $dbh->lastInsertId();
    }

    /**
     * Update the current user into the database.
     *
     * This will only take care of fields that are changeable through the interface!
     * @return bool True if the update was successful, false otherwise.
     */
    public function update() {
        $sql = "UPDATE user SET type = :type, verified = :verified "
             . "WHERE id = :id;";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam("id", $this->id);
        $stmt->bindParam("verified", $this->verified);
        $stmt->bindParam("type", $this->type);

        return $stmt->execute() !== false;
    }

    /**
     * Check whether an administrator has verified this user's status
     *
     * @return bool True if verified, otherwise false
     */
    public function isVerified()
    {
        return (bool) $this->verified;
    }

    /**
     * Set whether an administrator has verified this user's status
     *
     * @param bool $flag True if verified, otherwise false
     */
    public function setVerified($flag)
    {
        $this->verified = $flag;
    }

    /**
     * Set the user's type
     *
     * @param string $type One of "student", "admin", "lecturer"
     */
    public function setType($type)
    {
        if (in_array($type, ["student", "admin", "lecturer"])) {
            $this->type = $type;
        }
    }

    /**
     * Get the user's access level.
     *
     * This value should be compared to the consts User::ADMIN, User::LECTURER
     * and User::STUDENT.
     *
     * @return int The user's access level.
     */
    public function getAccessLevel()
    {
        if ($this->verified != true) {
            return User::STUDENT;
        }

        switch ($this->type) {
            case "admin":
                return User::ADMIN;

            case "lecturer":
                return User::LECTURER;
        }

        return User::STUDENT;
    }

    /**
     * Check whether the user has access to features required by the given level.
     *
     * @param int $accessLevel An access level (User::STUDENT, User::ADMIN etc.)
     * @return bool True if the user has access, otherwise false
     */
    public function is($accessLevel) {
        return $this->getAccessLevel() >= $accessLevel;
    }

    /**
     * Check whether the user has access privileges matching a student.
     *
     * @return bool True if the user has student access, otherwise false.
     */
    public function isStudent() {
        return $this->is(User::STUDENT);
    }

    /**
     * Check whether the user has access privileges matching a lecturer.
     *
     * @return bool True if the user has lecturer access, otherwise false.
     */
    public function isLecturer() {
        return $this->is(User::LECTURER);
    }

    /**
     * Check whether the user has access privileges matching an admin.
     *
     * @return bool True if the user has admin access, otherwise false.
     */
    public function isAdmin() {
        return $this->is(User::ADMIN);
    }
}
