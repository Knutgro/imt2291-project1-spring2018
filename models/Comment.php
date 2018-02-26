<?php
/**
 * Created by PhpStorm.
 * User: Knut
 * Date: 26.02.2018
 * Time: 18.47
 */

class Comment
{
    private $id;
    private $user;
    private $video;
    private $comment;


    public function __construct($user = null, $video = null, $comment = null)
    {
        $this->user = $user;
        $this->video = $video;
        $this->comment = $comment;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getId()
    {
        return $this->id;
    }


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

    public function insertComment()
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

    static public function getCommentsByVideoId($video)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        $sql = "SELECT * FROM comment WHERE video = ?";
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