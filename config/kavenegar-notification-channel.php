<?php

return [
    'api_key' => env('KAVENEGAR_API_KEY'),
    'from' => env('KAVENEGAR_FROM'), // optional

    /**
     * Specify a number where all messages should be routed. This can be used in development/staging environments
     * for testing.
     */
    'debug_to' => env('KAVENEGAR_DEBUG_TO'),

    /**
     * If an exception is thrown with one of these error codes, it will be caught & suppressed.
     * Specify '*' in the array, which will cause all exceptions to be suppressed.
     * Suppressed errors will not be logged or reported, but the `NotificationFailed` event will be emitted.
     *
     * @see https://kavenegar.com/rest.html
     */
    'ignored_error_codes' => ['*'],
];
