@php($cls = ['entrada' => 'success', 'saida' => 'danger', 'ajuste' => 'secondary'][$movement->type] ?? 'secondary')
@php($icon = ['entrada' => 'arrow-down-circle', 'saida' => 'arrow-up-circle', 'ajuste' => 'sliders'][$movement->type] ?? 'circle')
<span class="badge text-bg-{{ $cls }}"><i class="bi bi-{{ $icon }}"></i> {{ $movement->typeLabel() }}</span>
