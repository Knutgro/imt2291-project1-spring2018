<?php


class Rating
{
    private $user;
    private $video;
    private $rating;


    public function __construct($user = null, $video = null, $rating = null)
    {
        $this->user = $user;
        $this->video = $video;
        $this->rating = $rating;
    }


    public function getUser()
    {
        return $this->user;
    }


    public function getVideo()
    {
        return $this->video;
    }


    public function getRating()
    {
        return $this->rating;
    }

    /** Returns true if rating was inserted and false if it was not */
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

    /** Returns rating from a given user and video */
    static public function getUserRating($user, $video)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT rating FROM rating WHERE user = $user AND video = $video");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }

    /** Returns Total average rating from a given video  */
    static public function getTotalRating($video)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT AVG(rating) average FROM rating WHERE video = $video");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }

}