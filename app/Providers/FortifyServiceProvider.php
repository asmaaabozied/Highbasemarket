<?php

namespace App\Providers;

use App\Actions\Custom\SyncReferrals;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request): RedirectResponse
            {
                $user = auth()->user();

                if (session('to_form')) {
                    return back()->with(['link' => route('form.create')]);
                }

                if ($user->isAdmin()) {
                    return back()->with(['link' => route('dashboard')]);
                }

                if ($user->accountType() === 'vendor') {
                    return $user->userable->redirect_to
                        ? back()->with(['link' => $user->userable->redirect_to])
                        : back()->with(['link' => route('dashboard')]);
                }

                $intended = session()->pull('url.intended');

                if ($intended) {
                    return redirect()->to($intended);
                }

                return back();
            }
        });

        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse
        {
            public function toResponse($request): RedirectResponse
            {
                if (session('to_form')) {
                    return back()->with(['link' => route('form.create')]);
                }

                return back()->with('success', 'Your account has been created successfully');
            }
        });

        $this->app->instance(VerifyEmailResponse::class, new class implements VerifyEmailResponse
        {
            public function toResponse($request): RedirectResponse
            {
                return to_route('home')
                    ->with('success', __('Your email has been verified successfully'));
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateThrough(fn (Request $request): array => array_filter([

            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,

            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,

            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,

            AttemptToAuthenticate::class,

            PrepareAuthenticatedSession::class,

            SyncReferrals::class,
        ]));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
