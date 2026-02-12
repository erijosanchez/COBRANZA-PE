<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function formatPeru(?string $date): string
    {
        if (!$date) return '-';
        return Carbon::parse($date)->format('d/m/Y');
    }

    public static function formatDateTime(?string $date): string
    {
        if (!$date) return '-';
        return Carbon::parse($date)->format('d/m/Y H:i');
    }

    public static function diffForHumans(?string $date): string
    {
        if (!$date) return '-';
        return Carbon::parse($date)->diffForHumans();
    }

    public static function isOverdue(string $date): bool
    {
        return Carbon::parse($date)->lt(Carbon::today());
    }
}
