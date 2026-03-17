@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header">
            <h4>Edit Laporan Thawing</h4>
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
                                <label>Kondisi awal kemasan RM</label>
                                <select name="details[{{ $i }}][package_condition]" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="utuh" {{ $detail->package_condition=='utuh'?'selected':'' }}>Utuh
                                    </option>
                                    <option value="sobek" {{ $detail->package_condition=='sobek'?'selected':'' }}>Sobek
                                    </option>
                                </select>
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
                                <label>Kondisi Ruang</label>
                                <select name="details[{{ $i }}][room_condition]" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="OK" {{ $detail->room_condition=='OK'?'selected':'' }}>OK</option>
                                    <option value="Tidak OK" {{ $detail->room_condition=='Tidak OK'?'selected':'' }}>
                                        Tidak OK</option>
                                </select>
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
                                <label>Kondisi Produk</label>
                                <select name="details[{{ $i }}][product_condition]" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="OK" {{ $detail->product_condition=='OK'?'selected':'' }}>OK</option>
                                    <option value="Tidak OK" {{ $detail->product_condition=='Tidak OK'?'selected':'' }}>
                                        Tidak OK</option>
                                </select>
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

                <button class="btn btn-primary">
                    Update Laporan
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
let row = {
    {
        $report - > details - > count()
    }
}

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