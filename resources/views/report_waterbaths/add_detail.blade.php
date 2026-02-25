@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail untuk Report Tanggal {{ $report->date }} Shift {{ $report->shift }}</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_waterbaths.store_detail', $report->uuid) }}">
                @csrf

                <div id="blocks-wrapper">
                    {{-- initial blok index 0 --}}
                    <div class="card mb-3 block-item">
                        <div class="card-body">
                            <h5>Detail Produk</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nama Produk</label>
                                    <select name="details[0][product_uuid]" class="form-control select2-product"
                                        required>
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->uuid }}">{{ $p->product_name }} - {{ $p->nett_weight }} g
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Batch Code</label>
                                    <input type="text" name="details[0][batch_code]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Jumlah</label>
                                    <input type="number" name="details[0][amount]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Satuan</label>
                                    <select name="details[0][unit]" class="form-control" required>
                                        <option value="pcs">pcs</option>
                                        <option value="tray">tray</option>
                                    </select>
                                </div>

                            </div>

                            <hr>

                            <h5 class="mt-5">Pasteurisasi</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal produk</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][initial_product_temp]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal air</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][initial_water_temp]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Start Pasteurisasi</label>
                                    <input type="time" name="pasteurisasi[0][start_time_pasteur]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stop Pasteurisasi</label>
                                    <input type="time" name="pasteurisasi[0][stop_time_pasteur]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setelah produk dimasukkan panel</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][water_temp_after_input_panel]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setelah produk dimasukkan aktual</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][water_temp_after_input_actual]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setting</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][water_temp_setting]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air aktual</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][water_temp_actual]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir air</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][water_temp_final]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir produk</label>
                                    <input type="number" step="0.01" name="pasteurisasi[0][product_temp_final]"
                                        class="form-control">
                                </div>

                            </div>

                            <hr>

                            <h5 class="mt-5">Cooling Shock</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal air</label>
                                    <input type="number" step="0.01" name="cooling_shocks[0][initial_water_temp]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Start Pasteurisasi</label>
                                    <input type="time" name="cooling_shocks[0][start_time_pasteur]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stop Pasteurisasi</label>
                                    <input type="time" name="cooling_shocks[0][stop_time_pasteur]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setting </label>
                                    <input type="number" step="0.01" name="cooling_shocks[0][water_temp_setting]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air aktual </label>
                                    <input type="number" step="0.01" name="cooling_shocks[0][water_temp_actual]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir air </label>
                                    <input type="number" step="0.01" name="cooling_shocks[0][water_temp_final]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir produk </label>
                                    <input type="number" step="0.01" name="cooling_shocks[0][product_temp_final]"
                                        class="form-control">
                                </div>

                            </div>

                            <hr>

                            <h5 class="mt-5">Dripping</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Start Pasteurisasi</label>
                                    <input type="time" name="drippings[0][start_time_pasteur]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stop Pasteurisasi</label>
                                    <input type="time" name="drippings[0][stop_time_pasteur]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Zona Panas</label>
                                    <input type="number" step="0.01" name="drippings[0][hot_zone_temperature]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Zona Dingin</label>
                                    <input type="number" step="0.01" name="drippings[0][cold_zone_temperature]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Akhir Produk</label>
                                    <input type="number" step="0.01" name="drippings[0][product_temp_final]" class="form-control">
                                </div>
                            </div>

                            <hr>

                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <label>Catatan</label>
                                    <input type="text" name="details[0][note]" class="form-control">
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-danger btn-sm remove-block mt-4">Hapus
                                    Blok</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-block" class="btn btn-info">Tambah Blok</button>
                <button type="submit" class="btn btn-success">Simpan Detail</button>
                <a href="{{ route('report_waterbaths.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let blockIndex = 1;

document.getElementById('add-block').addEventListener('click', function() {
    let wrapper = document.getElementById('blocks-wrapper');
    let template = wrapper.querySelector('.block-item').outerHTML;

    template = template.replace(/\[0\]/g, `[${blockIndex}]`);
    wrapper.insertAdjacentHTML('beforeend', template);
    blockIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-block')) {
        e.target.closest('.block-item').remove();
    }
});
</script>
@endsection