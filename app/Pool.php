<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pool extends Model
{
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function isValid()
    {
        return $this->questions()->count() >= 1
            and $this->questions->every(function ($question) {
                return $question->isValid();
            });
    }
}
