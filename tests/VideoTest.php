<?php

declare(strict_types=1);

require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;


class MockUser {
    public function getId() {
        return 1;
    }
}


class VideoTest extends TestCase
{
    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();
    }

    public function tearDown()
    {
        $dbh = DB::getPDO();
        $dbh->rollBack();
    }


    public function testGetVideoByID ()
    {
        $video = Video::getByID( 1);

        $this->assertInstanceOf(Video::class, $video);
        $this->assertEquals(1 , $video->getId());
    }

    public function testGetVideoBySubject ()
    {
        $video = Video::getBySubject( "IMT2019");

        $this->assertInstanceOf(Video::class, $video);
        $this->assertEquals($video->getSubject(), "IMT2019");
    }

    public function testGetVideoByUser ()
    {
        $video = Video::getByUser( "1");

        $this->assertInternalType('array',$video);
        $this->assertEquals(4,count($video));
        $first = $video[0];
        $this->assertInstanceOf(Video::class, $first);
        $this->assertEquals(1 , $first->getUser());
    }

    public function testGetVideoBySearch ()
    {
        $video = Video::getBySearch( "testtest");

        $this->assertInternalType('array',$video);
        $this->assertEquals(1,count($video));
        $first = $video[0];
        $this->assertInstanceOf(Video::class, $first);
        $this->assertEquals(1 ,$first->getUser());

        $video = Video::getBySearch( "IMT2019");

        $this->assertInternalType('array',$video);
        $this->assertEquals(1,count($video));
        $first = $video[0];
        $this->assertInstanceOf(Video::class, $first);
        $this->assertEquals(1 , $first->getUser());

        $video = Video::getBySearch( "SMF");

        $this->assertInternalType('array',$video);
        $this->assertEquals(1,count($video));
        $first = $video[0];
        $this->assertInstanceOf(Video::class, $first);
        $this->assertEquals(1 , $first->getUser());
    }

    public function testInsert()
    {

        // Prepare a user and insert it into the database
        $video = new Video(new MockUser(), [
            "title"       => "Title",
            "description" => "Description",
            "subject"     => "Subject",
            "topic"       => "Topic",
        ], "videoPath", "thumbPath");
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
}
