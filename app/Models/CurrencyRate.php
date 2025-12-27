<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
    ];

    // Convert amount from one currency to another
    public static function convert(float $amount, string $from, string $to, $date = null): float
    {
        if ($from === $to) {
            return $amount;
        }

        $date = $date ?? today();

        $rate = self::where('from_currency', $from)
            ->where('to_currency', $to)
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();

        if (!$rate) {
            // Try reverse conversion
            $reverseRate = self::where('from_currency', $to)
                ->where('to_currency', $from)
                ->where('effective_date', '<=', $date)
                ->orderBy('effective_date', 'desc')
                ->first();

            if ($reverseRate) {
                return $amount / $reverseRate->rate;
            }

            return $amount; // No conversion available
        }

        return $amount * $rate->rate;
    }
}
