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

    public function getUser()
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

        $sql = "SELECT playlist.* FROM subscription 
                  LEFT JOIN playlist ON subscription.user=playlist.user
                    WHERE subscription.user = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "playlist");
        $results = [];

        foreach ($stmt as $row) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Checks whether there's a subscription between the user and the playlist
     *
     * @param $user The ID of the user
     * @param $playlist The ID of the playlist
     * @return Sibscription instance if subscribed, otherwise null
     */
    static public function getSubscription($user, $playlist)
    {
        // Get the DB handle
        $dbh = DB::getPDO();

        $sql = "SELECT * FROM subscription WHERE user = ? AND playlist = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$user, $playlist]);

        $stmt->setFetchMode(DB::FETCH_OBJECT, "Subscription");
        $result = $stmt->fetch();

        if ($result !== false) {
            return $result;
        }
    }

    /**
     * Insert the loaded subscription into the database.
     * @return mixed False if insertion failed,
     * otherwise true.
     */
    public function insert()
    {
        $sql = "INSERT INTO subscription (user, playlist)
                VALUES (:user, :playlist)";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":user", $this->user);
        $stmt->bindParam(":playlist", $this->playlist);
        return $stmt->execute();
    }

    public function delete()
    {
        $sql = "DELETE FROM subscription WHERE user = :user AND "
             . "playlist = :playlist";

        $dbh = DB::getPDO();
        $stmt = $dbh->prepare($sql);

        $stmt->bindParam(":user", $this->user);
        $stmt->bindParam(":playlist", $this->playlist);
        return $stmt->execute();
    }

}
