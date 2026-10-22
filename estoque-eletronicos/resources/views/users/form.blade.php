@extends('layouts.app')

@section('title', $user->exists ? 'Editar usuário' : 'Novo usuário')

@section('content')
<div class="row"><div class="col-lg-7">
<form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="card shadow-sm">
    @csrf
    @if ($user->exists) @method('PUT') @endif
    @php($self = $user->exists && $user->is(auth()->user()))
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">Nome *</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">E-mail *</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="150">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="role">Perfil *</label>
            <select class="form-select" id="role" name="role" @disabled($self)>
                <option value="operador" @selected(old('role', $user->role) === 'operador')>Operador</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrador</option>
            </select>
            @if ($self)<input type="hidden" name="role" value="admin">@endif
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked(old('active', $user->active)) @disabled($self)>
                <label class="form-check-label" for="active">Usuário ativo</label>
                @if ($self)<input type="hidden" name="active" value="1">@endif
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password">Senha {{ $user->exists ? '' : '*' }}</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ $user->exists ? '' : 'required' }} autocomplete="new-password">
            <div class="form-text">{{ $user->exists ? 'Deixe em branco para manter a senha atual. ' : '' }}Mínimo de 8 caracteres.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Confirmar senha</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
</div></div>
@endsection
