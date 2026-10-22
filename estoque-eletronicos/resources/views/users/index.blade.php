@extends('layouts.app')

@section('title', 'Usuários')

@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Novo usuário</a>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td class="fw-medium">{{ $user->name }} @if ($user->is(auth()->user()))<span class="badge text-bg-light border">você</span>@endif</td>
                    <td>{{ $user->email }}</td>
                    <td>{!! $user->isAdmin() ? '<span class="badge text-bg-primary">Administrador</span>' : '<span class="badge text-bg-info">Operador</span>' !!}</td>
                    <td>{!! $user->active ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' !!}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                        @unless ($user->is(auth()->user()))
                            @include('partials.delete-button', ['action' => route('users.destroy', $user)])
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())<div class="card-footer bg-white">{{ $users->links() }}</div>@endif
</div>
<p class="text-muted small mt-3"><strong>Administrador:</strong> acesso total. <strong>Operador:</strong> consulta produtos e registra movimentações.</p>
@endsection
