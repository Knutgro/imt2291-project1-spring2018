<?php
declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class SubscriptionTest extends TestCase {

    public function testGetId()
    {
        $user = new subscription(1,1);
        $this->assertEquals($user->getId(), 1);
    }

    public function testInsertSubscription()
    {
        $subscription = new Subscription(1, 1);
        $outcome = $subscription->insertSubscription();
        $this->assertNotEquals(false, $outcome);

        $fetchedSubscription = Subscription::getSubscriptionsByUserId(1);

        $this->assertInstanceOf(Subscription::class, $fetchedSubscription[0]);
        $this->assertEquals($subscription->getId(), $fetchedSubscription[0]->getId());

    }

    public function testGetSubscriptionsByUserId()
    {
        $subscriptions = Subscription::getSubscriptionsByUserId( 1);

        $this->assertInternalType('array',$subscriptions);
        $this->assertEquals(1,count($subscriptions));
        $first = $subscriptions[0];
        $this->assertInstanceOf(Subscription::class, $first);
        $this->assertEquals($first->getId(), 1);

    }

}