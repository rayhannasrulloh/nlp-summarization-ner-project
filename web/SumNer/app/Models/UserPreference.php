<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'abstractive_min_length',
        'abstractive_max_length',
        'abstractive_num_beams',
        'extractive_retention_ratio',
        'ner_threshold',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
