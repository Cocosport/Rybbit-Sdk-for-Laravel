<?php

declare(strict_types=1);

namespace Rybbit\Rybbit\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rybbit\Rybbit\RybbitServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            RybbitServiceProvider::class,
        ];
    }
}
