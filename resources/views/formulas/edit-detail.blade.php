@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Formulasi</h4>
        </div>

        <div class="card-body">

            {{-- ALERT --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p><strong>Nama Formula:</strong> {{ $formula->formula_name }}</p>
            <p><strong>Produk:</strong> {{ $formula->product->product_name ?? '-' }}</p>
            <p><strong>Area:</strong> {{ $formula->area->name ?? '-' }}</p>

            <hr>

            <form action="{{ route('formulas.updateDetail', [$formula->uuid, $formulation_name]) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- NAMA FORMULASI --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Formulasi</label>
                    <input type="text"
                           name="formulation_name"
                           value="{{ $formulation_name }}"
                           class="form-control"
                           required>
                </div>

                {{-- ================= RAW MATERIAL ================= --}}
                <h5>Raw Materials</h5>

                <div id="raw-material-wrapper">
                    @foreach($details->whereNotNull('raw_material_uuid') as $detail)
                        <div class="mb-2 d-flex align-items-center" style="gap:.8rem;">
                            <select name="raw_material_uuid[]" class="form-control select2-raw-material">
                                <option value="">-- Pilih Raw Material --</option>
                                @foreach($rawMaterials as $rm)
                                    <option value="{{ $rm->uuid }}"
                                        @selected($rm->uuid == $detail->raw_material_uuid)>
                                        {{ $rm->material_name }}
                                    </option>
                                @endforeach
                            </select>

                            <input type="number"
                                   name="raw_material_weight[]"
                                   class="form-control"
                                   placeholder="Berat (kg)"
                                   step="0.00000001"
                                   min="0"
                                   value="{{ $detail->weight }}">

                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="removeField(this)">
                                Hapus
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button"
                        class="btn btn-secondary btn-sm mb-5"
                        onclick="addRawMaterial()">
                    + Tambah Raw Material
                </button>

                {{-- ================= PREMIX ================= --}}
                <h5>Premixes</h5>

                <div id="premix-wrapper">
                    @foreach($details->whereNotNull('premix_uuid') as $detail)
                        <div class="mb-2 d-flex align-items-center" style="gap:.8rem;">
                            <select name="premix_uuid[]" class="form-control select2-premix">
                                <option value="">-- Pilih Premix --</option>
                                @foreach($premixes as $premix)
                                    <option value="{{ $premix->uuid }}"
                                        @selected($premix->uuid == $detail->premix_uuid)>
                                        {{ $premix->name }}
                                    </option>
                                @endforeach
                            </select>

                            <input type="number"
                                   name="premix_weight[]"
                                   class="form-control"
                                   placeholder="Berat (kg)"
                                   step="0.00000001"
                                   min="0"
                                   value="{{ $detail->weight }}">

                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="removeField(this)">
                                Hapus
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button"
                        class="btn btn-secondary btn-sm mb-3"
                        onclick="addPremix()">
                    + Tambah Premix
                </button>

                <hr>

                {{-- ACTION --}}
                <div class="d-flex gap-2" style="gap: .4rem;">
                    <button class="btn btn-success">
                        Simpan Perubahan
                    </button>

                    <a href="{{ route('formulas.detail', $formula->uuid) }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('.select2-raw-material').select2({
        placeholder: '-- Pilih Raw Material --',
        allowClear: true,
        width: '100%'
    });

    $('.select2-premix').select2({
        placeholder: '-- Pilih Premix --',
        allowClear: true,
        width: '100%'
    });
});

function addRawMaterial() {
    let html = `
        <div class="mb-2 d-flex align-items-center" style="gap:.8rem;">
            <select name="raw_material_uuid[]" class="form-control select2-raw-material">
                <option value="">-- Pilih Raw Material --</option>
                @foreach($rawMaterials as $rm)
                    <option value="{{ $rm->uuid }}">{{ $rm->material_name }}</option>
                @endforeach
            </select>

            <input type="number"
                   name="raw_material_weight[]"
                   class="form-control"
                   placeholder="Berat (kg)"
                   step="0.00000001"
                   min="0">

            <button type="button"
                    class="btn btn-danger btn-sm"
                    onclick="removeField(this)">
                Hapus
            </button>
        </div>
    `;

    $('#raw-material-wrapper').append(html);
    $('#raw-material-wrapper .select2-raw-material').last().select2({
        placeholder: '-- Pilih Raw Material --',
        allowClear: true,
        width: '100%'
    });
}

function addPremix() {
    let html = `
        <div class="mb-2 d-flex align-items-center" style="gap:.8rem;">
            <select name="premix_uuid[]" class="form-control select2-premix">
                <option value="">-- Pilih Premix --</option>
                @foreach($premixes as $premix)
                    <option value="{{ $premix->uuid }}">{{ $premix->name }}</option>
                @endforeach
            </select>

            <input type="number"
                   name="premix_weight[]"
                   class="form-control"
                   placeholder="Berat (kg)"
                   step="0.00000001"
                   min="0">

            <button type="button"
                    class="btn btn-danger btn-sm"
                    onclick="removeField(this)">
                Hapus
            </button>
        </div>
    `;

    $('#premix-wrapper').append(html);
    $('#premix-wrapper .select2-premix').last().select2({
        placeholder: '-- Pilih Premix --',
        allowClear: true,
        width: '100%'
    });
}

function removeField(btn) {
    btn.parentElement.remove();
}
</script>
@endsection
