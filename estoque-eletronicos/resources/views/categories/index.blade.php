@extends('layouts.app')

@section('title', 'Categorias')

@section('actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nova categoria</a>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body border-bottom">
        <form method="GET" class="d-flex gap-2" style="max-width: 420px">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar categoria">
            <button class="btn btn-outline-primary">Buscar</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Nome</th><th>Descrição</th><th class="text-center">Produtos</th><th class="text-center">Unidades</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td class="fw-medium"><a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="text-decoration-none">{{ $category->name }}</a></td>
                    <td class="text-muted">{{ $category->description ?? '-' }}</td>
                    <td class="text-center">{{ $category->products_count }}</td>
                    <td class="text-center">{{ (int) $category->units }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                        @include('partials.delete-button', ['action' => route('categories.destroy', $category)])
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Nenhuma categoria cadastrada.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($categories->hasPages())<div class="card-footer bg-white">{{ $categories->links() }}</div>@endif
</div>
@endsection
