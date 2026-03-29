<?php

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppClientServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WhatsAppCloudApi::class, function ($app): \Netflie\WhatsAppCloudApi\WhatsAppCloudApi {
            $token     = config('services.whatsapp.token');
            $accountId = config('services.whatsapp.account_id');
            $phoneId   = config('services.whatsapp.phone_id');

            if (empty($token) || empty($accountId) || empty($phoneId)) {
                throw new InvalidArgumentException('WhatsApp configuration values are not properly defined.');
            }

            return new WhatsAppCloudApi(
                config: [
                    'access_token'         => $token,
                    'business_id'          => $accountId,
                    'from_phone_number_id' => $phoneId,
                ]
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [WhatsAppCloudApi::class];
    }
}
