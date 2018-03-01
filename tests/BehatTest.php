<?php

declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;


/**
 * Functional testing using Mink
 *
 * These tests builds on eachother in order to simplify test layout, database
 * cleanup and reduce boilerplate.
 *
 * Because of this, any failed tests will result in dependant tests to be
 * skipped, theoretically ensuring that calling the preceeding tests from inside
 * the new one should give the same result as preceeding tests directly.
 */
final class BehatTest extends TestCase {
    protected $baseUrl = "http://localhost:8000/";


    /**
     * Prepare tests by setting up Mink and creating a test user
     */
    public function setUp()
    {
        // Prepare Behat
        $driver = new \Behat\Mink\Driver\GoutteDriver();
        $this->session = new \Behat\Mink\Session($driver);
        $this->session->start();

        $this->createUser();
    }

    /**
     * Create a user that should be used by these tests
     */
    public function createUser()
    {
        $this->user_pwd = "Test Password";

        $this->user = new User( "behat@test.user", $this->user_pwd, "lecturer" );
        $this->assertNotFalse($this->user->insert());

        $this->user->setVerified(true);
        $this->user->update();
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
     * Verify that we can log in to the website, also allowing for further tests
     * to work as they should
     */
    public function testLoginUser()
    {
        // Get the log in page
        $this->session->visit($this->baseUrl . "login.php");
        $page = $this->session->getPage();

        // Log in the user
        $page->find('css', "#email")->setValue($this->user->getEmail());
        $page->find('css', "#password")->setValue($this->user_pwd);
        $page->find('css', "#login")->submit();

        // Get the frontpage redirect and verify we're logged in
        $page = $this->session->getPage();

        $logout = $page->find('css', '#btn-logout');
        $this->assertInstanceOf(NodeElement::Class, $logout, 'Unable to find logout button. Not logged in?');
    }

    /**
     * Verify that we can create a new playlist, and get redirected to the new
     * playlist's video listing page.
     *
     * @depends testLoginUser
     */
    public function testCreatePlaylist()
    {
        $this->testLoginUser();

        // Get the playlist creation page
        $this->session->visit($this->baseUrl . "addPlaylist.php");
        $page = $this->session->getPage();

        // Set form data and submit
        $page->find('css', "#title")->setValue("title");
        $page->find('css', "#description")->setValue("description");
        $page->find('css', "#subject")->setValue("subject");
        $page->find('css', "#topic")->setValue("topic");

        $page->find('css', "#playlistForm")->submit();

        // Get the new page and verify that we're on the playlist view page
        $page = $this->session->getPage();

        // Check that the subscribers button is there. This button is only
        // present on the playlist view page and the video view page
        // (when playing a playlist).
        $sub = $page->find('css', 'a.btn.subscribe');
        $this->assertInstanceOf(NodeElement::Class, $sub,
            'Unable to find subscribe button.');

        $query = parse_url($this->session->getCurrentUrl(), PHP_URL_QUERY);
        $params = [];
        parse_str($query, $params);
        $this->playlistId = $params["v"];

        $this->assertNotNull(Playlist::getPlaylistById($this->playlistId),
            "Unable to verify playlist id " . $this->playlistId . " through DB");
    }

    /**
     * Verify that we can add three videos to the playlist, and that we can find
     * the videos on the playlist's video listings page afterwards.
     *
     * @depends testCreatePlaylist
     */
    public function testAddVideos()
    {
        $this->testCreatePlaylist();

        // Add some dummy videos to this user for use in testing
        $videos = [
            $this->createDummyVideo(),
            $this->createDummyVideo(),
            $this->createDummyVideo(),
        ];
        foreach ( $videos as $video )
            $this->assertNotFalse( $video->insert(), "Unable to prepare video" );

        // Go over each video, adding them to the playlist
        foreach ( $videos as $video )
        {
            // Load the playlist page
            $this->session->visit($this->baseUrl . "playlistSelect.php?v=" . $this->playlistId);
            $page = $this->session->getPage();

            // Verify that the video is not in the playlist to start with
            $videoRowSelector = "#playlist-" . $this->playlistId . "-video-" . $video->getId();
            $videoRow = $page->find('css', $videoRowSelector);
            $this->assertNull( $videoRow );

            // Load the view page and submit the "add to playlist" form
            $this->session->visit($this->baseUrl . "watch.php?v=" . $video->getId());

            $page = $this->session->getPage();
            $page->find('css', "#playlist")->setValue( $this->playlistId );
            $page->find('css', "#playlistForm")->submit();

            // Verify that we got redirected to the playlist page
            $this->assertEquals($this->baseUrl . "playlistSelect.php?v=" . $this->playlistId,
                $this->session->getCurrentUrl());

            // Verify that the video is now present
            $videoRowSelector = "#playlist-" . $this->playlistId . "-video-" . $video->getId();
            $videoRow = $page->find('css', $videoRowSelector);
            $this->assertNotNull( $videoRow, "Unable to find ${videoRowSelector}" );
        }

        // Load the playlist page again, now verifying that all videos are
        // present on the same page. This serves to verify that there's no videos
        // being replaced or removed, maybe from DB transaction problems or the
        // like.
        $this->session->visit($this->baseUrl . "playlistSelect.php?v=" . $this->playlistId);
        $page = $this->session->getPage();

        foreach ( $videos as $video ) {
            // Verify that the video is present
            $videoRowSelector = "#playlist-" . $this->playlistId . "-video-" . $video->getId();
            $videoRow = $page->find('css', $videoRowSelector);
            $this->assertNotNull( $videoRow );
        }
    }

    /**
     * Clean up after the test
     */
    public function tearDown()
    {
        // Delete the user, which will cascade delete videos and playlists
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare( "DELETE FROM user WHERE id = ?" );
        $this->assertTrue($stmt->execute([$this->user->getId()]));
    }

}
