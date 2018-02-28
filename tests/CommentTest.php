<?php


declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase {

    public function testGetUser()
    {
        $user = new Comment(1);
        $this->assertEquals($user->getUser(), 1);
    }



    public function testInsertComment()
    {
        $comment = new Comment(1, 1, "hello world");
        $id = $comment->insertComment();
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
        $comment = Comment::getCommentsByVideoId( 1);

        $this->assertInternalType('array',$comment);
        $this->assertEquals(1,count($comment));
        $first = $comment[0];
        $this->assertInstanceOf(Comment::class, $first);
        $this->assertEquals($first->getUser(), 1);

    }
}