<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Arguments Separator
    |--------------------------------------------------------------------------
    |
    | The string placed between an option's arguments. It must match the
    | IMGPROXY_ARGUMENTS_SEPARATOR setting on your imgproxy server.
    |
    */

    'arguments_separator' => env('IMGPROXY_ARGUMENTS_SEPARATOR', ':'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | This URL is used when generating imgproxy links. Set it to the root of
    | your imgproxy instance, including scheme and host.
    |
    */

    'base_url' => env('IMGPROXY_BASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | Hex-encoded AES key used for encrypted sources (imgproxy Pro). It must
    | decode to 16, 24 or 32 bytes, and it is required when the source
    | encoding below is set to "encrypted".
    |
    */

    'encryption_key' => env('IMGPROXY_ENCRYPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Image-Info Only Presets
    |--------------------------------------------------------------------------
    |
    | Generate presets-only paths for image-info URLs (imgproxy Pro). Enable
    | this when your server runs with IMGPROXY_INFO_ONLY_PRESETS. Like the
    | server, it defaults to the IMGPROXY_ONLY_PRESETS value when unset.
    |
    */

    'info_only_presets' => (bool) env(
        'IMGPROXY_INFO_ONLY_PRESETS',
        env('IMGPROXY_ONLY_PRESETS', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Image-Info Option Aliases
    |--------------------------------------------------------------------------
    |
    | Maps image-info option names to the exact aliases your server accepts,
    | such as ['size' => 's'] (imgproxy Pro). These apply only to info URLs;
    | processing URLs use "option_aliases" below.
    |
    */

    'info_option_aliases' => [],

    /*
    |--------------------------------------------------------------------------
    | Signing Keys
    |--------------------------------------------------------------------------
    |
    | Hex-encoded key and salt used to sign imgproxy URLs. Provide both or
    | leave both empty. Signature size (bytes, from 1 to 32) trims the
    | signature length when your server expects a shorter value.
    |
    */

    'key' => env('IMGPROXY_KEY'),

    'salt' => env('IMGPROXY_SALT'),

    'signature_size' => (int) env('IMGPROXY_SIGNATURE_SIZE', 32),

    /*
    |--------------------------------------------------------------------------
    | Processing Only Presets
    |--------------------------------------------------------------------------
    |
    | Generate presets-only paths for processing URLs. Enable this when your
    | server runs with IMGPROXY_ONLY_PRESETS. Image-info URLs use the
    | separate "info_only_presets" setting above.
    |
    */

    'only_presets' => (bool) env('IMGPROXY_ONLY_PRESETS', false),

    /*
    |--------------------------------------------------------------------------
    | Processing Option Aliases
    |--------------------------------------------------------------------------
    |
    | Maps processing option names to the exact aliases your server accepts,
    | such as ['resize' => 'rs']. Use this when your server only allows
    | specific option names.
    |
    */

    'option_aliases' => [],

    /*
    |--------------------------------------------------------------------------
    | Source Chunk Size
    |--------------------------------------------------------------------------
    |
    | Splits Base64 and encrypted sources into path segments of this many
    | characters. Set it to zero to keep the source in a single segment.
    |
    */

    'source_chunk_size' => (int) env('IMGPROXY_SOURCE_CHUNK_SIZE', 0),

    /*
    |--------------------------------------------------------------------------
    | Source Encoding
    |--------------------------------------------------------------------------
    |
    | Controls how sources are encoded before signing. Any other value
    | throws an exception when a builder is created.
    |
    | Supported encodings: "base64", "encrypted" (imgproxy Pro), "plain"
    |
    */

    'source_encoding' => env('IMGPROXY_SOURCE_ENCODING', 'base64'),

    /*
    |--------------------------------------------------------------------------
    | Source Filename
    |--------------------------------------------------------------------------
    |
    | Filename appended to Base64 and encrypted sources. Set it when your
    | server runs with IMGPROXY_BASE64_URL_INCLUDES_FILENAME enabled.
    |
    */

    'source_filename' => env('IMGPROXY_SOURCE_FILENAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Trusted Signature
    |--------------------------------------------------------------------------
    |
    | A value listed in your server's IMGPROXY_TRUSTED_SIGNATURES. When set,
    | it is used as the signature instead of signing with the key and salt.
    |
    */

    'trusted_signature' => env('IMGPROXY_TRUSTED_SIGNATURE'),

    /*
    |--------------------------------------------------------------------------
    | Option Names
    |--------------------------------------------------------------------------
    |
    | Use imgproxy's short option names instead of the long names.
    |
    */

    'use_short_options' => (bool) env('IMGPROXY_SHORT_OPTIONS', false),

];
