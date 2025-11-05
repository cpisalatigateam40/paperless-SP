@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Formula Detail</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <p> <span style="font-weight: bold;">Nama Formula:</span> {{ $formula->formula_name }}</p>
            <p> <span style="font-weight: bold;">Nama Produk:</span> {{ $formula->product->product_name ?? '-' }}</p>
            <p> <span style="font-weight: bold;">Area:</span> {{ $formula->area->name ?? '-' }}</p>

            <hr>
            <form action="{{ route('formulas.addDetail', $formula->uuid) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Formulasi</label>
                    <input type="text" name="formulation_name" class="form-control" required>
                </div>

                <h5>Raw Materials</h5>
                <div id="raw-material-wrapper">
                    <div class="mb-2 d-flex align-items-center" style="gap: .8rem;">
                        <select name="raw_material_uuid[]" class="form-control">
                            <option value="">-- Select Raw Material --</option>
                            @foreach($rawMaterials as $rm)
                            <option value="{{ $rm->uuid }}">{{ $rm->material_name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="raw_material_weight[]" class="form-control" placeholder="Berat (kg)"
                            step="0.00000001" min="0">
                        <button type="button" class="btn btn-danger btn-sm ms-2"
                            onclick="removeField(this)">Hapus</button>
                    </div>
                </div>
                <button type="button" onclick="addRawMaterial()" class="btn btn-secondary btn-sm mb-3">+ Tambah Raw
                    Material</button>

                <h5>Premixes</h5>
                <div id="premix-wrapper">
                    <div class="mb-2 d-flex align-items-center" style="gap: .8rem;">
                        <select name="premix_uuid[]" class="form-control">
                            <option value="">-- Pilih Premix --</option>
                            @foreach($premixes as $premix)
                            <option value="{{ $premix->uuid }}">{{ $premix->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="premix_weight[]" class="form-control" placeholder="Berat (kg)"
                            step="0.00000001" min="0">
                        <button type="button" class="btn btn-danger btn-sm ms-2"
                            onclick="removeField(this)">Hapus</button>
                    </div>
                </div>


                <button type="button" onclick="addPremix()" class="btn btn-secondary btn-sm mb-3">+ Tambah
                    Premix</button> <br>


                <button class="btn btn-success mt-4">Simpan Formulasi</button>
            </form>

            <h4 class="mt-4">Formulasi</h4>
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Formulasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $grouped = $formula->formulations->groupBy('formulation_name');
                    @endphp

                    @forelse($grouped as $formulationName => $details)
                    <tr>
                        <td>
                            <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ Str::slug($formulationName) }}">
                                {{ $formulationName }}
                            </button>
                        </td>
                        <td>
                            <form
                                action="{{ route('formulas.deleteDetailByName', [$formula->uuid, $formulationName]) }}"
                                method="POST"
                                onsubmit="return confirm('Delete all details with this formulation name?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus Formulasi</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="collapse" id="collapse-{{ Str::slug($formulationName) }}">
                        <td colspan="2">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bahan</th>
                                        <th>Jenis</th>
                                        <th>Berat (kg)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalWeight = 0; @endphp
                                    @foreach($details as $detail)
                                    @php $totalWeight += $detail->weight ?? 0; @endphp
                                    <tr>
                                        <td>{{ $detail->rawMaterial->material_name ?? $detail->premix->name ?? '-' }}
                                        </td>
                                        <td>
                                            @if($detail->rawMaterial)
                                            <span class="badge bg-primary" style="color: white !important;">Raw
                                                Material</span>
                                            @elseif($detail->premix)
                                            <span class="badge bg-success"
                                                style="color: white !important;">Premix</span>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $detail->weight ? number_format($detail->weight, 2) : '-' }}
                                        </td>
                                        <td>
                                            <form
                                                action="{{ route('formulas.deleteDetail', [$formula->uuid, $detail->uuid]) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Delete this detail?')"
                                                    class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <p class="mt-2 fw-bold">
                                Total Bahan: {{ number_format($totalWeight, 2) }} kg
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted">Belum ada formulasi detail.</td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});

function addRawMaterial() {
    let html = `
            <div class="mb-2 d-flex align-items-center" style="gap: .8rem;">
                <select name="raw_material_uuid[]" class="form-control">
                    <option value="">-- Select Raw Material --</option>
                    @foreach($rawMaterials as $rm)
                        <option value="{{ $rm->uuid }}">{{ $rm->material_name }}</option>
                    @endforeach
                </select>
                <input type="number" name="raw_material_weight[]" class="form-control" placeholder="Berat (kg)" step="0.00000001" min="0">
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="removeField(this)">Hapus</button>
            </div>
        `;
    document.getElementById('raw-material-wrapper').insertAdjacentHTML('beforeend', html);
}

function addPremix() {
    let html = `
            <div class="mb-2 d-flex align-items-center" style="gap: .8rem;">
                <select name="premix_uuid[]" class="form-control">
                    <option value="">-- Pilih Premix --</option>
                    @foreach($premixes as $premix)
                        <option value="{{ $premix->uuid }}">{{ $premix->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="premix_weight[]" class="form-control" placeholder="Berat (kg)" step="0.00000001" min="0">
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="removeField(this)">Hapus</button>
            </div>
        `;
    document.getElementById('premix-wrapper').insertAdjacentHTML('beforeend', html);
}

function removeField(button) {
    button.parentElement.remove();
}
</script>
@endsection