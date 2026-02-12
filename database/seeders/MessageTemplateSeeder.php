<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        $templates = [
            [
                'name' => 'Recordatorio de pago',
                'channel' => 'whatsapp',
                'type' => 'payment_reminder',
                'body' => 'Estimado/a {nombre}, le recordamos que su cuota de {monto} vence el {fecha_vencimiento}. Puede realizar su pago por Yape, Plin o transferencia. Gracias. - {empresa}',
            ],
            [
                'name' => 'Aviso de mora',
                'channel' => 'whatsapp',
                'type' => 'overdue_notice',
                'body' => 'Estimado/a {nombre}, su cuota de {monto} se encuentra vencida desde el {fecha_vencimiento}. Le solicitamos regularizar su pago a la brevedad para evitar intereses moratorios. - {empresa}',
            ],
            [
                'name' => 'Confirmación de pago',
                'channel' => 'whatsapp',
                'type' => 'payment_confirmation',
                'body' => 'Estimado/a {nombre}, hemos registrado su pago de {monto} correspondiente a {concepto}. Saldo pendiente: {saldo}. Gracias por su pago. - {empresa}',
            ],
            [
                'name' => 'Recordatorio de promesa',
                'channel' => 'whatsapp',
                'type' => 'promise_reminder',
                'body' => 'Estimado/a {nombre}, le recordamos su compromiso de pago de {monto} para el día {fecha_promesa}. Quedamos atentos. - {empresa}',
            ],
            [
                'name' => 'Aviso legal',
                'channel' => 'email',
                'type' => 'legal_warning',
                'subject' => 'AVISO IMPORTANTE - Deuda pendiente',
                'body' => 'Estimado/a {nombre}, le comunicamos que su deuda de {monto} lleva {dias_mora} días de retraso. De no regularizar en los próximos 5 días hábiles, procederemos con las acciones legales correspondientes. - {empresa}',
            ],
            [
                'name' => 'Recordatorio por correo',
                'channel' => 'email',
                'type' => 'payment_reminder',
                'subject' => 'Recordatorio de pago - {empresa}',
                'body' => 'Estimado/a {nombre}, le recordamos que tiene una cuota pendiente de {monto} con vencimiento el {fecha_vencimiento}. Agradeceremos regularizar su pago. Saludos, {empresa}.',
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::firstOrCreate(
                ['company_id' => $company->id, 'name' => $template['name']],
                array_merge($template, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}