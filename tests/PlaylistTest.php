<?php

declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class PlaylistTest extends TestCase {

    private $id;
    private $user;
    private $playlist;
    private $video1;
    private $video2;
    private $video1id;
    private $video2id;


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

    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();

        $this->user = new User( "mock@email.donotuse", "nopass", "lecturer" );
        $this->user->insert();

        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->id = $this->playlist->insertPlaylist();

        $this->video1 = $this->createDummyVideo();
        $this->video1id = $this->video1->insert();

        $this->video2 = $this->createDummyVideo();
        $this->video2id = $this->video2->insert();

    }

    public function tearDown()
    {
        $dbh = DB::getPDO();
        $dbh->rollBack();
    }

    /**
     * @Depends setUp()
     **/
    public function testGetId()
    {
        $playlistId = $this->playlist->getId();
        $this->assertEquals($playlistId, $this->id);
    }

    /**
     * @Depends setUp()
     * @Depends testGetId()
     **/
    public function testGetPlaylistByUser()
    {
        $playlist = Playlist::getPlaylistByUser( $this->user->getId());

        $this->assertInternalType('array', $playlist);
        $this->assertTrue(count($playlist) >= 1);
        $first = $playlist[0];
        $this->assertInstanceOf(Playlist::class, $first);
        $this->assertEquals($first->getUser(), $this->user->getId());
    }
    /**
     * @Depends setUp()
     * @Depends testGetId()
     **/
    public function testGetPlaylistById()
    {
        $playlist = Playlist::getPlaylistByid($this->id);

        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertEquals($playlist->getId(), $this->id);
    }


    public function testGetVideoByPlaylistId()
    {
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO playlistvideos (playlist, video, no) VALUES (?, ?, 0);");
        $this->assertTrue($stmt->execute([$this->id, $this->video1id]));
        $this->assertTrue($stmt->execute([$this->id, $this->video2id]));

        $videos = Playlist::getVideosByPlaylistId($this->id);

        $this->assertInternalType('array', $videos);
        $this->assertEquals(2, count($videos));
    }


    public function testSearchPlaylistByKeyword()
    {
        $playlist = Playlist::searchPlaylistsByKeyword($this->playlist->getTopic());

        $this->assertInternalType('array', $playlist);
        $this->assertTrue(count($playlist) >= 1);
        $first = $playlist[0];
        $this->assertInstanceOf(Playlist::class, $first);
        $this->assertEquals($first->getUser(), $this->id);
    }

    /**
     * @depends testGetPlaylistById
     */
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
        $this->assertEquals($playlist->getVideoOrderNo(1, 1), 2);
    }
    /**
     * @depends testGetPlaylistById
     * @depends testGetVideoByPlaylistId
     */
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
