@extends('layouts.app')

@section('title', $category->exists ? 'Editar categoria' : 'Nova categoria')

@section('content')
<div class="row"><div class="col-lg-6">
<form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="card shadow-sm">
    @csrf
    @if ($category->exists) @method('PUT') @endif
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label" for="name">Nome *</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="100" autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="form-label" for="description">Descrição</label>
            <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $category->description) }}" maxlength="255">
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
</div></div>
@endsection
