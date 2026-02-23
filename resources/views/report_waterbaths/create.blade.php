@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Laporan Verifikasi Pasteurisasi Waterbath</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_waterbaths.store') }}" method="POST">
                @csrf

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" required>
                    </div>
                </div>

                <div id="blocks-wrapper">
                    {{-- initial blok index 0 (sama struktur dengan template) --}}
                    <div class="card mb-3 block-item">
                        <div class="card-body">
                            <h5>Detail Produk</h5>
                            <div class="row mb-4">
                                {{-- Detail Produk --}}
                                <div class="col-md-6">
                                    <label>Nama Produk</label>
                                    <select name="details[0][product_uuid]" class="form-control select2-product">
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->uuid }}">{{ $p->product_name }} - {{ $p->nett_weight }} g
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Batch Code</label>
                                    <input type="text" name="details[0][batch_code]" class="form-control">
                                </div>

                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Jumlah</label>
                                    <input type="number" name="details[0][amount]" class="form-control">
                                </div>
                                <div class="col-md-6">
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
                                    <input type="number" name="pasteurisasi[0][initial_product_temp]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal air</label>
                                    <input type="number" name="pasteurisasi[0][initial_water_temp]"
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
                                    <input type="number" name="pasteurisasi[0][water_temp_after_input_panel]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setelah produk dimasukkan aktual</label>
                                    <input type="number" name="pasteurisasi[0][water_temp_after_input_actual]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setting</label>
                                    <input type="number" name="pasteurisasi[0][water_temp_setting]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air aktual</label>
                                    <input type="number" name="pasteurisasi[0][water_temp_actual]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir air</label>
                                    <input type="number" name="pasteurisasi[0][water_temp_final]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir produk</label>
                                    <input type="number" name="pasteurisasi[0][product_temp_final]"
                                        class="form-control">
                                </div>
                            </div>

                            <hr>

                            <h5 class="mt-5">Cooling Shock</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal air</label>
                                    <input type="number" name="cooling_shocks[0][initial_water_temp]"
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
                                    <input type="number" name="cooling_shocks[0][water_temp_setting]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air aktual</label>
                                    <input type="number" name="cooling_shocks[0][water_temp_actual]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir air </label>
                                    <input type="number" name="cooling_shocks[0][water_temp_final]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir produk</label>
                                    <input type="number" name="cooling_shocks[0][product_temp_final]"
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
                                    <input type="number" name="drippings[0][hot_zone_temperature]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Zona Dingin</label>
                                    <input type="number" name="drippings[0][cold_zone_temperature]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Akhir Produk</label>
                                    <input type="number" name="drippings[0][product_temp_final]" class="form-control">
                                </div>
                            </div>

                            <hr>

                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <label>Catatan</label>
                                    {{-- catatan disimpan di details[index][note] supaya gampang --}}
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

                <button type="button" id="add-block" class="btn btn-secondary btn-sm mt-2">+ Tambah Blok</button>

                <hr>

                <button type="submit" class="btn btn-success mt-4">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function() {
    // Tombol tambah blok
    const addBtn = document.getElementById('add-block');
    const wrapper = document.getElementById('blocks-wrapper');
    const template = document.createElement('template');

    // template HTML server-rendered (pakai blade foreach untuk options)
    template.innerHTML = `
<div class="card mb-3 block-item">
    <div class="card-body">
        <h5>Detail Produk</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Nama Produk</label>
                <select name="details[__INDEX__][product_uuid]" class="form-control select2-product">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $p)
                    <option value="{{ $p->uuid }}">{{ $p->product_name }} - {{ $p->nett_weight }} g</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Batch Code</label>
                <input type="text" name="details[__INDEX__][batch_code]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Jumlah</label>
                <input type="number" name="details[__INDEX__][amount]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Satuan</label>
                <select name="details[__INDEX__][unit]" class="form-control" required>
                    <option value="pcs">pcs</option>
                    <option value="tray">tray</option>
                </select>
            </div>
        </div>

        <hr>

        <h5 class='mt-5'>Pasteurisasi</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Suhu awal produk</label>
                <input type="number" name="pasteurisasi[__INDEX__][initial_product_temp]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu awal air</label>
                <input type="number" name="pasteurisasi[__INDEX__][initial_water_temp]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Start Pasteurisasi</label>
                <input type="time" name="pasteurisasi[__INDEX__][start_time_pasteur]" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Stop Pasteurisasi</label>
                <input type="time" name="pasteurisasi[__INDEX__][stop_time_pasteur]" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu air setelah produk dimasukkan panel</label>
                <input type="number" name="pasteurisasi[__INDEX__][water_temp_after_input_panel]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu air setelah produk dimasukkan aktual</label>
                <input type="number" name="pasteurisasi[__INDEX__][water_temp_after_input_actual]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu air setting</label>
                <input type="number" name="pasteurisasi[__INDEX__][water_temp_setting]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu air aktual</label>
                <input type="number" name="pasteurisasi[__INDEX__][water_temp_actual]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu akhir air</label>
                <input type="number" name="pasteurisasi[__INDEX__][water_temp_final]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu akhir produk</label>
                <input type="number" name="pasteurisasi[__INDEX__][product_temp_final]" class="form-control">
            </div>
        </div>

        <hr>

        <h5 class='mt-5'>Cooling Shock</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Suhu awal air </label>
                <input type="number" name="cooling_shocks[__INDEX__][initial_water_temp]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Start Pasteurisasi</label>
                <input type="time" name="cooling_shocks[__INDEX__][start_time_pasteur]" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Stop Pasteurisasi</label>
                <input type="time" name="cooling_shocks[__INDEX__][stop_time_pasteur]" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu air setting </label>
                <input type="number" name="cooling_shocks[__INDEX__][water_temp_setting]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu air aktual </label>
                <input type="number" name="cooling_shocks[__INDEX__][water_temp_actual]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu akhir air </label>
                <input type="number" name="cooling_shocks[__INDEX__][water_temp_final]"
                    class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu akhir produk </label>
                <input type="number" name="cooling_shocks[__INDEX__][product_temp_final]" class="form-control">
            </div>
        </div>
        
        <hr>

        <h5 class='mt-5'>Dripping</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Start Pasteurisasi</label>
                <input type="time" name="drippings[__INDEX__][start_time_pasteur]" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Stop Pasteurisasi</label>
                <input type="time" name="drippings[__INDEX__][stop_time_pasteur]" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu Zona Panas</label>
                <input type="number" name="drippings[__INDEX__][hot_zone_temperature]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu Zona Dingin</label>
                <input type="number" name="drippings[__INDEX__][cold_zone_temperature]" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Suhu Akhir Produk</label>
                <input type="number" name="drippings[__INDEX__][product_temp_final]" class="form-control">
            </div>
        </div>

        <hr>

        <div class="row mt-5">
            <div class="col-md-6">
                <label>Catatan</label>
                <input type="text" name="details[__INDEX__][note]" class="form-control">
            </div>
        </div>

        <div>
            <button type="button" class="btn btn-danger btn-sm remove-block mt-4">Hapus Blok</button>
        </div>
    </div>
</div>

    `;

    addBtn.addEventListener('click', function() {
        const index = wrapper.querySelectorAll('.block-item').length;
        // clone template (already string) and parse to DOM using templateElement
        const temp = template.content.cloneNode(true);
        // replace all name attributes containing __INDEX__
        temp.querySelectorAll('select, input, textarea').forEach(el => {
            if (el.name) {
                el.name = el.name.replace(/__INDEX__/g, index);
            }
        });
        wrapper.appendChild(temp);
        // optionally scroll to new block
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: 'smooth'
        });
    });

    // event delegation untuk hapus blok
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-block')) {
            const blocks = wrapper.querySelectorAll('.block-item');
            // bila mau mencegah hapus terakhir, uncomment baris di bawah:
            // if (blocks.length === 1) return alert('Minimal harus ada 1 blok.');
            e.target.closest('.block-item').remove();
        }
    });
})();
</script>
@endsection