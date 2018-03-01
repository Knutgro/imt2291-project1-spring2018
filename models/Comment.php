<?php
/**
 * Comment class
 * Manages comments on videos
 */

class Comment
{
    private $id;
    private $user;
    private $video;
    private $comment;

    /**
     * Comment constructor.
     *
     * All fields are optional in order to allow for PDO class fetching. If any
     * of these fields are null on database actions, the database will throw an
     * error so everything's A-OK.
     *
     * @param int $user id for the comment
     * @param int $video id for the video
     * @param string $comment for the comment
     */
    public function __construct($user = null, $video = null, $comment = null)
    {
        $this->user = $user;
        $this->video = $video;
        $this->comment = $comment;
    }
    /**
     * Get the comment's user id
     *
     * @return int the users id
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the User that posted this comment
     * @return User The commenter
     */
    public function getCommenter()
    {
        return User::getById($this->user);
    }

    /**
     * Get the comment text
     * @return string Comment text
     */
    public function getText()
    {
        return $this->comment;
    }
    /**
     * Get the comment's  id
     *
     * @return int the comment's id
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Finds a comment of a given id
     * @param $id comment id
     * @return object Comment which has the given id
     */
    static public function getCommentById($id)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given ID
        $stmt = $dbh->prepare("SELECT * FROM comment WHERE id = ?");
        $stmt->execute([$id]);

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $stmt->setFetchMode(DB::FETCH_OBJECT, "Comment");
        $result = $stmt->fetch();
        return $result;
    }
    /**
     * Insert the loaded comment into the database.
     * @return mixed False if insertion failed,
     * otherwise it returns the id of the inserted comment.
     */
    public function insert()
    {
        $sql = "INSERT INTO comment (user, video, comment)
                VALUES (:user, :video, :comment)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":user", $this->user);
        $stmt->bindParam(":video", $this->video);
        $stmt->bindParam(":comment", $this->comment);

        if ($stmt->execute() === false) {
            return false;
        }
        return $this->id = $dbh->lastInsertId();
    }
    /**
     * Finds comments by a given video id.
     * @param $video video id.
     * @return array of comment objects.
     */
    static public function getCommentsByVideoId($video)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        $sql = "SELECT * FROM comment WHERE video = ? ORDER BY id DESC";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$video]);
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Comment");
        $results = [];

        foreach ($stmt as $row) {
            $results[] = $row;
        }
        return $results;
    }
}
