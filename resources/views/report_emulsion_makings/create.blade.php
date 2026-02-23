@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Verifikasi Pembuatan Emulsi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_emulsion_makings.store') }}" method="POST">
                @csrf

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col">
                        <label>Hari/Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}">
                    </div>
                    <div class="col">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" required>
                    </div>
                </div>

                <hr>

                {{-- Jenis Emulsi dan Kode Produksi --}}
                <div class="row mb-3">
                    <div class="col">
                        <label>Jenis Emulsi</label>
                        <select name="emulsion_type" class="form-control">
                            <option value="">--Pilih Emulsi--</option>
                            <option value="Emulsi Oil">Emulsi Oil</option>
                            <option value="Emulsi Skin">Emulsi Skin</option>
                            <option value="Emulsi Gel">Emulsi Gel</option>
                            <option value="Emulsi GMB">Emulsi GMB</option>
                        </select>
                    </div>
                    <div class="col">
                        <label>Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control">
                    </div>
                </div>

                <hr>

                {{-- Tabel Bahan Baku --}}
                <h5 class="mb-2">Bahan Baku</h5>
                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Berat (kg)</th>
                            <th>Suhu (°C)</th>
                            <!-- <th>Sensori</th> -->
                            <th>Kesesuaian Formula</th>
                        </tr>
                    </thead>
                    <tbody id="bahan-baku-body">
                        <tr>
                            <td>
                                <!-- <select name="details[0][raw_material_uuid]" class="form-control">
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach($rawMaterials as $material)
                                    <option value="{{ $material->uuid }}">{{ $material->material_name }}</option>
                                    @endforeach
                                </select> -->
                                <select name="details[0][material_uuid]" class="form-control" onchange="updateMaterialType(this)">
                                    <option value="">-- Pilih Bahan --</option>

                                    {{-- Raw Materials --}}
                                    @foreach($rawMaterials as $material)
                                        <option value="{{ $material->uuid }}" data-type="raw">{{ $material->material_name }}</option>
                                    @endforeach

                                    {{-- Premix --}}
                                    @foreach($premixes as $premix)
                                        <option value="{{ $premix->uuid }}" data-type="premix">{{ $premix->name }} (Premix)</option>
                                    @endforeach
                                </select>

                                <input type="hidden" name="details[0][material_type]" value="raw">

                            </td>
                            <td>
                                <input type="number" step="0.01" name="details[0][weight]" class="form-control">
                            </td>
                            <td>
                                <input type="number" step="0.1" name="details[0][temperature]" class="form-control">
                            </td>
                            <!-- <td>
                                    <input type="text" name="details[0][sensory]" class="form-control">
                                </td> -->
                            <td>
                                <select name="details[0][conformity]" class="form-control">
                                    <option value="✓">✓</option>
                                    <option value="x">x</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>

                </table>

                <div class="mt-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="tambahBaris()">Tambah Baris</button>
                </div>


                <hr>

                {{-- Aging --}}
                <!-- <h5 class="mb-2">Aging</h5> -->
                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th>Waktu Awal Pembuatan Emulsi</th>
                            <th>Waktu Akhir Pembuatan Emulsi</th>
                            <th>Sensori Warna</th>
                            <th>Sensori Texture</th>
                            <th>Suhu Emulsi After Proses</th>
                            <th>Hasil Emulsi (Sensori)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="time" name="agings[0][start_aging]" class="form-control"
                                    placeholder="Start Aging">
                            </td>
                            <td>
                                <input type="time" name="agings[0][finish_aging]" class="form-control"
                                    placeholder="Finish Aging">
                            </td>

                            <td>
                                <select name="agings[0][sensory_color]" class="form-control">
                                    <option value="✓">✓</option>
                                    <option value="x">x</option>
                                </select>
                            </td>
                            <td>
                                <select name="agings[0][sensory_texture]" class="form-control">
                                    <option value="✓">✓</option>
                                    <option value="x">x</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.1" name="agings[0][temp_after]" class="form-control">
                            </td>
                            <td>
                                <select name="agings[0][emulsion_result]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success mt-3">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let rowIdx = 1;

function tambahBaris() {
    let html = `
            <tr>
                <td>
                    <select name="details[${rowIdx}][material_uuid]" class="form-control" onchange="updateMaterialType(this)">
                        <option value="">-- Pilih Bahan --</option>
                        {{-- Raw Materials --}}
                        @foreach($rawMaterials as $material)
                            <option value="{{ $material->uuid }}" data-type="raw">{{ $material->material_name }}</option>
                        @endforeach

                        {{-- Premix --}}
                        @foreach($premixes as $premix)
                            <option value="{{ $premix->uuid }}" data-type="premix">{{ $premix->name }} (Premix)</option>
                        @endforeach
                    </select>

                    <input type="hidden" name="details[${rowIdx}][material_type]" value="raw">
                </td>
                <td>
                    <input type="number" step="0.01" name="details[${rowIdx}][weight]" class="form-control">
                </td>
                <td>
                    <input type="number" step="0.1" name="details[${rowIdx}][temperature]" class="form-control">
                </td>
                <td>
                    <select name="details[${rowIdx}][conformity]" class="form-control">
                        <option value="✓">✓</option>
                        <option value="x">x</option>
                    </select>
                </td>
            </tr>`;
    document.getElementById('bahan-baku-body').insertAdjacentHTML('beforeend', html);
    rowIdx++;
}

function updateMaterialType(select) {
    const hidden = select.closest('tr').querySelector('input[name$="[material_type]"]');
    hidden.value = select.selectedOptions[0].dataset.type;
}
</script>

@endsection