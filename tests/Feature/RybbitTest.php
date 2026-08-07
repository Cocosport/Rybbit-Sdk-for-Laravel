<?php

declare(strict_types=1);

use Cocosport\Rybbit\Rybbit;
use Cocosport\Rybbit\SendEvents;

it('exposes a fluent entry point for sending events', function () {
    expect(app(Rybbit::class)->track())->toBeInstanceOf(SendEvents::class);
});

it('returns a new instance on every call', function () {
    $rybbit = app(Rybbit::class);

    expect($rybbit->track())->not->toBe($rybbit->track());
});
