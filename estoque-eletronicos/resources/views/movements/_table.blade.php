<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
        <tr>
            <th>Data</th>
            <th>Tipo</th>
            @if ($showProduct ?? true)<th>Produto</th>@endif
            <th>Motivo</th>
            <th class="text-center">Qtd.</th>
            <th class="text-center">Saldo</th>
            <th>Documento</th>
            <th>Usuário</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($movements as $movement)
            <tr>
                <td class="text-nowrap">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                <td>@include('partials.movement-badge')</td>
                @if ($showProduct ?? true)
                    <td><a href="{{ route('products.show', $movement->product_id) }}" class="text-decoration-none">{{ $movement->product->name }}</a><br><small class="text-muted">{{ $movement->product->sku }}</small></td>
                @endif
                <td>
                    {{ $movement->reason }}
                    @if ($movement->notes)<br><small class="text-muted">{{ $movement->notes }}</small>@endif
                </td>
                <td class="text-center fw-semibold {{ $movement->signedQuantity() >= 0 ? 'text-success' : 'text-danger' }}">{{ $movement->signedQuantity() > 0 ? '+' : '' }}{{ $movement->signedQuantity() }}</td>
                <td class="text-center text-nowrap"><small class="text-muted">{{ $movement->balance_before }} →</small> <strong>{{ $movement->balance_after }}</strong></td>
                <td>{{ $movement->document ?? '-' }}@if ($movement->unit_cost)<br><small class="text-muted">{{ brl($movement->unit_cost) }}/un.</small>@endif</td>
                <td>{{ $movement->user?->name ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-5">Nenhuma movimentação encontrada.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@if ($movements->hasPages())
    <div class="card-footer bg-white">{{ $movements->links() }}</div>
@endif
