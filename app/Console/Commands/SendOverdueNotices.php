<?php

namespace App\Console\Commands;

use App\Models\Debt;
use App\Models\MessageTemplate;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendOverdueNotices extends Command
{
    protected $signature = 'notifications:send-overdue 
                            {--min-days=7 : Mínimo de días de mora para notificar}
                            {--channel=whatsapp : Canal de notificación}';

    protected $description = 'Envía avisos de mora a deudores con cuotas vencidas';

    public function handle()
    {
        $minDays = (int) $this->option('min-days');
        $channel = $this->option('channel');

        $this->info("Buscando deudas con más de {$minDays} días de mora...");

        $debts = Debt::where('status', 'overdue')
            ->where('days_overdue', '>=', $minDays)
            ->where('pending_amount', '>', 0)
            ->with(['debtor', 'company'])
            ->get();

        if ($debts->isEmpty()) {
            $this->info('No hay deudas vencidas que cumplan el criterio.');
            return Command::SUCCESS;
        }

        $sent = 0;

        foreach ($debts as $debt) {
            $debtor = $debt->debtor;
            $company = $debt->company;

            // Buscar template de mora
            $template = MessageTemplate::where('company_id', $company->id)
                ->where('channel', $channel)
                ->where('type', 'overdue_notice')
                ->where('is_active', true)
                ->first();

            if (!$template) continue;

            $recipient = match ($channel) {
                'whatsapp', 'sms' => $debtor->phone,
                'email' => $debtor->email,
                default => null,
            };

            if (!$recipient) continue;

            // No enviar más de 1 aviso de mora por semana por deuda
            $recentNotice = NotificationLog::where('debtor_id', $debtor->id)
                ->where('debt_id', $debt->id)
                ->where('channel', $channel)
                ->where('template', 'like', '%mora%')
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->exists();

            if ($recentNotice) continue;

            $message = $template->render([
                'nombre' => $debtor->full_name,
                'monto' => number_format($debt->pending_amount, 2),
                'fecha_vencimiento' => $debt->due_date->format('d/m/Y'),
                'dias_mora' => $debt->days_overdue,
                'concepto' => $debt->concept,
                'empresa' => $company->trade_name ?? $company->business_name,
            ]);

            NotificationLog::create([
                'company_id' => $company->id,
                'debtor_id' => $debtor->id,
                'debt_id' => $debt->id,
                'channel' => $channel,
                'recipient' => $recipient,
                'template' => $template->name,
                'message' => $message,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $sent++;
            $this->line("  ✓ {$debtor->full_name} - {$debt->code} - {$debt->days_overdue} días mora");
        }

        $this->info("Avisos de mora enviados: {$sent}");

        return Command::SUCCESS;
    }
}
