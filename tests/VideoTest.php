<?php

declare(strict_types=1);

require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;


class VideoTest extends TestCase
{

    public function testGetVideoByID ()
    {
        $video = Video::getByID( 1);

        $this->assertInstanceOf(Video::class, $video);
        $this->assertEquals($video->getId(), 1);
    }

    public function testGetVideoBySubject ()
    {
        $video = Video::getBySubject( "IT");

        $this->assertInstanceOf(Video::class, $video);
        $this->assertEquals($video->getSubject(), "IT");
    }

    public function testGetVideoByUser ()
    {
        $video = Video::getByUser( "1");

        $this->assertInternalType('array',$video);
        $this->assertEquals(1,count($video));
        $first = $video[0];
        $this->assertInstanceOf(Video::class, $first);
        $this->assertEquals($first->getUser(), 1);
    }
}
