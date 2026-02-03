<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummarizationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'input_text',
        'input_url',
        'input_pdf_path',
        'summary',
        'entities',
        'summary_type',
        'sentiment_label',
        'sentiment_score',
        'category',
        'image_url',
    ];

    protected $casts = [
        'entities' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
