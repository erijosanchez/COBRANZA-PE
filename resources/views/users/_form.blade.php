@php $u = $user ?? null; @endphp

<div class="mb-3">
    <label class="form-label">Nombre *</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $u?->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3">
    <label class="form-label">Email *</label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $u?->email) }}" required>
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label">DNI</label>
        <input type="text" name="dni" class="form-control" value="{{ old('dni', $u?->dni) }}" maxlength="8">
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $u?->phone) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Rol *</label>
        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}"
                    {{ old('role', $u?->getRoleNames()->first()) == $role->name ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}</option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
