<?php

declare(strict_types=1);

use Rybbit\Rybbit\Rybbit;

it('resolves the singleton', function () {
    expect(app(Rybbit::class))->toBeInstanceOf(Rybbit::class);
});

it('returns the same instance from the container', function () {
    expect(app(Rybbit::class))->toBe(app(Rybbit::class));
});

it('merges the package config', function () {
    expect(config('rybbit-sdk-for-laravel.placeholder'))->toBe('default');
});
