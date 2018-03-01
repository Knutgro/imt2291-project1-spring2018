<?php

declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class SubscriptionTest extends TestCase {

    private $user;
    private $playlist;

    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();

        // Mock data
        $this->user = new User( "mock@email.donotuse", "nopass", "student" );
        $this->user->insert();

        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->playlist->insertPlaylist();
    }

    public function tearDown()
    {
        $dbh = DB::getPDO();
        $dbh->rollBack();
    }


    public function testGetId()
    {
        $subscription = new Subscription(1, 1);
        $this->assertEquals($subscription->getUser(), 1);
    }

    public function testInsertDelete()
    {
        // Add new subscription
        $subscription = new Subscription(2, 1);
        $outcome = $subscription->insert();
        $this->assertTrue($outcome);

        // Verify that the subscription were sent to the database
        $subscriptions = Subscription::getSubscriptionsByUserId(1);

        $found = false;
        foreach ($subscriptions as $subscription) {
            if ($subscription->getUser() == 1 && $subscription->getPlaylist() == 1) {
                $found = true;
            }
        }
        $this->assertTrue($found);

        // Delete the subscription
        $this->assertTrue($subscription->delete());

        // Verify that we can't find it in the database anymore
        $subscriptions = Subscription::getSubscriptionsByUserId(1);

        $found = false;
        foreach ($subscriptions as $subscription) {
            if ($subscription->getUser() == 1 && $subscription->getPlaylist() == 1) {
                $found = true;
            }
        }
        $this->assertFalse($found);
    }

    public function testGetSubscriptionsByUserId()
    {
        $subscriptions = Subscription::getSubscriptionsByUserId(1);

        $this->assertInternalType('array',$subscriptions);
        $this->assertTrue(count($subscriptions) >= 1);

        $first = $subscriptions[0];
        $this->assertInstanceOf(Subscription::class, $first);
        $this->assertEquals($first->getUser(), 1);
    }

    /**
     * Test that Subscription::getsubscription returns a subscription instance
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
