<?php

namespace App\Console\Commands;

use App\Models\CollectionAction;
use App\Models\MessageTemplate;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPromiseReminders extends Command
{
    protected $signature = 'notifications:send-promises';
    protected $description = 'Envía recordatorios de promesas de pago que vencen mañana';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow();
        $this->info("Buscando promesas de pago para {$tomorrow->format('d/m/Y')}...");

        $promises = CollectionAction::where('result', 'promise_to_pay')
            ->whereDate('promise_date', $tomorrow)
            ->whereNotNull('promise_amount')
            ->with(['debt.debtor', 'debt.company'])
            ->get();

        if ($promises->isEmpty()) {
            $this->info('No hay promesas de pago para mañana.');
            return Command::SUCCESS;
        }

        $sent = 0;

        foreach ($promises as $promise) {
            $debt = $promise->debt;
            $debtor = $debt->debtor;
            $company = $debt->company;

            $template = MessageTemplate::where('company_id', $company->id)
                ->where('channel', 'whatsapp')
                ->where('type', 'promise_reminder')
                ->where('is_active', true)
                ->first();

            if (!$template || !$debtor->phone) continue;

            $message = $template->render([
                'nombre' => $debtor->full_name,
                'monto' => number_format($promise->promise_amount, 2),
                'fecha_promesa' => $promise->promise_date->format('d/m/Y'),
                'concepto' => $debt->concept,
                'empresa' => $company->trade_name ?? $company->business_name,
            ]);

            NotificationLog::create([
                'company_id' => $company->id,
                'debtor_id' => $debtor->id,
                'debt_id' => $debt->id,
                'channel' => 'whatsapp',
                'recipient' => $debtor->phone,
                'template' => $template->name,
                'message' => $message,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $sent++;
            $this->line("  ✓ {$debtor->full_name} - Promesa: S/{$promise->promise_amount}");
        }

        $this->info("Recordatorios de promesas enviados: {$sent}");

        return Command::SUCCESS;
    }
}
