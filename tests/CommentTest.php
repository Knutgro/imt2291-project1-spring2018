<?php


declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase {


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

    public function testGetUser()
    {
        $user = new Comment(1);
        $this->assertEquals($user->getUser(), 1);
    }
    
    public function testInsert()

    {
        $comment = new Comment(1, 1, "hello world");
        $id = $comment->insert();
        $this->assertNotEquals(false, $id);

        $fetchedComment = Comment::getCommentById($id);

        $this->assertInstanceOf(Comment::class, $fetchedComment);
        $this->assertEquals($comment->getId(), $fetchedComment->getId());
    }

    public function testGetCommentById()
    {
        $comment = Comment::getCommentById(1);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals($comment->getId(), 1);

    }

    public function testGetCommentByVideoId()
    {
        $comments = Comment::getCommentsByVideoId(1);

        $this->assertInternalType('array', $comments);
        $this->assertNotEquals(0, count($comments));

        $first = $comments[0];
        $this->assertInstanceOf(Comment::class, $first);

        // Verify that we have at least one resulting comment from the admin/test user
        $found = false;
        foreach ($comments as $comment) {
            if ($comment->getUser() == 1) {
                $found = true;
            }
        }
        $this->assertTrue($found);

    }
}
