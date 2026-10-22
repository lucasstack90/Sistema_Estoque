@extends('layouts.app')

@section('title', 'Nova movimentação')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <form method="POST" action="{{ route('movements.store') }}" class="card shadow-sm" id="movementForm">
            @csrf
            <input type="hidden" name="redirect_to_product" value="{{ $selectedProduct ? 1 : 0 }}">
            <div class="card-body">
                <label class="form-label fw-semibold">Tipo de movimentação</label>
                <div class="btn-group w-100 type-selector mb-4" role="group">
                    @foreach (['entrada' => ['success', 'arrow-down-circle', 'Entrada'], 'saida' => ['danger', 'arrow-up-circle', 'Saída'], 'ajuste' => ['secondary', 'sliders', 'Ajuste']] as $value => [$color, $icon, $label])
                        <input type="radio" class="btn-check" name="type" id="type_{{ $value }}" value="{{ $value }}" @checked(old('type', $selectedType) === $value)>
                        <label class="btn btn-outline-{{ $color }}" for="type_{{ $value }}"><i class="bi bi-{{ $icon }}"></i> {{ $label }}</label>
                    @endforeach
                </div>

                <div class="mb-3">
                    <label class="form-label" for="product_id">Produto *</label>
                    <input type="search" class="form-control form-control-sm mb-1" id="productSearch" placeholder="Filtrar lista por nome ou SKU...">
                    <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required size="1">
                        <option value="">Selecione o produto...</option>
                        @foreach ($products->groupBy('category.name') as $category => $items)
                            <optgroup label="{{ $category }}">
                                @foreach ($items as $product)
                                    <option value="{{ $product->id }}" data-stock="{{ $product->stock }}" data-min="{{ $product->min_stock }}" data-cost="{{ $product->cost_price }}"
                                        @selected(old('product_id', $selectedProduct) == $product->id)>{{ $product->sku }} · {{ $product->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="alert alert-light border d-none" id="stockInfo">
                    Saldo atual: <strong id="currentStock">0</strong> un. · Mínimo: <span id="minStock">0</span> un.
                    <div class="balance-preview mt-1">Saldo após a operação: <strong id="newStock">-</strong></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="reason">Motivo *</label>
                        <select class="form-select @error('reason') is-invalid @enderror" id="reason" name="reason" required data-old="{{ old('reason') }}"></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="quantity" id="quantityLabel">Quantidade *</label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" min="0" required>
                        <div class="form-text" id="quantityHelp"></div>
                    </div>
                    <div class="col-md-6" id="unitCostGroup">
                        <label class="form-label" for="unit_cost">Custo unitário (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" inputmode="decimal" class="form-control" id="unit_cost" name="unit_cost" value="{{ old('unit_cost') }}" placeholder="0,00">
                        </div>
                        <div class="form-text">Se informado, atualiza o custo médio do produto.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="document">Documento</label>
                        <input type="text" class="form-control" id="document" name="document" value="{{ old('document') }}" maxlength="60" placeholder="Nº da NF, pedido...">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="notes">Observações</label>
                        <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes') }}" maxlength="255">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex gap-2">
                <button class="btn btn-primary" id="submitBtn"><i class="bi bi-check-lg"></i> Registrar</button>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const reasons = @json(\App\Models\StockMovement::REASONS);
        const form = document.getElementById('movementForm');
        const product = document.getElementById('product_id');
        const search = document.getElementById('productSearch');
        const reason = document.getElementById('reason');
        const qty = document.getElementById('quantity');
        const info = document.getElementById('stockInfo');
        const newStock = document.getElementById('newStock');
        const submit = document.getElementById('submitBtn');
        let firstRender = true;

        const currentType = () => form.querySelector('input[name="type"]:checked')?.value || 'entrada';
        const selected = () => product.options[product.selectedIndex];

        function renderReasons() {
            const keep = firstRender ? reason.dataset.old : reason.value;
            reason.innerHTML = '';
            reasons[currentType()].forEach((r) => reason.add(new Option(r, r, false, r === keep)));
            firstRender = false;
        }

        function renderType() {
            const type = currentType();
            document.getElementById('unitCostGroup').classList.toggle('d-none', type !== 'entrada');
            document.getElementById('quantityLabel').textContent = type === 'ajuste' ? 'Saldo contado (novo saldo) *' : 'Quantidade *';
            document.getElementById('quantityHelp').textContent = type === 'ajuste'
                ? 'Informe a quantidade física contada; o sistema calcula a diferença.' : '';
            qty.min = type === 'ajuste' ? 0 : 1;
            renderReasons();
            renderPreview();
        }

        function renderPreview() {
            const opt = selected();
            if (!opt || !opt.value) { info.classList.add('d-none'); return; }
            const stock = parseInt(opt.dataset.stock, 10);
            const min = parseInt(opt.dataset.min, 10);
            document.getElementById('currentStock').textContent = stock;
            document.getElementById('minStock').textContent = min;
            info.classList.remove('d-none');

            const q = parseInt(qty.value, 10);
            if (isNaN(q)) { newStock.textContent = '-'; newStock.className = ''; submit.disabled = false; return; }
            const type = currentType();
            const result = type === 'entrada' ? stock + q : type === 'saida' ? stock - q : q;
            newStock.textContent = result < 0 ? 'Estoque insuficiente!' : result + ' un.' + (type === 'ajuste' ? ' (diferença: ' + (result - stock > 0 ? '+' : '') + (result - stock) + ')' : '');
            newStock.className = result < 0 ? 'text-danger' : (result <= min ? 'text-warning-emphasis' : 'text-success');
            submit.disabled = result < 0;
        }

        search.addEventListener('input', () => {
            const term = search.value.toLowerCase();
            let firstMatch = null;
            product.querySelectorAll('option[value]:not([value=""])').forEach((opt) => {
                const match = opt.textContent.toLowerCase().includes(term);
                opt.hidden = !match;
                if (match && !firstMatch) firstMatch = opt;
            });
            if (term && firstMatch && selected().hidden) { product.value = firstMatch.value; renderPreview(); }
        });

        form.querySelectorAll('input[name="type"]').forEach((r) => r.addEventListener('change', renderType));
        product.addEventListener('change', renderPreview);
        qty.addEventListener('input', renderPreview);
        renderType();
    })();
</script>
@endpush
