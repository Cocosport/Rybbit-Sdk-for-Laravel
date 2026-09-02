<?php

declare(strict_types=1);

namespace Cocosport\Rybbit\Enums;

/**
 * Time bucket granularity for Rybbit's time-series endpoints.
 */
enum TimeBucket: string
{
    case Minute = 'minute';
    case FiveMinutes = 'five_minutes';
    case TenMinutes = 'ten_minutes';
    case FifteenMinutes = 'fifteen_minutes';
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
