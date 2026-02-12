<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\Debtor;
use App\Models\Debt;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:notifications.index')->only('index');
        $this->middleware('permission:notifications.send')->only(['showSendForm', 'send', 'sendBulk']);
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = NotificationLog::where('company_id', $companyId)
            ->with(['debtor', 'debt']);

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderByDesc('created_at')->paginate(15)->appends($request->query());

        return view('notifications.index', compact('logs'));
    }

    public function showSendForm(Debtor $debtor)
    {
        if ($debtor->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $companyId = auth()->user()->company_id;
        $templates = MessageTemplate::where('company_id', $companyId)->active()->get();
        $debts = $debtor->debts()->active()->get();

        return view('notifications.send', compact('debtor', 'templates', 'debts'));
    }

    public function send(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'debtor_id' => 'required|exists:debtors,id',
            'debt_id' => 'nullable|exists:debts,id',
            'channel' => 'required|in:whatsapp,email,sms',
            'template_id' => 'nullable|exists:message_templates,id',
            'message' => 'required|string',
        ]);

        $debtor = Debtor::where('id', $validated['debtor_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $recipient = match ($validated['channel']) {
            'whatsapp', 'sms' => $debtor->phone,
            'email' => $debtor->email,
        };

        if (!$recipient) {
            return back()->with('error', 'El deudor no tiene ' . ($validated['channel'] === 'email' ? 'correo electrónico' : 'teléfono') . ' registrado.');
        }

        $log = NotificationLog::create([
            'company_id' => $companyId,
            'debtor_id' => $debtor->id,
            'debt_id' => $validated['debt_id'] ?? null,
            'channel' => $validated['channel'],
            'recipient' => $recipient,
            'template' => $validated['template_id'] ?? null,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        // TODO: Aquí va la integración real con WhatsApp/Email/SMS API
        // Por ahora simulamos el envío
        $log->update(['status' => 'sent', 'sent_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'Notificación enviada a ' . $debtor->full_name);
    }

    public function sendBulk(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'channel' => 'required|in:whatsapp,email,sms',
            'template_id' => 'required|exists:message_templates,id',
            'filter' => 'required|in:all_overdue,days_overdue',
            'min_days' => 'nullable|integer|min:1',
        ]);

        $template = MessageTemplate::findOrFail($validated['template_id']);
        $company = auth()->user()->company;

        $query = Debt::byCompany($companyId)->overdue()->with('debtor');

        if ($validated['filter'] === 'days_overdue' && $validated['min_days']) {
            $query->where('days_overdue', '>=', $validated['min_days']);
        }

        $debts = $query->get();
        $sent = 0;

        foreach ($debts as $debt) {
            $debtor = $debt->debtor;
            $recipient = match ($validated['channel']) {
                'whatsapp', 'sms' => $debtor->phone,
                'email' => $debtor->email,
            };

            if (!$recipient) continue;

            $message = $template->render([
                'nombre' => $debtor->full_name,
                'monto' => number_format($debt->pending_amount, 2),
                'fecha_vencimiento' => $debt->due_date->format('d/m/Y'),
                'dias_mora' => $debt->days_overdue,
                'empresa' => $company->trade_name ?? $company->business_name,
            ]);

            NotificationLog::create([
                'company_id' => $companyId,
                'debtor_id' => $debtor->id,
                'debt_id' => $debt->id,
                'channel' => $validated['channel'],
                'recipient' => $recipient,
                'template' => $template->name,
                'message' => $message,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $sent++;
        }

        return redirect()->route('notifications.index')->with('success', "Se enviaron {$sent} notificaciones masivas.");
    }
}
