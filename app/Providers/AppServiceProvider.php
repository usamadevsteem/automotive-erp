<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // @active('route.name') blade directive for nav highlighting
        Blade::directive('active', function (string $expression) {
            return "<?php echo request()->routeIs({$expression}) ? 'active' : ''; ?>";
        });
    }
}
