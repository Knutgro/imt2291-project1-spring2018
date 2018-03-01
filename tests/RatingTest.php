<?php
declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

/**
 * Unit tests on the Rating database model.
 */
final class RatingTest extends TestCase {
    /**
     * Prepare test data and transaction
     */
    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();

        $this->user = new User( "mock@email.donotuse", "nopass", "lecturer" );
        $this->assertNotFalse($this->user->insert());

        $this->extraUser = new User( "eemock@email.donotuse", "nopass", "lecturer" );
        $this->assertNotFalse($this->extraUser->insert());

        $this->video = $this->createDummyVideo();
        $this->assertNotFalse($this->video->insert());
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
     * Create a video that should be used during testing
     *
     * @return Video A pre-constructed video instance ready to be inserted and
     * tested against.
     */
    public function createDummyVideo()
    {
        return new Video( $this->user, [
            "title" => "title",
            "description" => "description",
            "subject" => "subject",
            "topic" => "topic",
        ], "videoPath", "thumbnailPath");
    }

    /**
     * Verify that ratings can be inserted into the database
     */
    public function testInsert()
    {
        $rating = new Rating($this->user->getId(), $this->video->getId(), 2);
        $inserted = $rating->insert();

        $this->assertNotFalse($inserted);

        $fetchedRating = rating::getUserRating($this->user->getId(), $this->video->getId());
        $this->assertEquals($rating->getRating(), $fetchedRating);
    }

    /**
     * Check that the user rating is retrieved from database
     *
     * @depends testInsert
     */
    public function testGetUserRating()
    {
        $rating = new Rating($this->user->getId(), $this->video->getId(), 5);
        $inserted = $rating->insert();

        $rating = Rating::getUserRating($this->user->getId(), $this->video->getId());
        $this->assertEquals(5, $rating);
    }

    /**
     * Check that the average rating is retrieved from database
     *
     * @depends testInsert
     */
    public function testGetTotalRating()
    {
        $rating = new Rating($this->user->getId(), $this->video->getId(), 5);
        $rating->insert();

        $rating = new Rating($this->extraUser->getId(), $this->video->getId(), 2);
        $rating->insert();


        $totalRating = Rating::getTotalRating($this->video->getId());
        $this->assertEquals(7 / 2, $totalRating);
    }
}
