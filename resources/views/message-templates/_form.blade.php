@php $t = $template ?? null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombre *</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $t?->name) }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Canal *</label>
        <select name="channel" class="form-select" required>
            @foreach (['whatsapp' => 'WhatsApp', 'email' => 'Email', 'sms' => 'SMS'] as $k => $v)
                <option value="{{ $k }}" {{ old('channel', $t?->channel) == $k ? 'selected' : '' }}>
                    {{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo *</label>
        <select name="type" class="form-select" required>
            @foreach (['payment_reminder' => 'Recordatorio', 'overdue_notice' => 'Aviso mora', 'payment_confirmation' => 'Confirmación', 'promise_reminder' => 'Promesa', 'legal_warning' => 'Legal', 'custom' => 'Personalizado'] as $k => $v)
                <option value="{{ $k }}" {{ old('type', $t?->type) == $k ? 'selected' : '' }}>
                    {{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12">
        <label class="form-label">Asunto <small class="text-muted">(solo para email)</small></label>
        <input type="text" name="subject" class="form-control" value="{{ old('subject', $t?->subject) }}">
    </div>
    <div class="col-md-12">
        <label class="form-label">Cuerpo del mensaje *</label>
        <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="5" required>{{ old('body', $t?->body) }}</textarea>
        @error('body')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Variables: {nombre}, {monto}, {fecha_vencimiento}, {dias_mora}, {concepto}, {saldo},
            {empresa}, {fecha_promesa}</small>
    </div>
</div>
