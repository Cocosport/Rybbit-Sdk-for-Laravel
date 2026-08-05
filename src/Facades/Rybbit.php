<?php

declare(strict_types=1);

namespace Rybbit\Rybbit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rybbit\Rybbit\Rybbit
 */
class Rybbit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Rybbit\Rybbit\Rybbit::class;
    }
}
