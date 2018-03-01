<?php

declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class PlaylistTest extends TestCase {

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

    public function testGetPlaylistOwner()
    {
        $user = new playlist(1);
        $this->assertEquals($user->getUser(), 1);
    }


    public function testGetPlaylistByUser()
    {
        $playlist = Playlist::getPlaylistByUser( "1");

        $this->assertInternalType('array', $playlist);
        $this->assertTrue(count($playlist) >= 1);
        $first = $playlist[0];
        $this->assertInstanceOf(Playlist::class, $first);
        $this->assertEquals($first->getUser(), 1);
    }


    public function testGetPlaylistById()
    {
        $playlist = Playlist::getPlaylistByid(1);  // The admin user should have ID 1 in the DB

        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertEquals($playlist->getId(), 1);
    }


    public function testGetVideoByPlaylistId()
    {
        $videos = Playlist::getVideosByPlaylistId(1);

        $this->assertInternalType('array', $videos);
        $this->assertEquals(2, count($videos));
    }


    public function testSearchPlaylistByKeyword()
    {
        $playlist = Playlist::searchPlaylistsByKeyword( "ntnu");

        $this->assertInternalType('array', $playlist);
        $this->assertTrue(count($playlist) >= 1);
        $first = $playlist[0];
        $this->assertInstanceOf(Playlist::class, $first);
        $this->assertEquals($first->getUser(), 1);
    }


    public function testInsertPlaylistAndVideo()
    {
        $playlist = new playlist(1, "test-title", "test-description", "test-subject", "test-topic");
        $id = $playlist->insertPlaylist();
        $this->assertNotEquals(false, $id);

        $fetchedPlaylist = playlist::getPlaylistById($id);

        $this->assertInstanceOf(Playlist::class, $fetchedPlaylist);
        $this->assertEquals($playlist->getUser(), $fetchedPlaylist->getUser());

        $video = $playlist->insertVideo(1,$playlist->getId());
        $this->assertNotEquals(false, $video);
    }


    public function testChangeVideoOrder()
    {
        $playlist = Playlist::getPlaylistById(1);
        $playlist->changeVideoOrder(1, 2);
        $this->assertEquals($playlist->getVideoOrderNo(1), 2);
    }

    public function testRemoveVideoFromPlaylist()
    {
        $playlist = Playlist::getPlaylistById(1);
        $playlist->removeVideoFromPlaylist(1);
        $videos = Playlist::getVideosByPlaylistId(1);

        $this->assertInternalType('array',$videos);
        $this->assertEquals(1, count($videos));

        $this->assertEquals($playlist->getVideoOrderNo(2), 1);
    }
}
