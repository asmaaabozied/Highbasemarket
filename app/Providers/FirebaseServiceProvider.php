<?php

namespace App\Providers;

use App\Interfaces\PushNotifier;
use App\Services\FcmPushNotifier;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        $this->app->singleton(PushNotifier::class, function ($app): \App\Services\FcmPushNotifier {
            $credentialsPath = config('services.firebase.credentials');

            if (empty($credentialsPath)) {
                throw new InvalidArgumentException(__('Firebase credentials path is not defined or is empty in the configuration.'));
            }

            $factory = (new Factory)->withServiceAccount(base_path($credentialsPath));

            return new FcmPushNotifier($factory->createMessaging());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function provides(): array
    {
        return [PushNotifier::class];
    }
}
