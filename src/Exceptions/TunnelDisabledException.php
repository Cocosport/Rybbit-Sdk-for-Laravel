<?php

namespace Cocosport\Rybbit\Exceptions;

use Exception;
use Throwable;

class TunnelDisabledException extends Exception
{
    public function __construct(
        string $message = 'Rybbit tunnel is disabled. Enable it in the configuration file.',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
