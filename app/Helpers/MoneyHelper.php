<?php

namespace App\Helpers;

class MoneyHelper
{
    public static function format(float $amount, string $currency = 'PEN'): string
    {
        $symbol = match ($currency) {
            'PEN' => 'S/',
            'USD' => '$',
            default => $currency,
        };

        return $symbol . ' ' . number_format($amount, 2);
    }

    public static function toWords(float $amount, string $currency = 'PEN'): string
    {
        $entero = (int) $amount;
        $decimal = round(($amount - $entero) * 100);

        $currencyName = match ($currency) {
            'PEN' => 'soles',
            'USD' => 'dólares',
            default => $currency,
        };

        $formatter = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);
        $words = ucfirst($formatter->format($entero));

        return "{$words} con {$decimal}/100 {$currencyName}";
    }
}
