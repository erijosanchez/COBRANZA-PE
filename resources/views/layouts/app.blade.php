<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CobranzaPE') - Sistema de Cobranzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #1a56db;
            --sidebar-bg: #111827;
        }

        body {
            background: #f3f4f6;
            font-size: 0.9rem;
        }

        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s;
        }

        .sidebar .brand {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar .brand h5 {
            color: #fff;
            margin: 0;
            font-weight: 700;
        }

        .sidebar .brand small {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.6);
            padding: 0.6rem 1.5rem;
            font-size: 0.85rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-left-color: var(--primary-color);
        }

        .sidebar .nav-link i {
            width: 24px;
            margin-right: 8px;
        }

        .sidebar .nav-header {
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem 0.4rem;
            font-weight: 600;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-bar {
            background: #fff;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #6b7280;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.3em 0.7em;
        }

        .btn-sm {
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    {{-- Sidebar --}}
    <nav class="sidebar" id="sidebar">
        <div class="brand">
            <h5><i class="bi bi-cash-stack"></i> CobranzaPE</h5>
            <small>{{ $currentCompany->trade_name ?? ($currentCompany->business_name ?? 'Sistema') }}</small>
        </div>

        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>

            <li class="nav-header">Operaciones</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('debtors.*') ? 'active' : '' }}"
                    href="{{ route('debtors.index') }}">
                    <i class="bi bi-people-fill"></i> Deudores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('debts.*') ? 'active' : '' }}"
                    href="{{ route('debts.index') }}">
                    <i class="bi bi-file-earmark-text-fill"></i> Deudas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}"
                    href="{{ route('payments.index') }}">
                    <i class="bi bi-credit-card-fill"></i> Pagos
                </a>
            </li>

            <li class="nav-header">Cobranza</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('collection-actions.*') ? 'active' : '' }}"
                    href="{{ route('collection-actions.index') }}">
                    <i class="bi bi-telephone-fill"></i> Gestiones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}"
                    href="{{ route('assignments.index') }}">
                    <i class="bi bi-person-check-fill"></i> Asignaciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                    href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell-fill"></i> Notificaciones
                </a>
            </li>

            <li class="nav-header">Reportes</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.debts') ? 'active' : '' }}"
                    href="{{ route('reports.debts') }}">
                    <i class="bi bi-bar-chart-fill"></i> Deudas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.payments') ? 'active' : '' }}"
                    href="{{ route('reports.payments') }}">
                    <i class="bi bi-graph-up"></i> Pagos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.collections') ? 'active' : '' }}"
                    href="{{ route('reports.collections') }}">
                    <i class="bi bi-clipboard-data-fill"></i> Gestiones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.overdue') ? 'active' : '' }}"
                    href="{{ route('reports.overdue') }}">
                    <i class="bi bi-exclamation-triangle-fill"></i> Morosidad
                </a>
            </li>

            @can('settings.company')
                <li class="nav-header">Configuración</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.company') ? 'active' : '' }}"
                        href="{{ route('settings.company') }}">
                        <i class="bi bi-building"></i> Empresa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.payment-methods.*') ? 'active' : '' }}"
                        href="{{ route('settings.payment-methods.index') }}">
                        <i class="bi bi-wallet2"></i> Métodos de Pago
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('message-templates.*') ? 'active' : '' }}"
                        href="{{ route('message-templates.index') }}">
                        <i class="bi bi-chat-square-text-fill"></i> Plantillas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                        href="{{ route('users.index') }}">
                        <i class="bi bi-person-gear"></i> Usuarios
                    </a>
                </li>
            @endcan
        </ul>
    </nav>

    {{-- Main Content --}}
    <div class="main-content">
        <div class="top-bar">
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-outline-secondary d-md-none me-3"
                    onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
                <h6 class="mb-0">@yield('page-title', 'Dashboard')</h6>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">{{ auth()->user()->name }}</span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span
                                class="dropdown-item-text small text-muted">{{ auth()->user()->getRoleNames()->first() }}</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>

        <div class="content-wrapper">
            {{-- Alerts --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
