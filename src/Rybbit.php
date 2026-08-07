<?php

declare(strict_types=1);

namespace Cocosport\Rybbit;

use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class Rybbit
{
    /**
     * Start sending a tracking event to Rybbit's /api/track endpoint.
     */
    public function track(): SendEvents
    {
        return app(SendEvents::class);
    }
}
