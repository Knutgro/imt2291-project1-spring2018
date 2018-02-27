<?php
declare(strict_types=1);
require_once dirname(__FILE__) . "/../lib.php";

use PHPUnit\Framework\TestCase;

final class RatingTest extends TestCase {
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

 public function testInsertRating()
 {
     $rating = new rating(2, 1, 2);
     $inserted = $rating->insertRating();
     $this->assertNotEquals(false, $inserted);

     $fetchedRating = rating::getUserRating(2,1);

     $this->assertEquals($rating->getRating(), $fetchedRating);

 }

 public function testGetUserRating()
 {
     $rating = Rating::getUserRating(1,1);
     $this->assertEquals(5,$rating);
 }

 public function testGetTotalRating()
 {
     $totalRating = Rating::getTotalRating(1);
     $this->assertEquals(7/2,$totalRating);
 }
}
