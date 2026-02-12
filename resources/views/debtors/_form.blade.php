@php $d = $debtor ?? null; @endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Tipo de documento</label>
        <select name="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
            @foreach (['DNI', 'RUC', 'CE', 'PASAPORTE'] as $type)
                <option value="{{ $type }}"
                    {{ old('document_type', $d?->document_type) == $type ? 'selected' : '' }}>{{ $type }}
                </option>
            @endforeach
        </select>
        @error('document_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Nro. documento</label>
        <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror"
            value="{{ old('document_number', $d?->document_number) }}" required>
        @error('document_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Nombre completo / Razón social</label>
        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
            value="{{ old('full_name', $d?->full_name) }}" required>
        @error('full_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $d?->email) }}">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono principal</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $d?->phone) }}">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono secundario</label>
        <input type="text" name="phone_secondary" class="form-control"
            value="{{ old('phone_secondary', $d?->phone_secondary) }}">
    </div>
    <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $d?->address) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Distrito</label>
        <input type="text" name="district" class="form-control" value="{{ old('district', $d?->district) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Provincia</label>
        <input type="text" name="province" class="form-control" value="{{ old('province', $d?->province) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Departamento</label>
        <input type="text" name="department" class="form-control" value="{{ old('department', $d?->department) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Referencia</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference', $d?->reference) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Notas</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $d?->notes) }}</textarea>
    </div>
</div>
