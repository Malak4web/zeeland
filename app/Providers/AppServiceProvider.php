<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\Money;
use App\Support\Vite;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Behind a proxy or a shared host, generated URLs must not fall back
        // to http — a mixed canonical is an SEO own-goal.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination');

        /** @see App\Support\Vite — plain manifest reader, no Vite plugin. */
        Blade::directive('vite', function (string $expression) {
            return "<?php echo \\App\\Support\\Vite::tags({$expression}); ?>";
        });

        /** Money, always Latin digits and two decimals. `@money($order->total)` */
        Blade::directive('money', function (string $expression) {
            return "<?php echo e(\\App\\Support\\Money::format({$expression})); ?>";
        });

        Blade::directive('shortMoney', function (string $expression) {
            return "<?php echo e(\\App\\Support\\Money::short({$expression})); ?>";
        });

        // Settings are read on nearly every request; one query, cached forever,
        // invalidated by Setting::put().
        view()->composer('*', function ($view) {
            $view->with('settings', Setting::all_())
                ->with('currency', Money::currency());
        });
    }
}
