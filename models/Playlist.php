<?php
/**
 * Playlist class
 * Class containing video objects
 *
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


    public function __construct($user = null, $title = null, $description = null, $subject = null, $topic = null, $lastInserted = 1)
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



    static public function getVideosByPlaylistId($id)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT * FROM playlist WHERE id = $id");
        $stmt->execute();

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else
            return false;
    }



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


    public function insertPlaylist()
    {
        $sql = "INSERT INTO playlist (user, title, description, subject, topic)
                VALUES (:ownerEmail, :title, :description, :subject, :topic)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":ownerEmail", $this->user);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":subject", $this->subject);
        $stmt->bindParam(":topic", $this->topic);

        if (!$stmt->execute()) {
            return false;
        }

        return $dbh->lastInsertId();
    }


    public function insertVideo($videoId)
    {
        $sql = "INSERT INTO playlistvideos (no, playlist, video)
                VALUES (:no, :playlist, :video)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":no",$this->lastInserted);
        $stmt->bindParam(":playlistId", $this->id);
        $stmt->bindParam(":videoId", $videoId);

        if (!$stmt->execute()) {
            return false;
        }
        $this->lastInserted += 1;
        return true;
    }


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
        if ($results) {
            return $results;
        } else
            return false;
    }


    public function changeVideoOrder($playlistId, $videoId1, $videoId2) {
        $sql = "UPDATE playlistvideos AS playlistvideos1
                Where playlist = :playlistId
                JOIN playlistvideos AS playlistvideos2
                ON playlistvideos1.video = :videoId1 
                AND playlistvideos2.video = :videoId2";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':playlistId', $playlistId);
        $stmt->bindParam(':videoId1', $videoId1);
        $stmt->bindParam(':videoId2', $videoId2);
        if($stmt->execute()) {
            return true;
        }
        else
            return false;
    }

}