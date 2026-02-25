@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Pasteurisasi Waterbath</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_waterbaths.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                </div>

                <div id="blocks-wrapper">
                    @foreach($details as $i => $d)
                    <div class="card mb-3 block-item">
                        <div class="card-body">
                            <h5>Detail Produk</h5>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Nama Produk</label>
                                    <select name="details[{{ $i }}][product_uuid]" class="form-control select2-product">
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->uuid }}"
                                            {{ $d->product_uuid == $p->uuid ? 'selected' : '' }}>
                                            {{ $p->product_name }} - {{ $p->nett_weight }} g
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Batch Code</label>
                                    <input type="text" name="details[{{ $i }}][batch_code]" class="form-control"
                                        value="{{ $d->batch_code }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Jumlah</label>
                                    <input type="number" name="details[{{ $i }}][amount]" class="form-control"
                                        value="{{ $d->amount }}">
                                </div>
                                <div class="col-md-6">
                                    <label>Satuan</label>
                                    <select name="details[{{ $i }}][unit]" class="form-control">
                                        <option value="pcs" {{ $d->unit == 'pcs' ? 'selected' : '' }}>pcs</option>
                                        <option value="tray" {{ $d->unit == 'tray' ? 'selected' : '' }}>tray</option>
                                    </select>
                                </div>
                            </div>

                            <hr>

                            {{-- Pasteurisasi --}}
                            @php $p = $pasteurisasi[$i] ?? null; @endphp
                            <h5 class="mt-5">Pasteurisasi</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal produk</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][initial_product_temp]"
                                        class="form-control" value="{{ $p->initial_product_temp ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal air</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][initial_water_temp]"
                                        class="form-control" value="{{ $p->initial_water_temp ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Start Pasteurisasi</label>
                                    <input type="time" name="pasteurisasi[{{ $i }}][start_time_pasteur]"
                                        class="form-control" value="{{ $p->start_time_pasteur ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stop Pasteurisasi</label>
                                    <input type="time" name="pasteurisasi[{{ $i }}][stop_time_pasteur]"
                                        class="form-control" value="{{ $p->stop_time_pasteur ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setelah produk dimasukkan panel</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][water_temp_after_input_panel]"
                                        class="form-control" value="{{ $p->water_temp_after_input_panel ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setelah produk dimasukkan aktual</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][water_temp_after_input_actual]"
                                        class="form-control" value="{{ $p->water_temp_after_input_actual ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setting</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][water_temp_setting]"
                                        class="form-control" value="{{ $p->water_temp_setting ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air aktual</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][water_temp_actual]"
                                        class="form-control" value="{{ $p->water_temp_actual ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir air</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][water_temp_final]"
                                        class="form-control" value="{{ $p->water_temp_final ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir produk</label>
                                    <input type="number" step="0.01" name="pasteurisasi[{{ $i }}][product_temp_final]"
                                        class="form-control" value="{{ $p->product_temp_final ?? '' }}">
                                </div>
                            </div>

                            <hr>

                            {{-- Cooling Shock --}}
                            @php $c = $cooling_shocks[$i] ?? null; @endphp
                            <h5 class="mt-5">Cooling Shock</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Suhu awal air</label>
                                    <input type="number" step="0.01" name="cooling_shocks[{{ $i }}][initial_water_temp]"
                                        class="form-control" value="{{ $c->initial_water_temp ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Start Pasteurisasi</label>
                                    <input type="time" name="cooling_shocks[{{ $i }}][start_time_pasteur]"
                                        class="form-control" value="{{ $c->start_time_pasteur ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stop Pasteurisasi</label>
                                    <input type="time" name="cooling_shocks[{{ $i }}][stop_time_pasteur]"
                                        class="form-control" value="{{ $c->stop_time_pasteur ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air setting</label>
                                    <input type="number" step="0.01" name="cooling_shocks[{{ $i }}][water_temp_setting]"
                                        class="form-control" value="{{ $c->water_temp_setting ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu air aktual</label>
                                    <input type="number" step="0.01" name="cooling_shocks[{{ $i }}][water_temp_actual]"
                                        class="form-control" value="{{ $c->water_temp_actual ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir air</label>
                                    <input type="number" step="0.01" name="cooling_shocks[{{ $i }}][water_temp_final]"
                                        class="form-control" value="{{ $c->water_temp_final ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu akhir produk</label>
                                    <input type="number" step="0.01" name="cooling_shocks[{{ $i }}][product_temp_final]"
                                        class="form-control" value="{{ $c->product_temp_final ?? '' }}">
                                </div>
                            </div>

                            <hr>

                            {{-- Dripping --}}
                            @php $dr = $drippings[$i] ?? null; @endphp
                            <h5 class="mt-5">Dripping</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Start Pasteurisasi</label>
                                    <input type="time" name="drippings[{{ $i }}][start_time_pasteur]"
                                        class="form-control" value="{{ $dr->start_time_pasteur ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stop Pasteurisasi</label>
                                    <input type="time" name="drippings[{{ $i }}][stop_time_pasteur]"
                                        class="form-control" value="{{ $dr->stop_time_pasteur ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Zona Panas</label>
                                    <input type="number" step="0.01" name="drippings[{{ $i }}][hot_zone_temperature]"
                                        class="form-control" value="{{ $dr->hot_zone_temperature ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Zona Dingin</label>
                                    <input type="number" step="0.01" name="drippings[{{ $i }}][cold_zone_temperature]"
                                        class="form-control" value="{{ $dr->cold_zone_temperature ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Suhu Akhir Produk</label>
                                    <input type="number" step="0.01" name="drippings[{{ $i }}][product_temp_final]"
                                        class="form-control" value="{{ $dr->product_temp_final ?? '' }}">
                                </div>
                            </div>

                            <hr>

                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <label>Catatan</label>
                                    <input type="text" name="details[{{ $i }}][note]" class="form-control"
                                        value="{{ $d->note }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-success mt-4">Perbarui</button>
            </form>
        </div>
    </div>
</div>
@endsection