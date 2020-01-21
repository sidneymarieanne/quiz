<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;


class PlayTest extends TestCase
{
    use RefreshDatabase, WithFaker, HelperTrait;

    /** @test */
    public function testAllTheQuestionsOfThePlayBelongsToItsQuiz()
    {
        $quiz = $this->createRandomValidQuizzes()->first();
        $play = $quiz->createPlay();
        // $play->load('questions');
        $questionIds = $quiz->allQuestions()->pluck('id')->toArray();
        foreach($play->questions as $question) {
            $this->assertTrue(in_array($question->id, $questionIds));
        }
    }
}
