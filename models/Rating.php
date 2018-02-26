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


    public function insertRating()
    {
        $sql = "INSERT INTO playlist (video, user, rating)
                VALUES (:video, :user, :rating)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":video", $this->video);
        $stmt->bindParam(":user", $this->user);
        $stmt->bindParam(":comment", $this->rating);

        return $stmt->execute();
    }


    public function getUserRating($user)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT rating FROM rating WHERE user = $user");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }


    public function getTotalRating($video)
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("SELECT AVG(rating) FROM rating WHERE video = $video");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result;
    }

}