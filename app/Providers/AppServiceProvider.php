<?php
namespace App\Providers;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void {
        Gate::define('manage-portal', fn ($user): bool => (bool) $user->is_admin);
        RateLimiter::for('portal-login', fn (Request $request) => [
            Limit::perMinute(5)->by('login-ip:'.$request->ip()),
            Limit::perMinute(5)->by('login-email:'.hash('sha256', strtolower(is_string($request->input('email')) ? $request->input('email') : '').'|'.$request->ip())),
        ]);
        View::composer(['layouts.app', 'contacts'], fn ($view) => $view->with('portalSettings', Setting::pluck('value', 'key')->all()));
    }
}
