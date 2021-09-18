<?php

namespace App\Util;

final class ReplyUtils
{
    public static function success(array $payload = []): array
    {
        return array_merge(['status' => true], $payload);
    }

    public static function failure(array $payload = []): array
    {
        return array_merge(['status' => false], $payload);
    }
}
