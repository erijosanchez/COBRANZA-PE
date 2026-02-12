<?php

use Illuminate\Support\Facades\Schedule;

// Actualizar morosidad todos los días a las 6:00 AM
Schedule::command('debts:update-overdue')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/overdue.log'));

// Recordatorios de pago - 3 días antes del vencimiento - 8:00 AM
Schedule::command('notifications:send-reminders --days=3 --channel=whatsapp')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reminders.log'));

// Recordatorios de pago - 1 día antes del vencimiento - 9:00 AM
Schedule::command('notifications:send-reminders --days=1 --channel=whatsapp')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reminders.log'));

// Avisos de mora semanales - Lunes 8:00 AM
Schedule::command('notifications:send-overdue --min-days=7 --channel=whatsapp')
    ->weeklyOn(1, '08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/overdue-notices.log'));

// Recordatorio de promesas de pago - todos los días 7:00 PM
Schedule::command('notifications:send-promises')
    ->dailyAt('19:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/promises.log'));

// Reporte diario - Lunes a Viernes 6:00 PM
Schedule::command('reports:daily')
    ->weekdays()
    ->at('18:00')
    ->appendOutputTo(storage_path('logs/daily-report.log'));

// Limpiar notificaciones antiguas - 1er día del mes
Schedule::command('notifications:clean --months=6')
    ->monthlyOn(1, '03:00')
    ->appendOutputTo(storage_path('logs/cleanup.log'));
