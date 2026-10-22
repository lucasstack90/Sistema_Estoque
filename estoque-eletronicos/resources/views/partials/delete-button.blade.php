<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="{{ $confirm ?? 'Tem certeza que deseja excluir?' }}">
    @csrf
    @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i>@isset($label) {{ $label }}@endisset</button>
</form>
