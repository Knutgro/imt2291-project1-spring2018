<?php

declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

/**
 * Unit tests on the Subscription database model
 */
final class SubscriptionTest extends TestCase {

    private $user;
    private $playlist;

    /**
     * Prepare DB transaction and test data
     */
    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();

        // Mock data
        $this->user = new User( "mock@email.donotuse", "Some User", "nopass", "student" );
        $this->user->insert();

        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->playlist->insertPlaylist();
    }

    /**
     * DB cleanup
     */
    public function tearDown()
    {
        $dbh = DB::getPDO();
        $dbh->rollBack();
    }

    /**
     * Verify that we can insert and delete subscriptions from the database
     */
    public function testInsertDelete()
    {
        $userId = $this->user->getId();
        $playlistId = $this->playlist->getId();

        // Add new subscription
        $subscription = new Subscription($userId, $playlistId);
        $outcome = $subscription->insert();
        $this->assertTrue($outcome);

        // Verify that the subscription were sent to the database
        $subscriptions = Subscription::getSubscriptionsByUserId($userId);

        $found = false;
        foreach ($subscriptions as $subscription) {
            if ($subscription->getUser() == $userId && $subscription->getPlaylist() == $playlistId) {
                $found = true;
            }
        }
        $this->assertTrue($found);

        // Delete the subscription
        $this->assertTrue($subscription->delete());

        // Verify that we can't find it in the database anymore
        $subscriptions = Subscription::getSubscriptionsByUserId($userId);

        $found = false;
        foreach ($subscriptions as $subscription) {
            if ($subscription->getUser() == $userId && $subscription->getPlaylist() == $playlistId) {
                $found = true;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Test that Subscription::getSubscription returns a list of subscriptions
     * for the given user
     */
    public function testGetSubscriptionsByUserId()
    {
        $userId = $this->user->getId();
        $playlistId = $this->playlist->getId();

        // Verify that there's currently no subscriptions for this test user
        $subscriptions = Subscription::getSubscriptionsByUserId($userId);
        $this->assertInternalType('array',$subscriptions);
        $this->assertEquals(0, count($subscriptions));

        // Insert a sub into the DB manually
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO subscription (user, playlist) VALUES (?, ?);");
        $this->assertTrue($stmt->execute([$userId, $playlistId]));

        // Check that we got a subscription back
        $subscriptions = Subscription::getSubscriptionsByUserId($userId);
        $this->assertInternalType('array',$subscriptions);
        $this->assertEquals(1, count($subscriptions));

        $first = $subscriptions[0];
        $this->assertInstanceOf(Subscription::class, $first);
        $this->assertEquals($userId, $first->getUser());
        $this->assertEquals($playlistId, $first->getPlaylist());
    }

    /**
     * Test that Subscription::getSubscription returns a subscription instance
     * for valid lookups
     */
    public function testGetSubscription()
    {
        $userId = $this->user->getId();
        $playlistId = $this->playlist->getId();

        // Insert a sub into the DB manually
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO subscription (user, playlist) VALUES (?, ?);");
        $this->assertTrue($stmt->execute([$userId, $playlistId]));

        // Retrieve the sub
        $sub = Subscription::getSubscription($userId, $playlistId);
        $this->assertNotNull($sub);
        $this->assertInstanceOf(Subscription::class, $sub);
    }

}
