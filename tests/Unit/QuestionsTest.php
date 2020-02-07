<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Question;
use App\Answer;

class QuestionsTest extends TestCase
{
    use RefreshDatabase, WithFaker, HelperTrait;

    /** @test */
    public function testQuestionCanHaveManyAnswers()
    {
        $question = factory(Question::class)->create();
        $this->assertEquals(0, $question->answers()->count());

        $answer = factory(Answer::class)->create();
        $question->answers()->save($answer);
        $this->assertEquals(1, $question->answers()->count());

        $answer = factory(Answer::class)->create();
        $question->answers()->save($answer);
        $this->assertEquals(2, $question->answers()->count());
    }

    /** @test */
    public function testQuestionWithoutAnswerIsNotValid()
    {
        $question = factory(Question::class)->create();
        $this->assertFalse($question->isValid());
    }

    /** @test */
    public function testQuestionWithOnlyOneAnswerIsNotValid()
    {
        $question = factory(Question::class)->create();
        $answer = $this->createWrongAnswer();
        $question->answers()->save($answer);
        $this->assertFalse($question->isValid());

        $question = factory(Question::class)->create();
        $answer = $this->createCorrectAnswer();
        $question->answers()->save($answer);
        $this->assertFalse($question->isValid());
    }

    /** @test */
    public function testQuestionWithoutAnyCorrectAnswerIsNotValid()
    {
        $question = factory(Question::class)->create();
        $answers = [
            $this->createWrongAnswer(),
            $this->createWrongAnswer()
        ];
        $question->answers()->saveMany($answers);
        $this->assertFalse($question->isValid());
    }

    /** @test */
    public function testQuestionWithAtLeastOneCorrectAnswerIsValid()
    {
        $question = factory(Question::class)->create();
        $correctAnswer = $this->createCorrectAnswer();
        $answers = factory(Answer::class, 4)->create();
        $question->answers()->save($correctAnswer);
        $question->answers()->saveMany($answers);
        $this->assertTrue($question->isValid());
    }

    /** @test */
    public function testQuestionWithOnlyCorrectAnswersIsValid()
    {
        $question = factory(Question::class)->create();
        $correctAnswers = [
            $this->createCorrectAnswer(),
            $this->createCorrectAnswer(),
            $this->createCorrectAnswer(),
            $this->createCorrectAnswer(),
        ];
        $question->answers()->saveMany($correctAnswers);
        $this->assertTrue($question->isValid());
    }

    /** @test */
    public function testRandomQuestionHelper()
    {
        $questions = $this->createRandomValidQuestions(4);
        foreach ($questions as $question) {
            $this->assertTrue($question->isValid());
        }
    }
}
