@extends('layouts.app')

@section('title', 'Movimentações')

@section('actions')
    <a href="{{ route('reports.movements', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV</a>
    <a href="{{ route('movements.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nova movimentação</a>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Produto, SKU ou documento">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Todos os tipos</option>
                    @foreach (\App\Models\StockMovement::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="product_id" class="form-select">
                    <option value="">Todos os produtos</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-1">
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control" title="Data inicial">
            </div>
            <div class="col-6 col-md-1">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control" title="Data final">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill">Filtrar</button>
                <a href="{{ route('movements.index') }}" class="btn btn-outline-secondary" title="Limpar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
    @include('movements._table', ['movements' => $movements])
</div>
@endsection
