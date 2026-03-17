@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header">
            <h4>Tambah Detail Pemeriksaan</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('report_thawings.details.store',$report->uuid) }}" method="POST">
                @csrf

                <h5>Detail Pemeriksaan</h5>

                <div id="detail-wrapper">

                    <div class="detail-item card mb-3 p-3">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label>Waktu Thawing Awal</label>
                                <input type="time" name="details[0][start_thawing_time]" class="form-control"
                                    value="{{ now()->format('H:i') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Waktu Thawing Akhir</label>
                                <input type="time" name="details[0][end_thawing_time]" class="form-control"
                                    value="{{ now()->format('H:i') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Kondisi awal kemasan RM</label>
                                <select name="details[0][package_condition]" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="utuh">Utuh</option>
                                    <option value="sobek">Sobek</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Nama Bahan Baku</label>
                                <select name="details[0][raw_material_uuid]" class="form-control select2">

                                    <option value="">Pilih RM</option>

                                    @foreach($rawMaterials as $rm)
                                    <option value="{{ $rm->uuid }}">
                                        {{ $rm->material_name }}
                                    </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[0][production_code]" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Jumlah (kg)</label>
                                <input type="number" step="0.01" name="details[0][qty]" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Kondisi Ruang</label>
                                <select name="details[0][room_condition]" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Waktu Pemeriksaan</label>
                                <input type="time" name="details[0][inspection_time]" class="form-control"
                                    value="{{ now()->format('H:i') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Suhu Ruang (°C)</label>
                                <input type="number" step="0.01" name="details[0][room_temp]" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Suhu Air Thawing (°C)</label>
                                <input type="number" step="0.01" name="details[0][water_temp]" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Suhu Produk (°C)</label>
                                <input type="number" step="0.01" name="details[0][product_temp]" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Kondisi Produk</label>
                                <select name="details[0][product_condition]" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>

                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-danger remove-row">
                                Hapus
                            </button>
                        </div>

                    </div>

                </div>


                <button type="button" class="btn btn-info btn-sm mb-3" id="add-row">
                    Tambah Detail Pemeriksaan
                </button>

                <hr>

                <button class="btn btn-primary">
                    Simpan Detail
                </button>

                <a href="{{ route('report_thawings.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>
    </div>
</div>
@endsection

@section('script')

<script>
let row = 1

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
<label>Kondisi awal kemasan RM</label>
<select name="details[${row}][package_condition]" class="form-control">
<option value="">Pilih</option>
<option value="utuh">Utuh</option>
<option value="sobek">Sobek</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label>Nama Bahan Baku</label>
<select name="details[${row}][raw_material_uuid]" class="form-control select2">
<option value="">Pilih RM</option>

@foreach($rawMaterials as $rm)
<option value="{{ $rm->uuid }}">
{{ $rm->material_name }}
</option>
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
<label>Kondisi Ruang</label>
<select name="details[${row}][room_condition]" class="form-control">
<option value="">Pilih</option>
<option value="OK">OK</option>
<option value="Tidak OK">Tidak OK</option>
</select>
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
<label>Kondisi Produk</label>
<select name="details[${row}][product_condition]" class="form-control">
<option value="">Pilih</option>
<option value="OK">OK</option>
<option value="Tidak OK">Tidak OK</option>
</select>
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