<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Schema;
use Spatie\Permission\Models\Role;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return env('FRONTEND_URL', 'http://localhost:3000') . "/reset-password?token=$token&email=" . urlencode($user->email);
        });
        if (Schema::hasTable('roles') && app()->environment(['local', 'testing'])) {
            Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        }
    }
}
