<?php


declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase {


    /**
     * Prepare DB transaction and test data
     */
    public function setUp()
    {
        $dbh = DB::getPDO();
        $dbh->beginTransaction();

        $this->user = new User( "mock@email.donotuse", "nopass", "lecturer" );
        $this->assertNotFalse($this->user->insert());

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
     * Verify that the getUser method works
     */
    public function testGetUser()
    {
        $user = new Comment(1);
        $this->assertEquals($user->getUser(), 1);
    }

    /**
     * Verify that we can look up comments by ID
     */
    public function testGetCommentById()
    {
        // Insert a comment into the DB, avoiding using the insert method as
        // its tests depends on us.
        $dbh = DB::getPDO();
        $stmt = $dbh->prepare("INSERT INTO comment (user, video, comment) "
                            . "VALUES (?, ?, 'lmao');");
        $this->assertTrue($stmt->execute([$this->user->getId(),
                                          $this->video->getId()]));

        // Get the inserted ID form the DB
        $this->id = $dbh->lastInsertId();

        // Look up the comment from the DB
        $comment = Comment::getCommentById($this->id);

        // Verify that we got our comment back
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals($comment->getId(), $this->id);

    }

    /**
     * Verify that we can insert comments into the DB
     *
     * @depends testGetCommentById
     */
    public function testInsert()
    {
        // Insert our comment into the DB
        $comment = new Comment($this->user->getId(), $this->video->getId(), "hello world");
        $this->assertNotFalse($comment->insert());

        // Load the comment back from the DB
        $fetchedComment = Comment::getCommentById($comment->getId());

        // Make sure we got our comment back
        $this->assertInstanceOf(Comment::class, $fetchedComment);
        $this->assertEquals($comment->getId(), $fetchedComment->getId());
    }

    /**
     * Verify that we can look up comments by video ID
     *
     * @depends testInsert
     */
    public function testGetCommentByVideoId()
    {
        // Insert our comment into the DB
        $comment = new Comment($this->user->getId(), $this->video->getId(), "hello world");
        $this->assertNotFalse($comment->insert());

        // Look up video comments byt video ID
        $comments = Comment::getCommentsByVideoId($this->video->getId());

        // Verify that we got back the amount of comments we expected
        $this->assertInternalType('array', $comments);
        $this->assertCount(1, $comments);

        // Verify that we actually got our comment back
        $first = $comments[0];
        $this->assertInstanceOf(Comment::class, $first);
        $this->assertEquals($this->user->getId(), $comment->getUser());
    }
}
