<?php
/**
 * Playlist class
 * Manages playlist and its videos.
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

    /**
     * Playlist constructor.
     *
     * All fields are optional in order to allow for PDO class fetching. If any
     * of these fields are null on database actions, the database will throw an
     * error so everything's A-OK.
     *
     * @param int    $user id for the owner of the playlist
     * @param string $title title of playlist
     * @param string $description of playlist
     * @param string $subject of playlist
     * @param string $topic of playlist
     * @param int    $lastInserted of playlist
     */
    public function __construct($user = null, $title = null, $description = null, $subject = null, $topic = null, $lastInserted = 0)
    {
        $this->user = $user;
        $this->title = $title;
        $this->description = $description;
        $this->subject = $subject;
        $this->topic = $topic;
        $this->lastInserted = $lastInserted;
    }
    /**
     * Get the playlist's id
     *
     * @return int The playlist id
     */

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the user's id
     *
     * @return int The users id
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the playlist's title
     *
     * @return string The title of the playlist
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the playlist's description
     *
     * @return string The description of the playlist
     */

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the playlist's last inserted video id
     *
     * @return int The video id of last inserted in playlist
     */
    public function getLastInserted()
    {
        return $this->lastInserted;
    }

    /**
     * Finds all videos associated with a given playlist's id.
     *
     * @param int $id ID of the playlist that contains the videos
     * @return array of video ids
     */
    static public function getVideosByPlaylistId($id)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT * FROM playlistvideos WHERE playlist = $id ORDER BY no");
        $stmt->execute();

        // Return the data as an initialized user object, given the result set
        // isn't empty
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Finds a given video's order number in its playlist.
     * @param video $id of video
     * @return int order number of the video
     */
    static public function getVideoOrderNo($id)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT no FROM playlistvideos WHERE video = $id ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }

    /**
     * Load a playlist instance from the database, given playlist ID.
     *
     * @param int $id ID of the user that should be loaded
     * @return object playlist of the given id
     */
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

    /**
     * Finds all playlist's by a given user ID.
     *
     * @param int $user ID of the owner of playlist's.
     * @return array of playlist objects
     */

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

    /**
     * Insert the current playlist into the database.
     *
     * This will only take care of fields that are expected on registration!
     * @return mixed False if insertion failed, otherwise the ID of the inserted row.
     */
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


    /**
     * Inserts a video into a playlist
     *
     * @param $videoId video id of video to be inserted.
     * @param $playlistId playlist id where the video is to be inserted.
     * @return bool False if insertion failed, true if it was successful
     */
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

    /**
     * Searches the playlist and user(email) tables for keyword
     *
     * @param $keyword string word you would like to find in the playlist's
     * title, subject, topic or its owner's email.
     * @return array of playlist's objects where the keyword was found in the aforementioned columns.
     */
    static public function searchPlaylistsByKeyword($keyword)
    {
        $sql = "SELECT p.* FROM playlist p
                INNER JOIN  user u 
                ON p.user = u.id
                WHERE p.title LIKE Concat('%',:keyword,'%')
                OR p.subject LIKE Concat('%',:keyword,'%') 
                OR p.topic  LIKE Concat('%',:keyword,'%')
                OR u.email LIKE Concat ('%',:keyword,'%')";
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


    /**
     * Changes the order of videos in a loaded playlist
     * If an update fails, order numbers are changed back.
     * @param $videoId1 video id of the first video to be swapped.
     * @param $videoId2 video id of the second video to be swapped.
     * @return bool False if swap failed, true if it was successful
     */
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
            else {
                $sql3 = "UPDATE playlistvideos SET no = :video1 WHERE playlist = :id AND video = :video1Id";
                $stmt3 = $dbh->prepare($sql3);
                $stmt3->bindParam(":video1", $video1);
                $stmt3->bindParam(":video1Id", $videoId1);
                $stmt3->bindParam(":id", $this->id);
                $stmt3->execute();
            }
        }
        return false;
    }
    /**
     * Removes a video from a loded playlist.
     * Function changes the to be removed video's order number to
     * the last number and deletes it, without disturbing the video order
     * in the playlist.
     * @param $video video id of the video to be deleted from the playlist
     * @return bool False if remove failed, true if it was successful
     */
    public function removeVideoFromPlaylist($video)
    {
        $no = self::getVideoOrderNo($video);
        $count = self::countVideos($this->id);
        for($i = $no; $i <= $count; $i++)
        {
            $j = self::getIdByOrderNo($i++);
            self::changeVideoOrder($i, $j);

        }
        $dbh = DB::getPDO();
        $sql = "DELETE FROM playlistvideos WHERE video = $video";
        $stmt = $dbh->prepare($sql);
        return $stmt->execute();
    }
    /**
     * Finds the video id from its order number in a loaded playlist.
     * @param int $no video order number.
     * @return video id belonging to the given order number.
     */
    public function getIdByOrderNo($no)
    {
        $dbh = DB::getPDO();
        $sql = "SELECT video FROM playlistvideos WHERE no = $no";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }
    /**
     * Counts number of videos in a playlist.
     * @param $playlist playlist id.
     * @return int of how many videos in a given playlist.
     */
    public function countVideos($playlist)
    {
        $dbh = DB::getPDO();
        $sql = "SELECT COUNT(*) FROM playlistvideos WHERE playlist = $playlist";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }
}