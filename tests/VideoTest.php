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
        $this->assertEquals(2,count($video));
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
}
