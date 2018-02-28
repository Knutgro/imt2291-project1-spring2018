<?php

/**
 * Rating class
 * Manages a video's rating
 */
class Rating
{
    private $user;
    private $video;
    private $rating;

    /**
     * Rating constructor.
     *
     * All fields are optional in order to allow for PDO class fetching. If any
     * of these fields are null on database actions, the database will throw an
     * error so everything's A-OK.
     *
     * @param string $user id for the user
     * @param string $video id for the video
     * @param int $rating
     */
    public function __construct($user = null, $video = null, $rating = null)
    {
        $this->user = $user;
        $this->video = $video;
        $this->rating = $rating;
    }
    /**
     * Get the rating's user id
     *
     * @return int the users id
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the rating's video id
     *
     * @return int The video id
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Get the rating's rating
     *
     * @return int rating
     */

    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Insert the current rating into the database.
     * @return bool False if insertion failed, otherwise true.
     */
    public function insertRating()
    {
        $sql = "INSERT INTO rating (video, user, rating)
                VALUES (:video, :user, :rating)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":video", $this->video);
        $stmt->bindParam(":user", $this->user);
        $stmt->bindParam(":rating", $this->rating);

        return $stmt->execute();
    }
    /**
     * Finds the rating of a given user on a given video
     * @param $user user id of rating
     * @param $video video id of rating
     * @return int user's rating of a video
     */
    static public function getUserRating($user, $video)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT rating FROM rating WHERE user = $user AND video = $video");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }

    /**
     * Finds the total rating of a given video.
     * @param $video video id of total rating
     * @return int total rating of a video.
     */
    static public function getTotalRating($video)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT AVG(rating) average FROM rating WHERE video = $video");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }

}