@extends('layouts.app')

@section('title', 'Produtos')

@section('actions')
    <a href="{{ route('reports.products', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV</a>
    @can('admin')
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Novo produto</a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 auto-submit">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Nome, SKU, marca ou modelo">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todas as situações</option>
                    @foreach (['ativos' => 'Ativos', 'inativos' => 'Inativos', 'baixo' => 'Estoque baixo', 'sem_estoque' => 'Sem estoque'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select">
                    @foreach (['name' => 'Ordenar: Nome', 'stock_asc' => 'Menor estoque', 'stock_desc' => 'Maior estoque', 'price_desc' => 'Maior preço', 'recent' => 'Mais recentes'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'name') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-outline-primary">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th>SKU</th>
                <th>Produto</th>
                <th>Categoria</th>
                <th class="text-end">Preço de venda</th>
                <th class="text-center">Estoque</th>
                <th class="text-center">Situação</th>
                <th class="text-end">Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr data-href="{{ route('products.show', $product) }}" class="{{ $product->active ? '' : 'text-muted' }}">
                    <td><code>{{ $product->sku }}</code></td>
                    <td>
                        <div class="fw-medium">{{ $product->name }}</div>
                        <small class="text-muted">{{ $product->brand }}{{ $product->model ? ' · '.$product->model : '' }}</small>
                        @unless ($product->active)<span class="badge text-bg-secondary ms-1">Inativo</span>@endunless
                    </td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-end text-nowrap">{{ brl($product->sale_price) }}</td>
                    <td class="text-center fw-semibold">{{ $product->stock }} <small class="text-muted fw-normal">/ mín. {{ $product->min_stock }}</small></td>
                    <td class="text-center">@include('partials.stock-badge')</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('movements.create', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-primary" title="Movimentar"><i class="bi bi-arrow-left-right"></i></a>
                        @can('admin')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Nenhum produto encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection
