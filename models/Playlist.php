<?php
/**
 * Playlist class
 */

class Playlist
{
    private $id;
    private $user;
    private $title;
    private $description;
    private $subject;
    private $topic;
    private $lastInserted;


    public function __construct($user = null, $title = null, $description = null, $subject = null, $topic = null, $lastInserted = 0)
    {
        $this->user = $user;
        $this->title = $title;
        $this->description = $description;
        $this->subject = $subject;
        $this->topic = $topic;
        $this->lastInserted = $lastInserted;
    }


    public function getId()
    {
        return $this->id;
    }


    public function getUser()
    {
        return $this->user;
    }


    public function getTitle()
    {
        return $this->title;
    }


    public function getDescription()
    {
        return $this->description;
    }


    public function getLastInserted()
    {
        return $this->lastInserted;
    }

    /** Returns an array with video ids from the specified playlistId from the table playlistvideos*/
    static public function getVideosByPlaylistId($id)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT * FROM playlistvideos WHERE playlist = $id ORDER BY no");
        $stmt->execute();

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else
            return false;
    }


    /** Returns a videos order number from its video id*/
    static public function getVideoOrderNo($id)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT no FROM playlistvideos WHERE video = $id ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }


    /**Returns a playlist object from the database based on its id*/
    static function getPlaylistById($id)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given ID
        $stmt = $dbh->prepare("SELECT * FROM playlist WHERE id = ?");
        $stmt->execute([$id]);

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $stmt->setFetchMode(DB::FETCH_OBJECT, "Playlist");
        $result = $stmt->fetch();
        return $result;
    }


    /**Returns an array of playlists based on its owner id*/
    static function getPlaylistByUser($user)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        $sql = "SELECT * FROM playlist WHERE user = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$user]);
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Playlist");
        $results = [];

        foreach ($stmt as $row) {
            $results[] = $row;
        }
        return $results;
    }


    /**Inserts a playlist in the database*/
    public function insertPlaylist()
    {
        $sql = "INSERT INTO playlist (user, title, description, subject, topic)
                VALUES (:user, :title, :description, :subject, :topic)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":user", $this->user);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":subject", $this->subject);
        $stmt->bindParam(":topic", $this->topic);

        if ($stmt->execute() === false) {
            return false;
        }
        return $this->id = $dbh->lastInsertId();
    }


    /** Inserts a video into a given playlist */
    public function insertVideo($videoId, $playlistId)
    {
        $sql = "INSERT INTO playlistvideos (no, playlist, video)
                VALUES (:num, :playlist, :video)";
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":num",$this->lastInserted);
        $stmt->bindParam(":playlist",$playlistId);
        $stmt->bindParam(":video",$videoId);

        return $stmt->execute();

    }


    /** Returns an array with playlist objects, based on a search keyword*/
    static public function searchPlaylistsByKeyword($keyword)
    {
        $sql = "SELECT * FROM playlist 
                WHERE title LIKE Concat('%',:keyword,'%')
                OR subject LIKE Concat('%',:keyword,'%') 
                OR topic  LIKE Concat('%',:keyword,'%')";
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Playlist");

        $results = [];
        foreach ($stmt as $row) {
            $results[] = $row;
        }
        return $results;
    }


    /** Changes the order of two videos in a playlist*/
    public function changeVideoOrder($videoId1, $videoId2)
    {
        $video1 = self::getVideoOrderNo($videoId1);
        $video2 = self::getVideoOrderNo($videoId2);
        $dbh = DB::getPDO();

        $sql = "UPDATE playlistvideos SET no = :video2 WHERE playlist = :id AND video = :video1Id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":video2", $video2);
        $stmt->bindParam(":video1Id", $videoId1);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            $sql2 = "UPDATE playlistvideos SET no = :video1 WHERE playlist = :id AND video = :video2Id";
            $stmt2 = $dbh->prepare($sql2);
            $stmt2->bindParam(":video1", $video1);
            $stmt2->bindParam(":video2Id", $videoId2);
            $stmt2->bindParam(":id", $this->id);

            if ($stmt2->execute()){
                return true;
            }
        }
        return false;
    }
}