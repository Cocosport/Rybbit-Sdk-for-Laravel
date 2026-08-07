<?php

declare(strict_types=1);

namespace Cocosport\Rybbit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cocosport\Rybbit\Rybbit
 */
class Rybbit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cocosport\Rybbit\Rybbit::class;
    }
}
