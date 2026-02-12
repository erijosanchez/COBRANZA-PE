<?php

namespace App\Providers;

use App\Services\DebtService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\ReportService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DebtService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(ReportService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Blade directives personalizadas
        Blade::directive('money', function ($expression) {
            return "<?php echo \App\Helpers\MoneyHelper::format($expression); ?>";
        });

        Blade::directive('datePeru', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::formatPeru($expression); ?>";
        });
    }
}
