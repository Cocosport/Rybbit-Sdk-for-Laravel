<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Rybbit Host
    |--------------------------------------------------------------------------
    |
    | The URL of the Rybbit instance your analytics data is sent to. The
    | Client uses this as the base URL for its server-side API requests
    | (tracking events, fetching stats, ...), the tunnel forwards requests
    | to it, and it's what the @rybbit Blade directive points the tracking
    | script at. Leave this as the default if you use Rybbit Cloud, or
    | point it at your own instance if you self-host.
    |
    */

    'host' => env('RYBBIT_HOST', 'https://app.rybbit.io'),

    /*
    |--------------------------------------------------------------------------
    | Site ID
    |--------------------------------------------------------------------------
    |
    | The public ID of the site you created in your Rybbit dashboard. This
    | is the value injected into the tracking script by the @rybbit Blade
    | directive, and the one the browser sends back through the tunnel.
    |
    */

    'site_id' => env('RYBBIT_SITE_ID'),

    /*
    |--------------------------------------------------------------------------
    | Site Sequence ID
    |--------------------------------------------------------------------------
    |
    | The internal, numeric ID Rybbit assigns your site, as opposed to the
    | public site_id above. The Client sends this to identify the site on
    | authenticated server-side API calls, such as Rybbit::track() or
    | Rybbit::identify() made from your backend rather than the browser.
    |
    */

    'site_seq_id' => env('RYBBIT_SITE_SEQ_ID', '1'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Rybbit API key. The Client sends this as a bearer token to
    | authenticate the server-side requests it makes to the Rybbit API, so
    | it's required for any tracking done outside of the browser.
    |
    */

    'api_key' => env('RYBBIT_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | Controls how Rybbit::track()->send() resolves user_id from the
    | authenticated user. Leave both unset to use the default guard's
    | getAuthIdentifier() (the primary key).
    |
    */

    'user' => [

        /*
        |----------------------------------------------------------------------
        | Guard
        |----------------------------------------------------------------------
        |
        | The auth guard to resolve the authenticated user from. Leave unset
        | to use the application's default guard.
        |
        */

        'guard' => env('RYBBIT_USER_GUARD'),

        /*
        |----------------------------------------------------------------------
        | Key
        |----------------------------------------------------------------------
        |
        | The attribute or method to read user_id from, e.g. "uuid" or
        | "publicKey". Leave unset to use the user's getAuthIdentifier().
        |
        */

        'key' => env('RYBBIT_USER_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Script
    |--------------------------------------------------------------------------
    |
    | Extra data-* attributes the @rybbit Blade directive adds to the
    | tracking script tag. Leave a value unset (null / empty array) to omit
    | its attribute entirely and let Rybbit fall back to its own default.
    |
    | @see https://rybbit.com/docs/script
    |
    */

    'script' => [
        'debounce' => env('RYBBIT_SCRIPT_DEBOUNCE'),
        'skip_patterns' => [],
        'mask_patterns' => [],
        'tag' => env('RYBBIT_SCRIPT_TAG'),

        'replay' => [
            'mask_text_selectors' => [],
            'block_class' => env('RYBBIT_REPLAY_BLOCK_CLASS'),
            'block_selector' => env('RYBBIT_REPLAY_BLOCK_SELECTOR'),
            'ignore_class' => env('RYBBIT_REPLAY_IGNORE_CLASS'),
            'ignore_selector' => env('RYBBIT_REPLAY_IGNORE_SELECTOR'),
            'mask_text_class' => env('RYBBIT_REPLAY_MASK_TEXT_CLASS'),
            'mask_all_inputs' => env('RYBBIT_REPLAY_MASK_ALL_INPUTS'),
            'mask_input_options' => [],
            'collect_fonts' => env('RYBBIT_REPLAY_COLLECT_FONTS'),
            'sampling' => [],
            'slim_dom_options' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tunnel
    |--------------------------------------------------------------------------
    |
    | The tunnel exposes routes on your own domain that proxy requests
    | through to Rybbit, so the tracking script talks to your application
    | instead of Rybbit directly. This keeps it from being blocked by ad
    | blockers and browser tracking protections.
    |
    */

    'tunnel' => [

        /*
        |----------------------------------------------------------------------
        | Enable Tunnel
        |----------------------------------------------------------------------
        |
        | Set to false to stop registering the tunnel routes. The @rybbit
        | Blade directive falls back to pointing the tracking script
        | straight at "host" instead of the tunnel url.
        |
        */

        'enabled' => env('RYBBIT_TUNNEL_ENABLED', true),

        /*
        |----------------------------------------------------------------------
        | Tunnel URL
        |----------------------------------------------------------------------
        |
        | The path prefix your application exposes the tunnel routes under,
        | e.g. GET {url}/script.js and POST {url}/track. Point your tracking
        | script and the @rybbit directive at this same value.
        |
        */

        'url' => env('RYBBIT_TUNNEL_URL', '/rybbit'),

        /*
        |----------------------------------------------------------------------
        | Cache Key Prefix
        |----------------------------------------------------------------------
        |
        | Prefix used for the cache keys the tunnel stores the proxied
        | tracking script and site config responses under. Change this if
        | it collides with another key in your cache store.
        |
        */

        'cache-key-prefix' => env('RYBBIT_CACHE_KEY_PREFIX', 'rybbit_'),

        /*
        |----------------------------------------------------------------------
        | Middleware
        |----------------------------------------------------------------------
        |
        | Middleware applied to the tunnel route group.
        |
        */

        'middleware' => [
            'web',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, failed requests to Rybbit — both the ones proxied
    | synchronously through the tunnel and the ones sent from the queued
    | job — are written to your application's log so you can spot delivery
    | problems.
    |
    */

    'logs' => env('RYBBIT_LOGS', true),

    /*
    |--------------------------------------------------------------------------
    | Throw On Error
    |--------------------------------------------------------------------------
    |
    | When enabled, a failed response from Rybbit raises an
    | Illuminate\Http\Client\RequestException instead of failing silently.
    | Handy while developing; you'll usually want this off in production so
    | a Rybbit outage never breaks your application.
    |
    */

    'throw_on_error' => env('RYBBIT_THROW_ON_ERROR', false),

    /*
    |--------------------------------------------------------------------------
    | Queue Connection
    |--------------------------------------------------------------------------
    |
    | The queue used to dispatch data forwarded to Rybbit asynchronously
    | (currently, session replay chunks). Point this at a dedicated queue
    | if you want to keep Rybbit traffic separate from your app's own jobs.
    |
    */

    'queue' => env('RYBBIT_QUEUE', 'default'),

];
