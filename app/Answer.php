<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $guarded = [];

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }
}
