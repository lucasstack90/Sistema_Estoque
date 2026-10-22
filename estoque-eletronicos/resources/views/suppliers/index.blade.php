@extends('layouts.app')

@section('title', 'Fornecedores')

@section('actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Novo fornecedor</a>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body border-bottom">
        <form method="GET" class="d-flex gap-2" style="max-width: 420px">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nome ou CNPJ">
            <button class="btn btn-outline-primary">Buscar</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Fornecedor</th><th>CNPJ</th><th>Contato</th><th>E-mail / Telefone</th><th class="text-center">Produtos</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($suppliers as $supplier)
                <tr>
                    <td class="fw-medium"><a href="{{ route('products.index', ['supplier_id' => $supplier->id]) }}" class="text-decoration-none">{{ $supplier->name }}</a></td>
                    <td class="text-nowrap">{{ $supplier->cnpj ?? '-' }}</td>
                    <td>{{ $supplier->contact_name ?? '-' }}</td>
                    <td>{{ $supplier->email ?? '-' }}<br><small class="text-muted">{{ $supplier->phone }}</small></td>
                    <td class="text-center">{{ $supplier->products_count }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                        @include('partials.delete-button', ['action' => route('suppliers.destroy', $supplier)])
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Nenhum fornecedor cadastrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($suppliers->hasPages())<div class="card-footer bg-white">{{ $suppliers->links() }}</div>@endif
</div>
@endsection
