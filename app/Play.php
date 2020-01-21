<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Play extends Model
{
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
}
