<?php

/**
 * Class representing a single video
*/

Class Video {

    private $id;
    private $title;
    private $description;
    private $videoPath;
    private $thumbnailPath;
    private $subject;
    private $topic;
    private $user;

    /**
     * Get the Video ID
     *
     * @return int the Video's ID
     */

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the Video subject
     *
     * @return string the Video's subject
     */

    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the Video owner user id
     *
     * @return int the Video's owner
     */

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Load a video instance from the database, given video ID.
     *
     * @param int $id ID of the video that should be loaded
     */
    public function getById($id)
    {
        //Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given ID
        $stmt = $dbh->prepare("SELECT * FROM video WHERE id = ?");
        $stmt->execute([$id]);

        // Return the data as an initialized video object, given the result set
        // isn't empty
        $stmt->setFetchMode(DB::FETCH_OBJECT, "video");
        $result = $stmt->fetch();

        if ($result !== false) {
            return $result;
        }
    }

    /**
     * Load a video instance from the database, given video subject.
     *
     * @param string $subject Subject of the video that should be loaded
     */
    public function getBySubject($subject)
    {
        //Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given subject
        $stmt = $dbh->prepare("SELECT * FROM video WHERE subject = ?");
        $stmt->execute([$subject]);

        // Return the data as an initialized video object, given the result set
        // isn't empty
        $stmt->setFetchMode(DB::FETCH_OBJECT, "video");
        $result = $stmt->fetch();

        if ($result !== false) {
            return $result;
        }
    }

    /**
     * Load a video instance from the database, given video user ID.
     *
     * @param int $user user ID of the owner of the video
     */
    public function getByUser($user)
    {
        //Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given user
        $stmt = $dbh->prepare("SELECT * FROM video WHERE user = ?");
        $stmt->execute([$user]);
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Video");

        $results = [];
        // Return the data as an initialized video object, given the result set
        // isn't empty
        foreach ($stmt as $video) {
            $results[] = $video;
        }

        return $results;
    }
}
