@extends('layouts.app')

@section('title', $supplier->exists ? 'Editar fornecedor' : 'Novo fornecedor')

@section('content')
<div class="row"><div class="col-lg-8">
<form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="card shadow-sm">
    @csrf
    @if ($supplier->exists) @method('PUT') @endif
    <div class="card-body row g-3">
        <div class="col-md-8">
            <label class="form-label" for="name">Razão social / Nome *</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $supplier->name) }}" required maxlength="150" autofocus>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="cnpj">CNPJ</label>
            <input type="text" class="form-control @error('cnpj') is-invalid @enderror" id="cnpj" name="cnpj" value="{{ old('cnpj', $supplier->cnpj) }}" data-mask="cnpj" placeholder="00.000.000/0000-00">
            @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="contact_name">Contato</label>
            <input type="text" class="form-control" id="contact_name" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" maxlength="100">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="email">E-mail</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $supplier->email) }}" maxlength="150">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="phone">Telefone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}" maxlength="20">
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
</div></div>
@endsection
