@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Pemasakan Dengan Steamer</h4>
        </div>
        <div class="card-body">

            <form action="{{ route('report_rtg_steamers.update', $report->uuid) }}" method="POST" id="steamers-form"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" id="shift" name="shift" class="form-control" value="{{ $report->shift }}"
                            required>
                    </div>
                </div>

                <hr>

                <div id="detailWrapper">
                    @foreach($report->details as $i => $detail)
                    <div class="detail-block border rounded p-3 mb-3">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Produk</label>
                                <select name="product_uuid" class="form-control select2-product" required>
                                    <option value="">-- pilih produk --</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        {{ $product->uuid == $report->product_uuid ? 'selected' : '' }}>
                                        {{ $product->product_name }} - {{ $product->nett_weight }} g
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Steamer</label>
                                <input type="text" name="details[{{ $i }}][steamer]" class="form-control"
                                    value="{{ $detail->steamer }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                    value="{{ $detail->production_code }}">
                            </div>
                            <div class="col-md-6">
                                <label>Jumlah Trolly</label>
                                <input type="number" name="details[{{ $i }}][trolley_count]" class="form-control"
                                    value="{{ $detail->trolley_count }}">
                            </div>
                        </div>

                        <h5 class="mt-4">Steaming</h5>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label>Suhu Ruang (°C)</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][room_temp]"
                                    class="form-control" value="{{ $detail->room_temp }}">
                            </div>
                            <div class="col-md-4">
                                <label>Suhu Produk (°C)</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][product_temp]"
                                    class="form-control" value="{{ $detail->product_temp }}">
                            </div>
                            <div class="col-md-4">
                                <label>Waktu (menit)</label>
                                <input type="number" name="details[{{ $i }}][time_minute]" class="form-control"
                                    value="{{ $detail->time_minute }}">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>Jam Mulai</label>
                                <input type="time" name="details[{{ $i }}][start_time]" class="form-control"
                                    value="{{ $detail->start_time }}">
                            </div>
                            <div class="col-md-6">
                                <label>Jam Selesai</label>
                                <input type="time" name="details[{{ $i }}][end_time]" class="form-control"
                                    value="{{ $detail->end_time }}">
                            </div>
                        </div>

                        <h5 class="mt-4">Sensori</h5>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label>Kematangan</label>
                                <select name="details[{{ $i }}][sensory_ripeness]" class="form-control">
                                    <option value="OK" {{ $detail->sensory_ripeness == 'OK' ? 'selected' : '' }}>OK
                                    </option>
                                    <option value="Tidak OK"
                                        {{ $detail->sensory_ripeness == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Rasa</label>
                                <select name="details[{{ $i }}][sensory_taste]" class="form-control">
                                    <option value="OK" {{ $detail->sensory_taste == 'OK' ? 'selected' : '' }}>OK
                                    </option>
                                    <option value="Tidak OK"
                                        {{ $detail->sensory_taste == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Aroma</label>
                                <select name="details[{{ $i }}][sensory_aroma]" class="form-control">
                                    <option value="OK" {{ $detail->sensory_aroma == 'OK' ? 'selected' : '' }}>OK
                                    </option>
                                    <option value="Tidak OK"
                                        {{ $detail->sensory_aroma == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>Tekstur</label>
                                <select name="details[{{ $i }}][sensory_texture]" class="form-control">
                                    <option value="OK" {{ $detail->sensory_texture == 'OK' ? 'selected' : '' }}>OK
                                    </option>
                                    <option value="Tidak OK"
                                        {{ $detail->sensory_texture == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Warna</label>
                                <select name="details[{{ $i }}][sensory_color]" class="form-control">
                                    <option value="OK" {{ $detail->sensory_color == 'OK' ? 'selected' : '' }}>OK
                                    </option>
                                    <option value="Tidak OK"
                                        {{ $detail->sensory_color == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                    <a href="{{ route('report_rtg_steamers.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection