<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Quiz extends Model
{
    protected $guarded = [];

    public function pools()
    {
        return $this->belongsToMany(Pool::class)->withPivot('weight');
    }

    public function allPoolsQuestions()
    {
        return $this->hasManyThrough(Question::class, PoolQuiz::class, 'quiz_id', 'pool_id', 'id', 'pool_id');
        // return Question::whereIn('pool_id', $this->pools()->pluck('pool_id'));
    }

    public function allQuestions()
    {
        // return $this->hasManyThrough(Question::class, PoolQuiz::class, 'quiz_id', 'pool_id', 'id', 'pool_id');
        return Question::whereIn('pool_id', $this->pools()->pluck('pool_id'));
    }

    public function plays()
    {
        return $this->hasMany(Play::class);
    }

    public function isValid()
    {
        $takeByPool = $this->calculateNumberOfQuestionsByPool();
        foreach ($takeByPool as $row) {
            if ($row['take'] > $row['max']) {
                return false;
            }
        }
        return $this->pools()->count() >= 1
            and $this->nb_questions > 0
            and $this->nb_questions <= $this->max_nb_questions
            and $this->pools->every(function ($pool) {
                return $pool->isValid();
            });
    }

    public function getMaxNbQuestionsAttribute()
    {
        return $this->allPoolsQuestions()->count();
    }

    public function createPlay()
    {
        $questionIds = $this->generateSetOfQuestions();
        $play = $this->plays()->create();
        $play->questions()->sync($questionIds);
        return $play;
    }

    public function calculateNumberOfQuestionsByPool()
    {
        $totalWeight = $this->getPoolsTotalWeight();
        $result = [];
        foreach ($this->pools as $pool) {
            $take = (int)round($this->nb_questions * $pool->pivot->weight / $totalWeight, 0);
            if ($take==0) {
                $take = 1;
            }
            $result[] = ['pool_id' => $pool->id, 'take' => $take, 'max' => $pool->questions()->count()];
        }
        return $result;
    }

    public function generateSetOfQuestions()
    {
        /* example:
         * pool1: weight=5, nb_questions=40
         * pool2: weight=10, nb_questions=50
         * quiz: nb_questions=30
         * then: 
         * - must take 30*5/(5+10)=10 questions from pool1
         * - must take 30*10/(5+10)=20 questions from pool2
         */
        $totalWeight = $this->getPoolsTotalWeight();
        $nbPools = $this->pools()->count();
        $nbPool = 0;
        $nbQuestions = 0;
        $questionIds = [];
        foreach ($this->pools as $pool) {
            $nbPool++;
            if ($nbPool==$nbPools) {
                $take = $this->nb_questions - $nbQuestions;
            } else {
                $take = round($this->nb_questions * $pool->pivot->weight / $totalWeight, 0);
                if ($take==0) {
                    $take = 1;
                }
            }
            $nbQuestions += $take;
            $questionIds = array_merge(
                $questionIds,
                $pool->questions()->inRandomOrder()->take($take)->pluck('id')->toArray()
            );
        }
        return $questionIds;
    }

    public function getPoolsTotalWeight()
    {
        return PoolQuiz::where('quiz_id', $this->id)
        ->select(DB::raw('SUM(weight) AS total_weight'))
        ->first()->total_weight;
    }
}
