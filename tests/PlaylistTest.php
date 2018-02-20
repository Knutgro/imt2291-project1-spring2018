<?php

declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class PlaylistTest extends TestCase {

    public function testGetPlaylistOwner()
    {
        $user = new playlist("my@email.no");
        $this->assertEquals($user->getUser(), "my@email.no");
    }

    public function testGetPlaylistByUser() {
        $playlist = Playlist::getPlaylistByUser( "1");

        $this->assertInternalType('array',$playlist);
        $this->assertEquals(1,count($playlist));
        $first = $playlist[0];
        $this->assertInstanceOf(Playlist::class, $first);
        $this->assertEquals($first->getUser(), 1);
    }


    public function testGetPlaylistById() {
        $playlist = Playlist::getPlaylistByid(1);  // The admin user should have ID 1 in the DB

        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertEquals($playlist->getId(), 1);


    }
}
