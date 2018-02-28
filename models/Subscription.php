<?php


class Subscription
{
    private $user;
    private $playlist;

    /**
     * Subscription constructor.
     *
     * All fields are optional in order to allow for PDO class fetching. If any
     * of these fields are null on database actions, the database will throw an
     * error so everything's A-OK.
     *
     * @param int $user user id for the subscription
     * @param int $playlist id for the subscription
     */
    public function __construct($user = null, $playlist = null)
    {
        $this->user = $user;
        $this->playlist = $playlist;
    }

    /**
     * Get the Subscription's user id
     *
     * @return int user id
     */

    public function getId()
    {
        return $this->user;
    }

    /**
     * Get the Subscription's playlist id
     *
     * @return int playlist id
     */

    public function getPlaylist()
    {
        return $this->playlist;
    }
    /**
     * Finds subscriptions by a given user id.
     * @param $id user id.
     * @return array of subscription objects.
     */
    static public function getSubscriptionsByUserId($id)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        $sql = "SELECT * FROM subscription WHERE user = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Subscription");
        $results = [];

        foreach ($stmt as $row) {
            $results[] = $row;
        }
        return $results;
    }
    /**
     * Insert the loaded subscription into the database.
     * @return mixed False if insertion failed,
     * otherwise true.
     */
    public function insertSubscription()
    {
        $sql = "INSERT INTO subscription (user, playlist)
                VALUES (:user, :playlist)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":user", $this->user);
        $stmt->bindParam(":playlist", $this->playlist);
        return $stmt->execute();
    }

}