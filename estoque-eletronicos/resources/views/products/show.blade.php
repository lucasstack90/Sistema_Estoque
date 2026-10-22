@extends('layouts.app')

@section('title', $product->name)

@section('actions')
    <a href="{{ route('movements.create', ['product_id' => $product->id, 'type' => 'entrada']) }}" class="btn btn-success btn-sm"><i class="bi bi-arrow-down-circle"></i> Entrada</a>
    <a href="{{ route('movements.create', ['product_id' => $product->id, 'type' => 'saida']) }}" class="btn btn-danger btn-sm"><i class="bi bi-arrow-up-circle"></i> Saída</a>
    <a href="{{ route('movements.create', ['product_id' => $product->id, 'type' => 'ajuste']) }}" class="btn btn-secondary btn-sm"><i class="bi bi-sliders"></i> Ajuste</a>
    @can('admin')
        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>
    @endcan
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <code class="fs-6">{{ $product->sku }}</code>
                    @include('partials.stock-badge')
                    @unless ($product->active)<span class="badge text-bg-secondary">Inativo</span>@endunless
                </div>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Marca / Modelo</dt><dd class="col-sm-9">{{ $product->brand }}{{ $product->model ? ' · '.$product->model : '' }}</dd>
                    <dt class="col-sm-3">Categoria</dt><dd class="col-sm-9">{{ $product->category->name }}</dd>
                    <dt class="col-sm-3">Fornecedor</dt><dd class="col-sm-9">{{ $product->supplier?->name ?? '-' }}</dd>
                    <dt class="col-sm-3">Localização</dt><dd class="col-sm-9">{{ $product->location ?? '-' }}</dd>
                    <dt class="col-sm-3">Garantia</dt><dd class="col-sm-9">{{ $product->warranty_months ? $product->warranty_months.' meses' : '-' }}</dd>
                    @if ($product->description)
                        <dt class="col-sm-3">Descrição</dt><dd class="col-sm-9">{{ $product->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Saldo em estoque</div>
                <div class="display-6 fw-semibold mb-1 {{ $product->stockStatus() === 'ok' ? '' : ($product->stock === 0 ? 'text-danger' : 'text-warning-emphasis') }}">{{ $product->stock }} <small class="fs-6 text-muted">un.</small></div>
                <div class="small text-muted mb-3">Mínimo: {{ $product->min_stock }} un.</div>
                <table class="table table-sm mb-0">
                    <tr><td>Custo médio</td><td class="text-end">{{ brl($product->cost_price) }}</td></tr>
                    <tr><td>Preço de venda</td><td class="text-end">{{ brl($product->sale_price) }}</td></tr>
                    <tr><td>Margem</td><td class="text-end">{{ $product->marginPercent() !== null ? number_format($product->marginPercent(), 1, ',', '.').'%' : '-' }}</td></tr>
                    <tr><td>Valor em estoque (custo)</td><td class="text-end fw-semibold">{{ brl($product->stock * (float) $product->cost_price) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Histórico de movimentações</span>
        <a href="{{ route('reports.movements', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar</a>
    </div>
    @include('movements._table', ['movements' => $movements, 'showProduct' => false])
</div>

@can('admin')
    <div class="mt-3 text-end">
        @include('partials.delete-button', ['action' => route('products.destroy', $product), 'label' => 'Excluir produto', 'confirm' => 'Excluir este produto definitivamente?'])
    </div>
@endcan
@endsection
