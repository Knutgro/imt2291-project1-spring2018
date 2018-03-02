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

        $this->user = new User( "mock@email.donotuse", "Some User", "nopass", "lecturer" );
        $this->user->insert();

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
     * Verify that we can look up a playlist by playlist ID
     */
    public function testGetPlaylistById()
    {
        // Insert a playlist into the DB, avoiding using the insert method as
        // its testsdepends on us.
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO playlist (user, title, description, subject, topic) "
                            . "VALUES (?, 'title', 'desc', 'subj', 'topic');");
        $this->assertTrue($stmt->execute([$this->user->getId()]));

        // Get the inserted ID form the DB
        $this->id = $dbh->lastInsertId();

        // Read back the playlist from the DB
        $playlist = Playlist::getPlaylistById($this->id);

        // Check that we got back our own playlist
        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertEquals($playlist->getId(), $this->id);
    }

    /**
     * Verify that we can insert playlists and playlist videos into the database
     *
     * @depends testGetPlaylistById
     */
    public function testInsertPlaylistAndVideo()
    {
        // Insert a playlist into the DB
        $playlist = new playlist($this->user->getId(), "test-title",
            "test-description", "test-subject", "test-topic");
        $id = $playlist->insertPlaylist();
        $this->assertNotFalse($id);

        // Retrieve the playlist from the DB
        $fetchedPlaylist = Playlist::getPlaylistById($id);

        // Verify that we actually got our playlist back
        $this->assertInstanceOf(Playlist::class, $fetchedPlaylist);
        $this->assertEquals($playlist->getUser(), $fetchedPlaylist->getUser());

        // Insert a video into the playlist
        $video = $playlist->insertVideo($this->video1id);
        $this->assertNotFalse($video);
    }

    /**
     * Verify that we can get the videos from a playlist
     *
     * @depends testInsertPlaylistAndVideo
     */
    public function testGetVideoByPlaylistId()
    {
        // Create a playlist
        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->id = $this->playlist->insertPlaylist();

        // Insert some videos into the playlist
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO playlistvideos (playlist, video, no) VALUES (?, ?, 0);");
        $this->assertTrue($stmt->execute([$this->id, $this->video1id]));
        $this->assertTrue($stmt->execute([$this->id, $this->video2id]));

        // Get he video list from the database
        $videos = Playlist::getVideosByPlaylistId($this->id);

        // Verify that we got back two videos.
        $this->assertInternalType('array', $videos);
        $this->assertCount(2, $videos);
    }

    /**
     * Verify that we can search for playlists
     *
     * @depends testInsertPlaylistAndVideo
     */
    public function testSearchPlaylistByKeyword()
    {
        // Create a playlist
        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->id = $this->playlist->insertPlaylist();

        // Look up the playlist using one of its fields
        $playlist = Playlist::searchPlaylistsByKeyword($this->playlist->getTopic());

        // Verify that we got back a result list with one item
        $this->assertInternalType('array', $playlist);
        $this->assertCount(1, $playlist);

        // Check that the result we got back is the playlist we created
        $first = $playlist[0];
        $this->assertInstanceOf(Playlist::class, $first);
        $this->assertEquals($first->getId(), $this->id);
    }

    /**
     * Verify that we can get playlists that are owned by a given user
     *
     * @depends testInsertPlaylistAndVideo
     */
    public function testGetPlaylistByUser()
    {
        // Create a playlist
        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->id = $this->playlist->insertPlaylist();

        // Get theplaylist from hte DB by user
        $playlist = Playlist::getPlaylistByUser( $this->user->getId());

        // Verify that we got a list back
        $this->assertInternalType('array', $playlist);
        $this->assertCount(1, $playlist);

        // Verify that we got our playlist back
        $first = $playlist[0];
        $this->assertInstanceOf(Playlist::class, $first);
        $this->assertEquals($first->getUser(), $this->user->getId());
    }


    /**
     * Verify that we can retrieve the order number for a video
     *
     * @depends testInsertPlaylistAndVideo
     */
    public function testGetVideoOrderNo()
    {
        // Create a playlist
        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->id = $this->playlist->insertPlaylist();

        // Prepare data
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO playlistvideos (playlist, video, no) VALUES (?, ?, ?);");
        $this->assertTrue($stmt->execute([$this->id, $this->video1id, 12345]));

        $this->assertEquals(12345, Playlist::getVideoOrderNo($this->video1id, $this->id));
    }

    /**
     * Verify that we can swap the order of two videos.
     *
     * @depends testGetPlaylistById
     * @depends testGetVideoOrderNo
     * @depends testInsertPlaylistAndVideo
     */
    public function testChangeVideoOrder()
    {
        // Create a playlist
        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->id = $this->playlist->insertPlaylist();

        // Prepare data
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO playlistvideos (playlist, video, no) VALUES (?, ?, ?);");
        $this->assertTrue($stmt->execute([$this->id, $this->video1id, 1]));
        $this->assertTrue($stmt->execute([$this->id, $this->video2id, 2]));

        // Verify that video 2 has "no" 2 right now
        $this->assertEquals(2, Playlist::getVideoOrderNo($this->video2id, $this->id));

        // Swap the two videos
        $playlist = Playlist::getPlaylistById($this->id);
        $playlist->changeVideoOrder($this->video1id, $this->video2id);

        // Verify that video 2 has been swapped to "no" 1
        $this->assertEquals(1, Playlist::getVideoOrderNo($this->video2id, $this->id));
    }

    /**
     * Verify that we can remove videos from the database
     *
     * @depends testGetPlaylistById
     * @depends testGetVideoByPlaylistId
     * @depends testInsertPlaylistAndVideo
     */
    public function testRemoveVideoFromPlaylist()
    {
        // Create a playlist
        $this->playlist = new Playlist( $this->user->getId(), "title", "desc", "subj", "topic" );
        $this->id = $this->playlist->insertPlaylist();

        // Prepare playlist entries
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO playlistvideos (playlist, video, no) VALUES (?, ?, 0);");
        $this->assertTrue($stmt->execute([$this->id, $this->video1id]));
        $this->assertTrue($stmt->execute([$this->id, $this->video2id]));

        // Remove a video from the playlist
        $playlist = Playlist::getPlaylistById($this->id);
        $playlist->removeVideoFromPlaylist($this->video1id);

        // Verify that the video is not present in the playlist anymore
        $videos = Playlist::getVideosByPlaylistId($this->id);
        $this->assertInternalType('array',$videos);
        $this->assertEquals(1, count($videos));
    }
}
