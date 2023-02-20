<?php

namespace Solutionplus\MicroService\Helpers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

class Tz extends Carbon
{
    public static function createFromClient(string $date, string $clientTimezone = null)
    {
        $timezone = $clientTimezone ?? request()->header(config('app.client_timezone_request_header_name'));
        return CarbonImmutable::parse($date)->shiftTimezone($timezone)->setTimezone(config('app.timezone'));
    }

    public static function createFromServer(string $date, string $clientTimezone = null)
    {
        $timezone = $clientTimezone ?? request()->header(config('app.client_timezone_request_header_name'));
        if ($timezone != null) config(['app.timezone' => $timezone]);
        return CarbonImmutable::parse($date)->setTimezone($timezone);
    }

    public static function carbonDate($date = null)
    {
        if ($date == null) return null;
        return $date instanceof Carbon ? $date : Carbon::parse($date);
    }
}
