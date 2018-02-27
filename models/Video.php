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

    public function __construct($user=null, $data=null, $videoPath=null, $thumbnailPath=null)
    {
        if ($user) {
            $this->user = $user->getId();

            $this->title = $data["title"];
            $this->description = $data["description"];
            $this->subject = $data["subject"];
            $this->topic = $data["topic"];
            $this->videoPath = $videoPath;
            $this->thumbnailPath = $thumbnailPath;
        }
    }

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
     * Get the Video title
     *
     * @return string the Video's title
     */

     public function getTitle()
    {
        return $this->title;
    }


    /**
     * Get the Video ThumbnailPath
     *
     * @return string the Video's ThumbnailPath
     */

    public function getThumbnailPath()
    {
        return $this->thumbnailPath;
    }

    /**
     * Get the Video path
     *
     * @return string the Video's videoPath
     */

    public function getVideoPath()
    {
        return $this->videoPath;
    }


    /**
     * Get the Video topic
     *
     * @return string the Video's topic
     */

    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Get the Video description
     *
     * @return string the Video's description
     */

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Load a video instance from the database, given video ID.
     *
     * @param int $id ID of the video that should be loaded
     */
    static function getById($id)
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
    static function getBySubject($subject)
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
    static function getByUser($user)
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

    /**
     * Load video instances from the database, given a search parameter.
     *
     * @param string $search what the user want to search for
     */
    static function getBySearch($search)
    {
        //Get the DB handle
        $dbh = DB::getPDO();

        // Fetch data from DB given search
        $stmt = $dbh->prepare("SELECT video.* FROM video 
                                  LEFT JOIN user ON user.id=video.user
                                      WHERE video.subject LIKE :search OR 
                                      video.title LIKE :search OR 
                                      video.topic LIKE :search OR
                                      user.email LIKE :search");
        $stmt->bindValue(':search', "%{$search}%");

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Video");
        $results = [];
        // Return the data as an initialized video object, given the result set
        // isn't empty
        foreach ($stmt as $row) {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Insert the current video into the database.
     *
     * This will only take care of fields that are expected on upload
     * @return mixed False if insertion failed, otherwise the ID of the inserted row.
     */
    public function insert() {
        $sql = "INSERT INTO video (title, description, videoPath, "
            . "                    thumbnailPath, subject, topic, user) "
            . "VALUES (:title, :desc, :video, :thumb, :subject, :topic, :user)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam("title", $this->title);
        $stmt->bindParam("desc", $this->description);
        $stmt->bindParam("video", $this->videoPath);
        $stmt->bindParam("thumb", $this->thumbnailPath);
        $stmt->bindParam("subject", $this->subject);
        $stmt->bindParam("topic", $this->topic);
        $stmt->bindParam("user", $this->user);

        if ($stmt->execute() === false) {
            return false;
        }

        // Update the instance ID and return it
        $this->id = $dbh->lastInsertId();
        return $this->id;
    }

}
