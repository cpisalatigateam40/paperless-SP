@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Verifikasi Pembuatan Emulsi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_emulsion_makings.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col">
                        <label>Hari/Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', $report->date) }}">
                    </div>
                    <div class="col">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ old('shift', $report->shift) }}">
                    </div>
                </div>

                <hr>

                {{-- Jenis Emulsi dan Kode Produksi --}}
                <div class="row mb-3">
                    <div class="col">
                        <label>Jenis Emulsi</label>
                        <select name="emulsion_type" class="form-control">
                            <option value="">--Pilih Emulsi--</option>
                            <option value="Emulsi Oil" {{ $header->emulsion_type == 'Emulsi Oil' ? 'selected' : '' }}>
                                Emulsi Oil</option>
                            <option value="Emulsi Skin" {{ $header->emulsion_type == 'Emulsi Skin' ? 'selected' : '' }}>
                                Emulsi Skin</option>
                            <option value="Emulsi Gel" {{ $header->emulsion_type == 'Emulsi Gel' ? 'selected' : '' }}>
                                Emulsi Gel</option>
                            <option value="Emulsi GMB" {{ $header->emulsion_type == 'Emulsi GMB' ? 'selected' : '' }}>
                                Emulsi GMB</option>
                        </select>
                    </div>
                    <div class="col">
                        <label>Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control"
                            value="{{ old('production_code', $header->production_code) }}">
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
                            <th>Kesesuaian Formula</th>
                        </tr>
                    </thead>
                    <tbody id="bahan-baku-body">
                        @foreach($details as $i => $detail)
                        <tr>
                            <td>
                                <select name="details[{{ $i }}][raw_material_uuid]" class="form-control">
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach($rawMaterials as $material)
                                    <option value="{{ $material->uuid }}"
                                        {{ $material->uuid == $detail->raw_material_uuid ? 'selected' : '' }}>
                                        {{ $material->material_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="details[{{ $i }}][weight]" class="form-control"
                                    value="{{ $detail->weight }}">
                            </td>
                            <td>
                                <input type="number" step="0.1" name="details[{{ $i }}][temperature]"
                                    class="form-control" value="{{ $detail->temperature }}">
                            </td>
                            <td>
                                <select name="details[{{ $i }}][conformity]" class="form-control">
                                    <option value="✓" {{ $detail->conformity == '✓' ? 'selected' : '' }}>✓</option>
                                    <option value="x" {{ $detail->conformity == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- <div class="mt-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="tambahBaris()">Tambah Baris</button>
                </div> -->

                <hr>

                {{-- Aging --}}
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
                        @foreach($agings as $i => $aging)
                        <tr>
                            <td>
                                <input type="time" name="agings[{{ $i }}][start_aging]" class="form-control"
                                    value="{{ $aging->start_aging }}">
                            </td>
                            <td>
                                <input type="time" name="agings[{{ $i }}][finish_aging]" class="form-control"
                                    value="{{ $aging->finish_aging }}">
                            </td>
                            <td>
                                <select name="agings[{{ $i }}][sensory_color]" class="form-control">
                                    <option value="✓" {{ $aging->sensory_color == '✓' ? 'selected' : '' }}>✓</option>
                                    <option value="x" {{ $aging->sensory_color == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </td>
                            <td>
                                <select name="agings[{{ $i }}][sensory_texture]" class="form-control">
                                    <option value="✓" {{ $aging->sensory_texture == '✓' ? 'selected' : '' }}>✓</option>
                                    <option value="x" {{ $aging->sensory_texture == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.1" name="agings[{{ $i }}][temp_after]" class="form-control"
                                    value="{{ $aging->temp_after }}">
                            </td>
                            <td>
                                <select name="agings[{{ $i }}][emulsion_result]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    <option value="OK" {{ $aging->emulsion_result == 'OK' ? 'selected' : '' }}>OK
                                    </option>
                                    <option value="Tidak OK"
                                        {{ $aging->emulsion_result == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success mt-3">Update</button>
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
                    <select name="details[${rowIdx}][raw_material_uuid]" class="form-control">
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($rawMaterials as $material)
                            <option value="{{ $material->uuid }}">{{ $material->material_name }}</option>
                        @endforeach
                    </select>
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
</script>

@endsection