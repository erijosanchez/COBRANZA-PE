<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:notifications.templates');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;
        $templates = MessageTemplate::where('company_id', $companyId)->orderBy('channel')->orderBy('name')->get();

        return view('message-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('message-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:whatsapp,email,sms',
            'type' => 'required|in:payment_reminder,overdue_notice,payment_confirmation,promise_reminder,legal_warning,custom',
            'subject' => 'nullable|required_if:channel,email|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        MessageTemplate::create($validated);

        return redirect()->route('message-templates.index')->with('success', 'Plantilla creada exitosamente.');
    }

    public function edit(MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        return view('message-templates.edit', compact('messageTemplate'));
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:whatsapp,email,sms',
            'type' => 'required|in:payment_reminder,overdue_notice,payment_confirmation,promise_reminder,legal_warning,custom',
            'subject' => 'nullable|required_if:channel,email|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $messageTemplate->update($validated);

        return redirect()->route('message-templates.index')->with('success', 'Plantilla actualizada.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $messageTemplate->delete();

        return back()->with('success', 'Plantilla eliminada.');
    }
}
