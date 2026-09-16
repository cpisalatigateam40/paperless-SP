@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Proses Thawing', 'url' => route('report_thawings.index')],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow">

        <div class="card-header">
            <h4>Edit Verifikasi Proses Thawing</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('report_thawings.update',$report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">

                    <div class="col-md-6 mb-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date',$report->date) }}"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ old('shift',$report->shift) }}"
                            required>
                    </div>

                </div>

                <hr>

                <h5>Detail Pemeriksaan</h5>

                <div id="detail-wrapper">

                    @forelse($report->details as $i => $detail)

                    <div class="detail-item card p-3" style="margin-bottom: 4rem;">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label>Waktu Thawing Awal</label>
                                <input type="time" name="details[{{ $i }}][start_thawing_time]" class="form-control"
                                    value="{{ $detail->start_thawing_time }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Waktu Thawing Akhir</label>
                                <input type="time" name="details[{{ $i }}][end_thawing_time]" class="form-control"
                                    value="{{ $detail->end_thawing_time }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Kondisi awal kemasan RM</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][package_condition]" value="utuh"
                                        class="form-check-input" id="package_condition_ok_{{ $i }}"
                                        {{ $detail->package_condition == 'utuh' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="package_condition_ok_{{ $i }}">Utuh</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][package_condition]" value="sobek"
                                        class="form-check-input" id="package_condition_x_{{ $i }}"
                                        {{ $detail->package_condition == 'sobek' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="package_condition_x_{{ $i }}">Sobek</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Nama Bahan Baku</label>
                                <select name="details[{{ $i }}][raw_material_uuid]" class="form-control select2">

                                    <option value="">Pilih RM</option>

                                    @foreach($rawMaterials as $rm)

                                    <option value="{{ $rm->uuid }}"
                                        {{ $detail->raw_material_uuid == $rm->uuid ? 'selected':'' }}>

                                        {{ $rm->material_name }}

                                    </option>

                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                    value="{{ $detail->production_code }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Jumlah (kg)</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][qty]" class="form-control"
                                    value="{{ $detail->qty }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Kondisi Ruang</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][room_condition]" value="OK"
                                        class="form-check-input" id="room_condition_ok_{{ $i }}"
                                        {{ $detail->room_condition == 'OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="room_condition_ok_{{ $i }}">OK</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][room_condition]" value="Tidak OK"
                                        class="form-check-input" id="room_condition_x_{{ $i }}"
                                        {{ $detail->room_condition == 'Tidak OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="room_condition_x_{{ $i }}">Tidak OK</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Waktu Pemeriksaan</label>
                                <input type="time" name="details[{{ $i }}][inspection_time]" class="form-control"
                                    value="{{ $detail->inspection_time }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Suhu Ruang (°C)</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][room_temp]"
                                    class="form-control" value="{{ $detail->room_temp }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Suhu Air Thawing (°C)</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][water_temp]"
                                    class="form-control" value="{{ $detail->water_temp }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Suhu Produk (°C)</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][product_temp]"
                                    class="form-control" value="{{ $detail->product_temp }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Kondisi Produk</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][product_condition]" value="OK"
                                        class="form-check-input" id="product_condition_ok_{{ $i }}"
                                        {{ $detail->product_condition == 'OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="product_condition_ok_{{ $i }}">OK</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][product_condition]" value="Tidak OK"
                                        class="form-check-input" id="product_condition_x_{{ $i }}"
                                        {{ $detail->product_condition == 'Tidak OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="product_condition_x_{{ $i }}">Tidak OK</label>
                                </div>
                            </div>

                        </div>

                        <!-- <div class="text-end">
                            <button type="button" class="btn btn-danger remove-row">
                                Hapus
                            </button>
                        </div> -->

                    </div>

                    @empty

                    <div class="detail-item card mb-3 p-3">
                        <div class="text-center text-muted">
                            Belum ada detail
                        </div>
                    </div>

                    @endforelse

                </div>


                <!-- <button type="button" class="btn btn-info btn-sm mb-3" id="add-row">
                    Tambah Bahan Baku
                </button> -->

                <hr>

                <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>

                <button class="btn btn-success">
                    Update
                </button>


            </form>

        </div>
    </div>
</div>
@endsection


@section('script')

<script>
let row = {{ $report->details->count() }};

$('#add-row').click(function() {

    let html = `

<div class="detail-item card mb-3 p-3">

<div class="row">

<div class="col-md-6 mb-3">
<label>Waktu Thawing Awal</label>
<input type="time" name="details[${row}][start_thawing_time]" class="form-control" value="{{ now()->format('H:i') }}">
</div>

<div class="col-md-6 mb-3">
<label>Waktu Thawing Akhir</label>
<input type="time" name="details[${row}][end_thawing_time]" class="form-control" value="{{ now()->format('H:i') }}">
</div>

<div class="col-md-6 mb-3">
    <label class="form-label d-block">Kondisi awal kemasan RM</label>
    <div class="form-check form-check-inline">
        <input type="radio" name="details[${row}][package_condition]" value="utuh"
            class="form-check-input" id="package_condition_ok_${row}" checked>
        <label class="form-check-label" for="package_condition_ok_${row}">Utuh</label>
    </div>
    <div class="form-check form-check-inline">
        <input type="radio" name="details[${row}][package_condition]" value="sobek"
            class="form-check-input" id="package_condition_x_${row}">
        <label class="form-check-label" for="package_condition_x_${row}">Sobek</label>
    </div>
</div>

<div class="col-md-6 mb-3">
<label>Nama Bahan Baku</label>
<select name="details[${row}][raw_material_uuid]" class="form-control select2">

<option value="">Pilih RM</option>

@foreach($rawMaterials as $rm)
<option value="{{ $rm->uuid }}">{{ $rm->material_name }}</option>
@endforeach

</select>
</div>

<div class="col-md-6 mb-3">
<label>Kode Produksi</label>
<input type="text" name="details[${row}][production_code]" class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Jumlah (kg)</label>
<input type="number" step="0.01" name="details[${row}][qty]" class="form-control">
</div>

<div class="col-md-6 mb-3">
    <label class="form-label d-block">Kondisi Ruang</label>
    <div class="form-check form-check-inline">
        <input type="radio" name="details[${row}][room_condition]" value="OK"
            class="form-check-input" id="room_condition_ok_${row}" checked>
        <label class="form-check-label" for="room_condition_ok_${row}">OK</label>
    </div>
    <div class="form-check form-check-inline">
        <input type="radio" name="details[${row}][room_condition]" value="Tidak OK"
            class="form-check-input" id="room_condition_x_${row}">
        <label class="form-check-label" for="room_condition_x_${row}">Tidak OK</label>
    </div>
</div>

<div class="col-md-6 mb-3">
<label>Waktu Pemeriksaan</label>
<input type="time" name="details[${row}][inspection_time]" class="form-control" value="{{ now()->format('H:i') }}">
</div>

<div class="col-md-6 mb-3">
<label>Suhu Ruang (°C)</label>
<input type="number" step="0.01" name="details[${row}][room_temp]" class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Suhu Air Thawing (°C)</label>
<input type="number" step="0.01" name="details[${row}][water_temp]" class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Suhu Produk (°C)</label>
<input type="number" step="0.01" name="details[${row}][product_temp]" class="form-control">
</div>

<div class="col-md-6 mb-3">
    <label class="form-label d-block">Kondisi Produk</label>
    <div class="form-check form-check-inline">
        <input type="radio" name="details[${row}][product_condition]" value="OK"
            class="form-check-input" id="product_condition_ok_${row}" checked>
        <label class="form-check-label" for="product_condition_ok_${row}">OK</label>
    </div>
    <div class="form-check form-check-inline">
        <input type="radio" name="details[${row}][product_condition]" value="Tidak OK"
            class="form-check-input" id="product_condition_x_${row}">
        <label class="form-check-label" for="product_condition_x_${row}">Tidak OK</label>
    </div>
</div>

</div>

<div class="text-end">
<button type="button" class="btn btn-danger remove-row">Hapus</button>
</div>

</div>
`

    $('#detail-wrapper').append(html)

    row++

})


$(document).on('click', '.remove-row', function() {

    $(this).closest('.detail-item').remove()

})
</script>

@endsection