<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_en',
        'question_ru',
        'question_tk',
        'answer_en',
        'answer_ru',
        'answer_tk',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}