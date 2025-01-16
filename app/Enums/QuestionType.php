<?php

namespace App\Enums;

enum QuestionType: string
{
    case MULTIPLE = 'multiple';
    case BOOLEAN = 'boolean';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
