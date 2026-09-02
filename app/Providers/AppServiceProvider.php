<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Vercel/FrankenPHP may terminate HTTPS before Laravel sees the request.
        // Force Laravel-generated URLs/forms to use HTTPS in production.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // @active('route.name') blade directive for nav highlighting
        Blade::directive('active', function (string $expression) {
            return "<?php echo request()->routeIs({$expression}) ? 'active' : ''; ?>";
        });
    }
}
