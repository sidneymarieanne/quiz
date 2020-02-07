<?php

namespace Tests\Unit;

use App\Pool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Quiz;

class QuizTest extends TestCase
{
    use RefreshDatabase, WithFaker, HelperTrait;

    /** @test */
    public function testQuizCanHaveManyPools()
    {
        $quiz = factory(Quiz::class)->create();
        $this->assertEquals(0, $quiz->pools()->count());

        $pool = $this->createRandomValidPools()->first();
        $quiz->pools()->save($pool);
        $this->assertEquals(1, $quiz->pools()->count());

        $pools = $this->createRandomValidPools(2);
        $quiz->pools()->saveMany($pools);
        $this->assertEquals(3, $quiz->pools()->count());
    }

    /** @test */
    public function testPoolCanBelongToManyQuizzes()
    {
        $quiz1 = factory(Quiz::class)->create();
        $quiz2 = factory(Quiz::class)->create();
        $pool = $this->createRandomValidPools()->first();
        // pool is in quiz1 only
        $quiz1->pools()->save($pool);
        $this->assertEquals($pool->id, $quiz1->pools()->first()->id);
        $this->assertNull(optional($quiz2->pools()->first())->id);
        // pool is in quiz1 and quiz2
        $quiz2->pools()->save($pool);
        $this->assertEquals($pool->id, $quiz1->pools()->first()->id);
        $this->assertEquals($pool->id, $quiz2->pools()->first()->id);
    }

    /** @test */
    public function testRandomQuizHelper()
    {
        $quizzes = $this->createRandomValidQuizzes(4);
        foreach ($quizzes as $quiz) {
            $this->assertTrue($quiz->isValid());
        }
    }

    /** @test */
    public function testQuizWithAnyUnvalidPoolIsNotValid()
    {
        $quiz = $this->createRandomValidQuizzes()->first();
        $quiz->pools()->attach([
            factory(Pool::class)->create()->id
        ]);
        $quiz->refresh();
        $this->assertFalse($quiz->isValid());
    }

    /** @test */
    public function testValidQuizMustHaveAtLeastOneQuestion()
    {
        $quiz = $this->createRandomValidQuizzes()->first();
        $quiz->nb_questions = 0;
        $this->assertFalse($quiz->isValid());
        $quiz->nb_questions = 1;
        $this->assertTrue($quiz->isValid());
    }

    /** @test */
    public function testValidQuizMustNotHaveMoreQuestionsThanItsPoolsTotalNbOfQuestions()
    {
        $quiz = $this->createRandomValidQuizzes()->first();
        $quiz->nb_questions = $quiz->max_nb_questions + 1;
        $this->assertFalse($quiz->isValid());
        $quiz->nb_questions = $quiz->max_nb_questions;
        $this->assertTrue($quiz->isValid());
    }

    /** @test */
    public function testGenerateSetOfQuestions()
    {
        $quiz = $this->createRandomValidQuizzes()->first();
        $questions = $quiz->generateSetOfQuestions();
        $this->assertEquals($quiz->nb_questions, count($questions));
    }
}
