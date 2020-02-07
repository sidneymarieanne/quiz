<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class QuizPoolWeightTest extends TestCase
{
    use RefreshDatabase, WithFaker, HelperTrait;

    /** @test */
    public function testWeightIsAssociatedToTheQuizPoolRelationship()
    {
        $quiz = $this->createRandomValidQuizzes()->first();
        foreach ($quiz->pools as $pool) {
            $this->assertTrue($pool->pivot->weight > 0);
        }
    }
}
