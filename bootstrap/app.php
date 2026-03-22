<?php

use App\Http\Middleware\AccountMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DynamicSignatureCheck;
use App\Http\Middleware\FollowUpMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\InvitationMiddleware;
use App\Http\Middleware\LocationRegistererMiddleware;
use App\Http\Middleware\MarketMiddleware;
use App\Http\Middleware\NotDisabledMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Validation\ValidationException;
use Laravel\Telescope\Telescope;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            LocationRegistererMiddleware::class,
        ])
            ->web(append: [
                LocationRegistererMiddleware::class,
                MarketMiddleware::class,
                \App\Http\Middleware\LangMiddleware::class,
                HandleInertiaRequests::class,
                AddLinkHeadersForPreloadedAssets::class,
                //            \App\Http\Middleware\TrustProxies::class,

            ])->alias([
                'follow-up'    => FollowUpMiddleware::class,
                'admin'        => AdminMiddleware::class,
                'account'      => AccountMiddleware::class,
                'invite'       => InvitationMiddleware::class,
                'not-disabled' => NotDisabledMiddleware::class,
                'signature'    => DynamicSignatureCheck::class,
            ]);

        $middleware->validateCsrfTokens(except: [
            'chunk-upload',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Exception $exception) {
            if (in_array(app()->environment(), ['local', 'development'])) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return null;
            }

            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException && (bool) request()->header('x-inertia')) {
                $message = $exception->getMessage() ?: 'An error occurred';

                $logout = false;

                if ($exception->getMessage() === 'Your branch is disabled. Please contact the administrator.') {
                    $logout = true;
                }

                return response()->json([
                    'message'     => $message,
                    'status_code' => $exception->getStatusCode(),
                    'logout'      => $logout,
                ], $exception->getStatusCode());
            }

            Telescope::catch($exception);

            if (request()->header('x-inertia') && in_array(request()->method(), ['POST', 'PUT', 'DELETE'])) {
                return response()->json([
                    'message'     => __('The request data was invalid. Please check and try again.'),
                    'status_code' => 422,
                ], 422);
            }

            if (request()->header('x-inertia') && in_array(request()->method(), ['GET', 'HEAD', 'OPTIONS'])) {
                return response()->json([
                    'message'     => __('The resource you are looking for is not available. Please check and try again.'),
                    'status_code' => 422,
                ], 422);
            }
        });
    })
//    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
//        $schedule->job(new CompleteBranchProfileJob)->dailyAt('06:00');
//    })
    ->create();
