<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Pool;
use App\Question;

class PoolTest extends TestCase
{
    use RefreshDatabase, WithFaker, HelperTrait;

    /** @test */
    public function testAPoolCanHaveManyQuestions()
    {
        $pool = factory(Pool::class)->create();
        $this->assertEquals(0, $pool->questions()->count());

        $question = $this->createRandomValidQuestions()->first();
        $pool->questions()->save($question);
        $this->assertEquals(1, $pool->questions()->count());

        $questions = $this->createRandomValidQuestions(2);
        $pool->questions()->saveMany($questions);
        $this->assertEquals(3, $pool->questions()->count());
    }

    /** @test */
    public function testAQuestionCanNotBelongToManyPools()
    {
        $pool1 = factory(Pool::class)->create();
        $pool2 = factory(Pool::class)->create();
        $question = $this->createRandomValidQuestions()->first();
        // question is in pool1
        $pool1->questions()->save($question);
        $this->assertEquals($question->id, $pool1->questions()->first()->id);
        $this->assertNull(optional($pool2->questions()->first())->id);
        // question is in pool2
        $pool2->questions()->save($question);
        $this->assertEquals($question->id, $pool2->questions()->first()->id);
        $this->assertNull(optional($pool1->questions()->first())->id);
    }

    /** @test */
    public function testAPoolWithoutQuestionIsNotValid()
    {
        $pool = factory(Pool::class)->create();
        $this->assertFalse($pool->isValid());
    }

    /** @test */
    public function testAPoolOfOneValidQuestionIsValid()
    {
        $pool = factory(Pool::class)->create();
        $pool->questions()->saveMany(
            $this->createRandomValidQuestions(1)
        );
        $this->assertTrue($pool->isValid());
    }

    /** @test */
    public function testAPoolOfManyValidQuestionsIsValid()
    {
        $pool = factory(Pool::class)->create();
        $pool->questions()->saveMany(
            $this->createRandomValidQuestions(4)
        );
        $this->assertTrue($pool->isValid());
    }

    /** @test */
    public function testAPoolOfManyValidQuestionsAndOneNotValidIsNotValid()
    {
        $pool = factory(Pool::class)->create();
        $question = factory(Question::class)->create();
        $pool->questions()->save($question);
        $pool->questions()->saveMany(
            $this->createRandomValidQuestions(4)
        );
        $this->assertFalse($pool->isValid());
    }

    /** @test */
    public function testRandomPoolHelper()
    {
        $pools = $this->createRandomValidPools(4);
        foreach ($pools as $pool) {
            $this->assertTrue($pool->isValid());
        }
    }
}
