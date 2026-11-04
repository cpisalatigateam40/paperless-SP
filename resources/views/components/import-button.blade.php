@props(['modalId' => 'modalImport', 'label' => 'Import'])

<button type="button" {{ $attributes->merge(['class' => 'btn btn-sm btn-outline-success']) }}
    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
    <i class="fas fa-file-import"></i> {{ $label }}
</button>