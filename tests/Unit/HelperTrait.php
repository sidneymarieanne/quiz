<?php

namespace Tests\Unit;

use App\Answer;
use App\Pool;
use App\Question;
use App\Quiz;

trait HelperTrait
{
    public function createCorrectAnswer()
    {
        return factory(Answer::class)->create(['is_correct' => true]);
    }

    public function createWrongAnswer()
    {
        return factory(Answer::class)->create(['is_correct' => false]);
    }

    public function createRandomValidQuestions($quantity = 1)
    {
        $questions = factory(Question::class, $quantity)->create();
        foreach ($questions as $question) {
            $question->answers()->save($this->createCorrectAnswer());
            $nbAnswers = random_int(1,4);
            $question->answers()->saveMany(factory(Answer::class, $nbAnswers)->create());
        }
        return $questions->load('answers');
    }

    public function createRandomValidPools($quantity = 1, $nbQuestions = 5)
    {
        $pools = factory(Pool::class, $quantity)->create();
        foreach ($pools as $pool) {
            $pool->questions()->saveMany(
                $this->createRandomValidQuestions($nbQuestions)
            );
        }
        return $pools;
    }

    public function createRandomValidQuizzes($quantity = 1, $nbPools = null)
    {
        if (empty($nbPools)) {
            $nbPools = random_int(1,4);
        }
        $quizzes = factory(Quiz::class, $quantity)->create([
            'nb_questions' => random_int($nbPools, 30)
        ]);
        foreach ($quizzes as $quiz) {
            // insert pools
            for ($i = 1; $i <= $nbPools; $i++) {
                $pool = $this->createRandomValidPools(1,1)->first();
                $quiz->pools()->save($pool, ['weight' => random_int(1,4) * 10]);
            }
            // create enough questions to make it valid
            $data = $quiz->calculateNumberOfQuestionsByPool();
            foreach ($data as $row) {
                $pool = Pool::find($row['pool_id']);
                $pool->questions()->saveMany(
                    $this->createRandomValidQuestions($row['take'])
                );
            }
        }
        return $quizzes;
    }
}