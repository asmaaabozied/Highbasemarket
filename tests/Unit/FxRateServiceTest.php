<?php

use App\Enum\CurrencyEnum;
use App\Services\FxRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

describe('FxRateService', function () {

    beforeEach(function () {
        Cache::flush();
        Http::preventStrayRequests();
        $this->baseUrl = 'https://fake-api.test';
        $this->service = new FxRateService($this->baseUrl);
    });

    it('returns same amount if currencies match', function () {
        $result = $this->service->convert(100, CurrencyEnum::USD, CurrencyEnum::USD);

        expect($result)->toBe(100.0);
    });

    it('fetches rate from API and converts amount', function () {
        Http::fake([
            "{$this->baseUrl}/USD" => Http::response([
                'rates' => ['EUR' => 0.9],
            ], 200),
        ]);

        $result = $this->service->convert(100, CurrencyEnum::USD, CurrencyEnum::EUR);

        expect($result)->toBe(90.0);
    });

    it('uses cache after first request', function () {
        Http::fake([
            "{$this->baseUrl}/USD" => Http::response([
                'rates' => ['EUR' => 0.5],
            ], 200),
        ]);

        $first = $this->service->getRate(CurrencyEnum::USD, CurrencyEnum::EUR);

        // Change fake (should not matter due to cache)
        Http::fake([
            "{$this->baseUrl}/USD" => Http::response([
                'rates' => ['EUR' => 0.9],
            ], 200),
        ]);

        $second = $this->service->getRate(CurrencyEnum::USD, CurrencyEnum::EUR);

        expect($second)->toBe($first);
    });

    it('falls back to config when API fails', function () {
        Http::fake([
            "{$this->baseUrl}/*" => Http::response(null, 500),
        ]);

        config()->set('fallback_fx_rates', [
            CurrencyEnum::USD->value => [CurrencyEnum::EUR->value => 0.8],
        ]);

        $rate = $this->service->getRate(CurrencyEnum::USD, CurrencyEnum::EUR);

        expect($rate)->toBe(0.8);
    });

    it('returns 1.0 if no fallback exists', function () {
        Http::fake([
            "{$this->baseUrl}/*" => Http::response(null, 500),
        ]);

        config()->set('fallback_fx_rates', []);

        $rate = $this->service->getRate(CurrencyEnum::USD, CurrencyEnum::EUR);

        expect($rate)->toBe(1.0);
    });

    it('logs a warning when API fails', function () {
        Log::spy();

        Http::fake([
            "{$this->baseUrl}/*" => Http::response(null, 500),
        ]);

        $this->service->getRate(CurrencyEnum::USD, CurrencyEnum::EUR);

        Log::shouldHaveReceived('warning')->once();
    });
})->assignee('Waheed Sindhani');
