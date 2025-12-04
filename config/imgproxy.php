<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | This URL is used when generating imgproxy links. Set it to the root of
    | your imgproxy instance, including scheme and host.
    |
    */

    'base_url' => env('IMGPROXY_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | Hex-encoded AES key used for encrypted sources. Set this only when the
    | source encoding below is configured to "encrypted".
    |
    */

    'encryption_key' => env('IMGPROXY_ENCRYPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Signing Keys
    |--------------------------------------------------------------------------
    |
    | Hex-encoded key and salt used to sign imgproxy URLs. Provide both or
    | leave both empty. Signature size (bytes) trims the signature length
    | when your server expects a shorter value.
    |
    */

    'key' => env('IMGPROXY_KEY'),

    'salt' => env('IMGPROXY_SALT'),

    'signature_size' => env('IMGPROXY_SIGNATURE_SIZE'),

    /*
    |--------------------------------------------------------------------------
    | Source Encoding
    |--------------------------------------------------------------------------
    |
    | Controls how sources are encoded before signing.
    |
    | Supported encodings: "base64", "encrypted", "plain"
    |
    */

    'source_encoding' => env('IMGPROXY_SOURCE_ENCODING', 'base64'),

    /*
    |--------------------------------------------------------------------------
    | Option Names
    |--------------------------------------------------------------------------
    |
    | Use imgproxy's short option names instead of the long names.
    |
    */

    'use_short_options' => env('IMGPROXY_SHORT_OPTIONS', false),

];
