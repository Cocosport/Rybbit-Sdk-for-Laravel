<?php

namespace Cocosport\Rybbit\Exceptions;

use Exception;

class InvalidConfigurationException extends Exception
{
    public static function missingHost(): self
    {
        return new self('The "rybbit.host" configuration value is missing. Set RYBBIT_HOST in your .env file.');
    }

    public static function invalidHost(string $host): self
    {
        return new self("The \"rybbit.host\" configuration value [$host] is not a valid URL.");
    }

    public static function missingSiteSeqId(): self
    {
        return new self('The "rybbit.site_seq_id" configuration value is missing. Set RYBBIT_SITE_SEQ_ID in your .env file.');
    }
}
