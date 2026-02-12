<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanOldNotifications extends Command
{
    protected $signature = 'notifications:clean {--months=6 : Meses de antigüedad para eliminar}';
    protected $description = 'Elimina registros de notificaciones antiguas';

    public function handle()
    {
        $months = (int) $this->option('months');
        $cutoff = Carbon::now()->subMonths($months);

        $deleted = NotificationLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Se eliminaron {$deleted} registros de notificaciones anteriores a {$cutoff->format('d/m/Y')}");

        return Command::SUCCESS;
    }
}
