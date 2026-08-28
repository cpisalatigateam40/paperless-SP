@extends('layouts.app')

@php
    $isEdit = isset($item);
@endphp

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>{{ $isEdit ? 'Edit Item Checklist' : 'Tambah Item Checklist' }}</h4>
        </div>

        <div class="card-body">
            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                action="{{ $isEdit ? route('master_checklist_items.update', $item->uuid) : route('master_checklist_items.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Area</label>
                    <select name="area_uuid" id="area_uuid" class="form-control">
                        <option value="">- Berlaku untuk Semua Area -</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->uuid }}"
                            @selected(old('area_uuid', $isEdit ? $item->area_uuid : null) == $area->uuid)>
                            {{ $area->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Section</label>
                    <select name="section_uuid" id="section_uuid" class="form-control">
                        <option value="">- Pilih Section -</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->uuid }}"
                            data-area="{{ $section->area_uuid }}"
                            @selected(old('section_uuid', $isEdit ? $item->section_uuid : null) == $section->uuid)>
                            {{ $section->section_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                @if($isEdit)
                    <div class="col-md-12">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="names[]" class="form-control" placeholder="Contoh: Konveyer filling 1 & meja"
                            value="{{ old('names.0', $item->name) }}" required>
                    </div>
                @else
                    <div class="col-md-12">
                        <label class="form-label">Nama Item</label>
                        <div id="item-names-container">
                            @php $oldNames = old('names', ['']); @endphp
                            @foreach($oldNames as $i => $name)
                            <div class="input-group mb-2 item-name-row">
                                <input type="text" name="names[]" class="form-control" placeholder="Contoh: Konveyer filling 1 & meja"
                                    value="{{ $name }}" required>
                                <button type="button" class="btn btn-outline-danger remove-item-name" {{ count($oldNames) <= 1 ? 'style=display:none' : '' }}>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-item-name" class="btn btn-sm btn-info">
                            <i class="fas fa-plus"></i> Tambah Nama Item
                        </button>
                    </div>
                @endif
                @if($isEdit)
                    <div class="col-md-12 mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Item Aktif</label>
                        </div>
                    </div>
                @endif
            </div>

                <div class="mt-4 d-flex gap-2" style="gap: .4rem;">
                    <button type="submit" class="btn btn-success">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Item' }}
                    </button>
                    <a href="{{ route('master_checklist_items.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    // ===== Filter Section berdasarkan Area =====
    function filterSections(shouldReset) {
        const selectedArea = $('#area_uuid').val();
        $('#section_uuid option').each(function () {
            if (!$(this).val()) return;
            const optArea = $(this).data('area');
            $(this).toggle(!selectedArea || String(optArea) === String(selectedArea));
        });

        if (shouldReset) {
            const current = $('#section_uuid option:selected');
            if (current.val() && current.is(':hidden')) {
                $('#section_uuid').val('');
            }
        }
    }

    // Saat load awal: cuma sembunyikan opsi yang tidak relevan,
    // TIDAK reset value yang sudah tersimpan (edit mode)
    filterSections(false);

    // Saat user ganti Area secara aktif: baru reset Section yang tidak cocok lagi
    $('#area_uuid').on('change', function () {
        filterSections(true);
    });

    // ===== Tambah/Hapus baris Nama Item (hanya mode create) =====
    @if(!$isEdit)
    function toggleRemoveButtons() {
        const $rows = $('#item-names-container .item-name-row');
        $rows.find('.remove-item-name').toggle($rows.length > 1);
    }

    $('#add-item-name').on('click', function () {
        const $row = $(`
            <div class="input-group mb-2 item-name-row">
                <input type="text" name="names[]" class="form-control" placeholder="Contoh: Konveyer filling 1 & meja" required>
                <button type="button" class="btn btn-outline-danger remove-item-name">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
        $('#item-names-container').append($row);
        toggleRemoveButtons();
        $row.find('input').focus();
    });

    $(document).on('click', '.remove-item-name', function () {
        $(this).closest('.item-name-row').remove();
        toggleRemoveButtons();
    });

    toggleRemoveButtons();
    @endif
});
</script>
@endpush
@endsection