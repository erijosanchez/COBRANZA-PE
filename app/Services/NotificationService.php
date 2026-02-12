<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\MessageTemplate;
use App\Models\Debtor;
use App\Models\Debt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendWhatsApp(string $phone, string $message, array $context = []): bool
    {
        $phone = $this->formatPhone($phone);

        // Opción 1: Meta WhatsApp Business API
        $apiUrl = config('services.whatsapp.api_url');
        $token = config('services.whatsapp.token');

        if (!$apiUrl || !$token) {
            Log::warning('WhatsApp API no configurada. Mensaje simulado.', [
                'phone' => $phone,
                'message' => $message,
            ]);
            return true; // Simular envío
        }

        try {
            $response = Http::withToken($token)->post($apiUrl, [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Error enviando WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    public function sendEmail(string $email, string $subject, string $body): bool
    {
        try {
            Mail::raw($body, function ($msg) use ($email, $subject) {
                $msg->to($email)->subject($subject);
            });
            return true;
        } catch (\Exception $e) {
            Log::error('Error enviando email: ' . $e->getMessage());
            return false;
        }
    }

    public function sendNotification(
        Debtor $debtor,
        string $channel,
        string $message,
        int $companyId,
        ?int $debtId = null,
        ?string $templateName = null
    ): NotificationLog {
        $recipient = match ($channel) {
            'whatsapp', 'sms' => $debtor->phone,
            'email' => $debtor->email,
            default => null,
        };

        $status = 'failed';
        $errorMessage = null;

        if (!$recipient) {
            $errorMessage = "El deudor no tiene {$channel} registrado.";
        } else {
            try {
                $sent = match ($channel) {
                    'whatsapp' => $this->sendWhatsApp($recipient, $message),
                    'email' => $this->sendEmail($recipient, 'Notificación de Cobranza', $message),
                    default => false,
                };
                $status = $sent ? 'sent' : 'failed';
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        return NotificationLog::create([
            'company_id' => $companyId,
            'debtor_id' => $debtor->id,
            'debt_id' => $debtId,
            'channel' => $channel,
            'recipient' => $recipient ?? 'N/A',
            'template' => $templateName,
            'message' => $message,
            'status' => $status,
            'error_message' => $errorMessage,
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }

    public function renderTemplate(MessageTemplate $template, Debtor $debtor, ?Debt $debt = null, ?string $companyName = null): string
    {
        $variables = [
            'nombre' => $debtor->full_name,
            'empresa' => $companyName ?? '',
        ];

        if ($debt) {
            $variables = array_merge($variables, [
                'monto' => number_format($debt->pending_amount, 2),
                'fecha_vencimiento' => $debt->due_date->format('d/m/Y'),
                'dias_mora' => $debt->days_overdue,
                'concepto' => $debt->concept,
                'saldo' => number_format($debt->pending_amount, 2),
                'codigo' => $debt->code,
            ]);
        }

        return $template->render($variables);
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Si es número peruano sin código de país
        if (strlen($phone) === 9 && str_starts_with($phone, '9')) {
            $phone = '51' . $phone;
        }

        return $phone;
    }
}
