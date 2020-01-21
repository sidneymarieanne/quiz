<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    public function pool()
    {
        return $this->belongsTo(Pool::class);
    }

    public function plays()
    {
        return $this->belongsToMany(Play::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function correctAnswers()
    {
        return $this->answers()->correct();
    }

    public function isValid()
    {
        return ($this->answers()->count() > 1
            and $this->correctAnswers()->exists()
        );
    }
}
