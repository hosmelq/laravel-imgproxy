<?php

declare(strict_types=1);

use HosmelQ\Imgproxy\Laravel\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Decode a URL-safe Base64 encoded string.
 */
function base64_decode_url(string $value): string
{
    $padded = str_pad($value, strlen($value) + (4 - strlen($value) % 4) % 4, '=', STR_PAD_RIGHT);

    return base64_decode(strtr($padded, '-_', '+/'), true);
}

/**
 * Encode a string using URL-safe Base64 without padding.
 */
function base64_encode_url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}
