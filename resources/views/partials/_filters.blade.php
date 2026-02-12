{{-- Componente reutilizable para filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ $action }}" class="row g-2 align-items-end">
            {{ $slot }}
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filtrar</button>
                <a href="{{ $action }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>