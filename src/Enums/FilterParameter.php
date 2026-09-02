<?php

declare(strict_types=1);

namespace Cocosport\Rybbit\Enums;

/**
 * Dimensions Rybbit's Stats API can break traffic down by or filter on.
 *
 * @see https://rybbit.com/docs/api/getting-started
 */
enum FilterParameter: string
{
    // Browser & device
    case Browser = 'browser';
    case BrowserVersion = 'browser_version';
    case OperatingSystem = 'operating_system';
    case OperatingSystemVersion = 'operating_system_version';
    case DeviceType = 'device_type';
    case Dimensions = 'dimensions';
    case Language = 'language';

    // Location
    case Country = 'country';
    case Region = 'region';
    case City = 'city';
    case Timezone = 'timezone';
    case Latitude = 'lat';
    case Longitude = 'lon';

    // Page & traffic
    case Pathname = 'pathname';
    case PageTitle = 'page_title';
    case Hostname = 'hostname';
    case Querystring = 'querystring';
    case Referrer = 'referrer';
    case EntryPage = 'entry_page';
    case ExitPage = 'exit_page';
    case Channel = 'channel';

    // UTM
    case UtmSource = 'utm_source';
    case UtmMedium = 'utm_medium';
    case UtmCampaign = 'utm_campaign';
    case UtmTerm = 'utm_term';
    case UtmContent = 'utm_content';

    // User & events
    case UserId = 'user_id';
    case EventName = 'event_name';
}
