@extends('layouts.app')

@section('title', $product->exists ? 'Editar produto' : 'Novo produto')

@section('content')
<form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Dados do produto</div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="sku">SKU *</label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="50" placeholder="Ex.: SMP-SAM-A55">
                        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="name">Nome *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="brand">Marca *</label>
                        <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" value="{{ old('brand', $product->brand) }}" required maxlength="80">
                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="model">Modelo</label>
                        <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $product->model) }}" maxlength="80">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="warranty_months">Garantia (meses)</label>
                        <input type="number" class="form-control" id="warranty_months" name="warranty_months" value="{{ old('warranty_months', $product->warranty_months) }}" min="0" max="120">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="category_id">Categoria *</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">Selecione...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="supplier_id">Fornecedor</label>
                        <select class="form-select" id="supplier_id" name="supplier_id">
                            <option value="">Nenhum</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="2000">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Preços</div>
                <div class="card-body row g-3">
                    <div class="col-6 col-lg-12">
                        <label class="form-label" for="cost_price">Preço de custo *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" inputmode="decimal" class="form-control price-input @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->exists ? number_format((float) $product->cost_price, 2, ',', '.') : '') }}" required placeholder="0,00">
                        </div>
                    </div>
                    <div class="col-6 col-lg-12">
                        <label class="form-label" for="sale_price">Preço de venda *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" inputmode="decimal" class="form-control price-input @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->exists ? number_format((float) $product->sale_price, 2, ',', '.') : '') }}" required placeholder="0,00">
                        </div>
                    </div>
                    <div class="col-12"><small class="text-muted">Margem: <strong id="marginPreview">-</strong></small></div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Estoque</div>
                <div class="card-body row g-3">
                    @if ($product->exists)
                        <div class="col-12">
                            <div class="alert alert-light border mb-0 small">
                                Saldo atual: <strong>{{ $product->stock }}</strong> un.<br>
                                Para alterar o saldo, use <a href="{{ route('movements.create', ['product_id' => $product->id]) }}">Nova movimentação</a>.
                            </div>
                        </div>
                    @else
                        <div class="col-6">
                            <label class="form-label" for="initial_stock">Estoque inicial</label>
                            <input type="number" class="form-control" id="initial_stock" name="initial_stock" value="{{ old('initial_stock', 0) }}" min="0">
                        </div>
                    @endif
                    <div class="col-6">
                        <label class="form-label" for="min_stock">Estoque mínimo *</label>
                        <input type="number" class="form-control @error('min_stock') is-invalid @enderror" id="min_stock" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" min="0" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="location">Localização no depósito</label>
                        <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $product->location) }}" maxlength="50" placeholder="Ex.: Corredor A2">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked(old('active', $product->active))>
                            <label class="form-check-label" for="active">Produto ativo</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
        <a href="{{ $product->exists ? route('products.show', $product) : route('products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (() => {
        const parse = (v) => parseFloat(String(v).replace(/\./g, '').replace(',', '.')) || 0;
        const cost = document.getElementById('cost_price');
        const sale = document.getElementById('sale_price');
        const out = document.getElementById('marginPreview');
        const update = () => {
            const c = parse(cost.value), s = parse(sale.value);
            if (s <= 0) { out.textContent = '-'; out.className = ''; return; }
            const m = (s - c) / s * 100;
            out.textContent = m.toFixed(1).replace('.', ',') + '% (' + (s - c).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) + ' por unidade)';
            out.className = m < 0 ? 'text-danger' : (m < 15 ? 'text-warning-emphasis' : 'text-success');
        };
        cost.addEventListener('input', update);
        sale.addEventListener('input', update);
        update();
    })();
</script>
@endpush
