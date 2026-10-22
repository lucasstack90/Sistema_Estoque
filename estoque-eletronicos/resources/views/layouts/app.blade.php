<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <nav id="sidebar" class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 text-white text-decoration-none">
            <i class="bi bi-cpu fs-3 me-2"></i>
            <span class="fs-5 fw-semibold">Estoque<br><small class="fw-normal opacity-75">Eletrônicos</small></span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Produtos</a></li>
            <li><a href="{{ route('movements.index') }}" class="nav-link {{ request()->routeIs('movements.index') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i> Movimentações</a></li>
            <li><a href="{{ route('movements.create') }}" class="nav-link {{ request()->routeIs('movements.create') ? 'active' : '' }}"><i class="bi bi-plus-circle"></i> Nova movimentação</a></li>
            @can('admin')
                <li class="nav-section">Cadastros</li>
                <li><a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Categorias</a></li>
                <li><a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="bi bi-truck"></i> Fornecedores</a></li>
                <li><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Usuários</a></li>
            @endcan
        </ul>
        <hr>
        <div class="small">
            <div class="fw-semibold"><i class="bi bi-person-circle"></i> {{ auth()->user()->name }}</div>
            <div class="opacity-75 mb-2">{{ auth()->user()->isAdmin() ? 'Administrador' : 'Operador' }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-right"></i> Sair</button>
            </form>
        </div>
    </nav>

    <div class="flex-grow-1 min-vw-0">
        <header class="topbar d-flex align-items-center px-3 px-lg-4 py-2 border-bottom bg-white">
            <button class="btn btn-outline-secondary btn-sm d-lg-none me-2" id="sidebarToggle" aria-label="Menu"><i class="bi bi-list"></i></button>
            <h1 class="h5 mb-0">@yield('title', 'Painel')</h1>
            <div class="ms-auto">@yield('actions')</div>
        </header>
        <main class="p-3 p-lg-4">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
