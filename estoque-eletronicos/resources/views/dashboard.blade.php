@extends('layouts.app')

@section('title', 'Dashboard')

@section('actions')
    <a href="{{ route('movements.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nova movimentação</a>
@endsection

@section('content')
<div class="row g-3 mb-4">
    @php($cards = [
        ['Produtos ativos', number_format($stats['products'], 0, ',', '.'), 'box-seam', 'primary', route('products.index', ['status' => 'ativos'])],
        ['Unidades em estoque', number_format($stats['units'], 0, ',', '.'), 'stack', 'info', route('products.index')],
        ['Valor em estoque (custo)', brl($stats['cost_value']), 'cash-stack', 'success', null],
        ['Valor em estoque (venda)', brl($stats['sale_value']), 'graph-up-arrow', 'success', null],
        ['Estoque baixo', $stats['low'], 'exclamation-triangle', 'warning', route('products.index', ['status' => 'baixo'])],
        ['Sem estoque', $stats['out'], 'x-octagon', 'danger', route('products.index', ['status' => 'sem_estoque'])],
    ])
    @foreach ($cards as [$label, $value, $icon, $color, $link])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card shadow-sm h-100 {{ $link ? 'position-relative' : '' }}">
                <div class="card-body">
                    <span class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}-emphasis mb-2"><i class="bi bi-{{ $icon }}"></i></span>
                    <div class="stat-value">{{ $value }}</div>
                    <div class="text-muted small">{{ $label }}</div>
                    @if ($link)<a href="{{ $link }}" class="stretched-link" aria-label="{{ $label }}"></a>@endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Entradas x Saídas (últimos 30 dias, em unidades)</div>
            <div class="card-body"><div style="position: relative; height: 300px"><canvas id="movementsChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Valor em estoque por categoria</div>
            <div class="card-body"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-exclamation-triangle text-warning"></i> Precisam de reposição</span>
                <a href="{{ route('products.index', ['status' => 'baixo']) }}" class="small">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Produto</th><th class="text-center">Estoque</th><th class="text-center">Mínimo</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($lowStock as $product)
                        <tr data-href="{{ route('products.show', $product) }}">
                            <td><div class="fw-medium">{{ $product->name }}</div><small class="text-muted">{{ $product->sku }}</small></td>
                            <td class="text-center fw-semibold {{ $product->stock === 0 ? 'text-danger' : 'text-warning-emphasis' }}">{{ $product->stock }}</td>
                            <td class="text-center">{{ $product->min_stock }}</td>
                            <td class="text-end"><a href="{{ route('movements.create', ['product_id' => $product->id, 'type' => 'entrada']) }}" class="btn btn-sm btn-outline-success" title="Registrar entrada"><i class="bi bi-plus-lg"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Nenhum produto abaixo do mínimo.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-trophy text-primary"></i> Mais vendidos (30 dias)</div>
            <ul class="list-group list-group-flush">
                @forelse ($topSellers as $i => $item)
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge rounded-pill text-bg-primary me-2">{{ $i + 1 }}</span>
                        <a href="{{ route('products.show', $item->id) }}" class="text-decoration-none text-body text-truncate me-2">{{ $item->name }}</a>
                        <span class="ms-auto fw-semibold text-nowrap">{{ $item->total }} un.</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center py-4">Sem vendas no período.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-clock-history"></i> Últimas movimentações</span>
                <a href="{{ route('movements.index') }}" class="small">Ver todas</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($latest as $movement)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            @include('partials.movement-badge')
                            <small class="text-muted">{{ $movement->created_at->format('d/m H:i') }}</small>
                        </div>
                        <div class="small text-truncate mt-1">{{ $movement->product->name }}</div>
                        <div class="small text-muted">{{ $movement->reason }} · {{ $movement->signedQuantity() > 0 ? '+' : '' }}{{ $movement->signedQuantity() }} un. · {{ $movement->user?->name ?? '-' }}</div>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center py-4">Nenhuma movimentação.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const chartData = @json($chart);
    new Chart(document.getElementById('movementsChart'), {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                { label: 'Entradas', data: chartData.entradas, backgroundColor: 'rgba(25, 135, 84, .75)' },
                { label: 'Saídas', data: chartData.saidas, backgroundColor: 'rgba(220, 53, 69, .75)' },
            ],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });

    const categories = @json($byCategory);
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categories.map(c => c.name),
            datasets: [{
                data: categories.map(c => Number(c.total)),
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#0dcaf0', '#6c757d'],
            }],
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } },
                tooltip: { callbacks: { label: (ctx) => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) } },
            },
        },
    });
</script>
@endpush
