<?php

declare(strict_types=1);

require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;


class VideoTest extends TestCase
{
    /**
     * Prepare DB transaction and test data
     */
    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();

        $this->user = new User( "mock@email.donotuse", "nopass", "lecturer" );
        $this->assertNotFalse($this->user->insert());
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
     * Verify that we are able to look up videos by ID
     */
    public function testGetVideoById()
    {
        // Insert a video into the DB, avoiding using the insert method as
        // its tests depends on us.
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare(
            "INSERT INTO video (title, description, videoPath, thumbnailPath, subject, topic, user) "
          . "VALUES ('title', 'desc', 'vidPath', 'thumbPath', 'subj', 'topic', ?);"
        );
        $this->assertTrue($stmt->execute([$this->user->getId()]));

        // Get the inserted ID form the DB
        $this->id = $dbh->lastInsertId();

        $video = Video::getByID($this->id);

        $this->assertInstanceOf(Video::class, $video);
        $this->assertEquals($this->id, $video->getId());
    }

    /**
     * Verify that we are able to insert videos into the DB
     *
     * @depends testGetVideoById
     */
    public function testInsert()
    {
        // Prepare a video and insert it into the database
        $video = $this->createDummyVideo();
        $id = $video->insert();

        // Ensure that the insert was successful
        $this->assertNotEquals(false, $id);
        // Verify that the model updated the instance ID.
        $this->assertEquals($id, $video->getId());

        // Fetch the video from the database
        $fetchedVideo = Video::getById($id);

        // Verify that at least one field matches as it should, NOT NULL
        // constraints should be enough to verify that the other fields at least
        // holds a value.
        $this->assertInstanceOf(Video::class, $fetchedVideo);
        $this->assertEquals($video->getVideoPath(), $fetchedVideo->getVideoPath());
    }

    /**
     * Verify that we can look up videos by uploader
     *
     * @depends testInsert
     */
    public function testGetVideoByUser()
    {
        // Create a test video
        $testVideo = $this->createDummyVideo();
        $testVideo->insert();

        // Look up videos belonging to our user
        $video = Video::getByUser($this->user->getId());

        // Verify that we actually got some results back
        $this->assertInternalType('array', $video);
        $this->assertCount(1, $video);

        // Verify that we actually got our video back
        $first = $video[0];
        $this->assertInstanceOf(Video::class, $first);
        $this->assertEquals($this->user->getId(), $first->getUser());
    }

    /*
     * Test the search function, by trying out different search words,
     *   that should be in the database at all times
     *
     * @depends testInsert
     */
    public function testGetVideoBySearch ()
    {
        // Create a test video
        $testVideo = $this->createDummyVideo();
        $testVideo->insert();

        $video = Video::getBySearch($testVideo->getSubject());
        $this->checkVideoIsPresent($testVideo->getId(), $video);

        $video = Video::getBySearch($testVideo->getTitle());
        $this->checkVideoIsPresent($testVideo->getId(), $video);

        $video = Video::getBySearch($testVideo->getTopic());
        $this->checkVideoIsPresent($testVideo->getId(), $video);

        $video = Video::getBySearch($this->user->getEmail());
        $this->checkVideoIsPresent($testVideo->getId(), $video);
    }

    /**
     * Check that a video with the given id is present in the list
     *
     * @param $expected Video ID to look for
     * @param $list Array of videos to look through
     */
    public function checkVideoIsPresent($expected, $list)
    {
        $found = false;

        foreach ($list as $video) {
            if ($video->getId() == $expected) {
                $found = true;
            }
        }

        $this->assertTrue($found);
    }

}
