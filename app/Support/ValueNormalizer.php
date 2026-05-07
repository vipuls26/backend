<?php

namespace App\Support;

use Illuminate\Support\Str;

class ValueNormalizer
{
    public static function enumLike(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return Str::of($value)
            ->trim()
            ->replace(['-', ' '], '_')
            ->lower()
            ->squish()
            ->toString();
    }
}
