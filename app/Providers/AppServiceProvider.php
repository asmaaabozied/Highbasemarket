<?php

namespace App\Providers;

use App\Interfaces\WhatsAppMessenger;
use App\Services\FxRateService;
use App\Services\WhatsAppService;
use App\Support\RemoteVite;
use Exception;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Vite as ViteFacade;
use Illuminate\Support\ServiceProvider;
use PostHog\PostHog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (loadViteAsset()) {
            $this->app->singleton(Vite::class, fn (): \App\Support\RemoteVite => new RemoteVite);
        }

        $this->app->bind(WhatsAppMessenger::class, WhatsAppService::class);
        $this->app->bind(FxRateService::class, function ($app): \App\Services\FxRateService {
            $baseUrl = config('services.exchange_rate.base_url');
            $baseUrl .= '/'.config('services.exchange_rate.api_key').'/latest';

            return new FxRateService(baseUrl: $baseUrl);
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ViteFacade::prefetch(concurrency: 3);

        try {
            if (config('app.env') === 'production') {
                PostHog::init(config('services.posthog.api_key'), [
                    'host' => config('services.posthog.host'),
                ]);
            }
        } catch (Exception $e) {
            logger()->error($e->getMessage());
        }
    }
}
