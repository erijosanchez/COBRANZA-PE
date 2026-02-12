<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        Log::info('MercadoPago Webhook:', $request->all());

        $type = $request->input('type');

        if ($type === 'payment') {
            $paymentId = $request->input('data.id');

            // Verificar el pago con la API de Mercado Pago
            try {
                $response = \Illuminate\Support\Facades\Http::withToken(config('services.mercadopago.access_token'))
                    ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

                if ($response->successful()) {
                    $mpPayment = $response->json();

                    if ($mpPayment['status'] === 'approved') {
                        $externalRef = $mpPayment['external_reference'] ?? null;

                        if ($externalRef) {
                            // external_reference = debt_id
                            $debt = Debt::find($externalRef);

                            if ($debt) {
                                $payment = Payment::where('reference', 'MP-' . $paymentId)->first();

                                if (!$payment) {
                                    Payment::create([
                                        'company_id' => $debt->company_id,
                                        'debt_id' => $debt->id,
                                        'debtor_id' => $debt->debtor_id,
                                        'payment_method_id' => $this->getMercadoPagoMethodId($debt->company_id),
                                        'registered_by' => 1, // Sistema
                                        'receipt_number' => Payment::generateReceipt($debt->company_id),
                                        'amount' => $mpPayment['transaction_amount'],
                                        'payment_date' => now(),
                                        'reference' => 'MP-' . $paymentId,
                                        'notes' => 'Pago automático via Mercado Pago',
                                        'status' => 'confirmed',
                                    ]);

                                    $debt->recalculateAmounts();

                                    Log::info("Pago MP registrado: {$mpPayment['transaction_amount']} para deuda {$debt->code}");
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error procesando webhook MP: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function getMercadoPagoMethodId(int $companyId): int
    {
        return \App\Models\PaymentMethod::where('company_id', $companyId)
            ->where('code', 'MP')
            ->first()?->id ?? 1;
    }
}
