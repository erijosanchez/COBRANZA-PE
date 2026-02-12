@extends('layouts.app')
@section('title', 'Empresa')
@section('page-title', 'Configuración de Empresa')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">RUC *</label>
                                <input type="text" name="ruc" class="form-control"
                                    value="{{ old('ruc', $company->ruc) }}" maxlength="11" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Razón Social *</label>
                                <input type="text" name="business_name" class="form-control"
                                    value="{{ old('business_name', $company->business_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre Comercial</label>
                                <input type="text" name="trade_name" class="form-control"
                                    value="{{ old('trade_name', $company->trade_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="address" class="form-control"
                                    value="{{ old('address', $company->address) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $company->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $company->email) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
