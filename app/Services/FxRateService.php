<?php

namespace App\Services;

use App\Enum\CurrencyEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

readonly class FxRateService
{
    public function __construct(private string $baseUrl, private ?int $ttl = 3600) {}

    public function convert(float $amount, CurrencyEnum $from, CurrencyEnum $to = CurrencyEnum::USD): float
    {
        if ($from == $to) {
            return $amount;
        }

        $rate = $this->getRate($from, $to);

        return round($amount * $rate, 2);
    }

    public function getRate(CurrencyEnum|string $from, CurrencyEnum|string $to = CurrencyEnum::USD): float
    {
        $fromValue = $from instanceof CurrencyEnum ? $from->value : $from;
        $toValue   = $to instanceof CurrencyEnum ? $to->value : $to;

        if ($fromValue === $toValue) {
            return 1.0;
        }

        $cacheKey = "fx_rate_{$fromValue}_to_{$toValue}";

        return Cache::remember($cacheKey, $this->ttl, function () use ($fromValue, $toValue) {
            $url = "{$this->baseUrl}/{$fromValue}";

            $response = Http::get($url);

            if (! $response->successful()) {
                Log::warning('Exchange rate API failed', [
                    'from'   => $fromValue,
                    'to'     => $toValue,
                    'url'    => $url,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return $this->fallbackRate(
                    CurrencyEnum::tryFrom($fromValue),
                    CurrencyEnum::tryFrom($toValue)
                );
            }

            $data = $response->json();

            return $data['rates'][$toValue]
                ?? $this->fallbackRate(
                    CurrencyEnum::tryFrom($fromValue),
                    CurrencyEnum::tryFrom($toValue)
                );
        });
    }

    private function fallbackRate(CurrencyEnum $from, CurrencyEnum $to): float
    {
        $rates = config('fallback_fx_rates');

        return $rates[$from->value][$to->value] ?? 1.0;
    }
}
