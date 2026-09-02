<?php

declare(strict_types=1);

namespace Cocosport\Rybbit\Enums;

/**
 * Fields Rybbit's users list can be sorted by.
 */
enum UserSortBy: string
{
    case FirstSeen = 'first_seen';
    case LastSeen = 'last_seen';
    case Pageviews = 'pageviews';
    case Sessions = 'sessions';
    case Events = 'events';
}
