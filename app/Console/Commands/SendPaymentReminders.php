<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Installment;
use App\Models\MessageTemplate;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'notifications:send-reminders 
                            {--days=3 : Días antes del vencimiento para enviar recordatorio}
                            {--channel=whatsapp : Canal de notificación (whatsapp, email, sms)}';

    protected $description = 'Envía recordatorios automáticos de pago para cuotas próximas a vencer';

    public function handle()
    {
        $days = (int) $this->option('days');
        $channel = $this->option('channel');
        $targetDate = Carbon::today()->addDays($days);

        $this->info("Buscando cuotas que vencen el {$targetDate->format('d/m/Y')} ({$days} días)...");

        $installments = Installment::where('status', 'pending')
            ->whereDate('due_date', $targetDate)
            ->with(['debt.debtor', 'debt.company'])
            ->get();

        if ($installments->isEmpty()) {
            $this->info('No hay cuotas próximas a vencer para esta fecha.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($installments as $installment) {
            $debt = $installment->debt;
            $debtor = $debt->debtor;
            $company = $debt->company;

            // Buscar template de recordatorio
            $template = MessageTemplate::where('company_id', $company->id)
                ->where('channel', $channel)
                ->where('type', 'payment_reminder')
                ->where('is_active', true)
                ->first();

            if (!$template) continue;

            $recipient = match ($channel) {
                'whatsapp', 'sms' => $debtor->phone,
                'email' => $debtor->email,
                default => null,
            };

            if (!$recipient) continue;

            // Verificar que no se haya enviado hoy ya
            $alreadySent = NotificationLog::where('debtor_id', $debtor->id)
                ->where('debt_id', $debt->id)
                ->where('channel', $channel)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($alreadySent) continue;

            $message = $template->render([
                'nombre' => $debtor->full_name,
                'monto' => number_format($installment->total_amount, 2),
                'fecha_vencimiento' => $installment->due_date->format('d/m/Y'),
                'cuota' => $installment->number,
                'concepto' => $debt->concept,
                'saldo' => number_format($debt->pending_amount, 2),
                'empresa' => $company->trade_name ?? $company->business_name,
            ]);

            try {
                // TODO: Integrar con API real de WhatsApp/Email
                // Por ahora registramos como enviado
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
                $this->line("  ✓ {$debtor->full_name} - Cuota #{$installment->number} - S/{$installment->total_amount}");
            } catch (\Exception $e) {
                NotificationLog::create([
                    'company_id' => $company->id,
                    'debtor_id' => $debtor->id,
                    'debt_id' => $debt->id,
                    'channel' => $channel,
                    'recipient' => $recipient,
                    'template' => $template->name,
                    'message' => $message,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                $failed++;
                $this->error("  ✗ {$debtor->full_name} - Error: {$e->getMessage()}");
            }
        }

        $this->info("Recordatorios enviados: {$sent} | Fallidos: {$failed}");

        return Command::SUCCESS;
    }
}
