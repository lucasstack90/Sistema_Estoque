@php($status = $product->stockStatus())
@if ($status === 'sem_estoque')
    <span class="badge text-bg-danger">Sem estoque</span>
@elseif ($status === 'baixo')
    <span class="badge text-bg-warning">Estoque baixo</span>
@else
    <span class="badge text-bg-success">OK</span>
@endif
