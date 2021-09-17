<?php

namespace App\Util;

final class ReplyUtils
{
    public static function dump(array $payload = []): array
    {
        return $payload;
    }

    public static function success(array $payload = []): array
    {
        return self::dump(array_merge(['status' => true], $payload));
    }

    public static function failure(array $payload = []): array
    {
        return self::dump(array_merge(['status' => false], $payload));
    }
}
