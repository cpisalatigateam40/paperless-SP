@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Edit Laporan Verifikasi Metal Detector Produk</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_md_products.update', $report->uuid) }}">
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date->format('Y-m-d') }}"
                            required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                </div>

                <hr>
                <h5 class="mt-5">Detail Pemeriksaan</h5>
                @foreach ($report->details as $i => $detail)
                <p class="mt-5">Pilih Tipe</p>
                <div class="d-flex mb-3" style="gap: 2rem;">
                    <label>
                        <input type="radio" name="details[{{ $i }}][process_type]" value="Manual"
                            {{ old("details.$i.process_type", $detail->process_type) == 'Manual' ? 'checked' : '' }}>
                        Manual
                    </label>

                    <label>
                        <input type="radio" name="details[{{ $i }}][process_type]" value="CFS"
                            {{ old("details.$i.process_type", $detail->process_type) == 'CFS' ? 'checked' : '' }}>
                        CFS
                    </label>

                    <label>
                        <input type="radio" name="details[{{ $i }}][process_type]" value="Colimatic"
                            {{ old("details.$i.process_type", $detail->process_type) == 'Colimatic' ? 'checked' : '' }}>
                        Colimatic
                    </label>

                    <label>
                        <input type="radio" name="details[{{ $i }}][process_type]" value="Multivac"
                            {{ old("details.$i.process_type", $detail->process_type) == 'Multivac' ? 'checked' : '' }}>
                        Multivac
                    </label>
                </div>
                <div class="border rounded p-3 mb-3">
                    <div class="mb-3">
                        <label>Waktu Pengecekan</label>
                        <input type="time" name="details[{{ $i }}][time]" class="form-control"
                            value="{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }}">
                    </div>

                    <div class="mb-3">
                        <label>Nama Produk</label>
                        <select name="details[{{ $i }}][product_uuid]" class="form-control select2-product">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->uuid }}"
                                {{ $product->uuid == $detail->product_uuid ? 'selected' : '' }}>
                                {{ $product->product_name }} - {{ $product->nett_weight }} g
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Kode Produksi</label>
                        <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                            value="{{ $detail->production_code }}">
                    </div>

                    <div class="mb-3">
                        <label>Best Before</label>
                        <input type="date" name="details[{{ $i }}][best_before]" class="form-control"
                            value="{{ $detail->best_before }}">
                    </div>

                    <div class="mb-3">
                        <label>Nomor Program</label>
                        <input type="text" name="details[{{ $i }}][program_number]" class="form-control"
                            value="{{ $detail->program_number }}">
                    </div>

                    <h6 class="mt-4">Hasil Pemeriksaan Verifikasi Specimen</h6>
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>Specimen</th>
                                <th>Depan (D)</th>
                                <th>Tengah (T)</th>
                                <th>Belakang (B)</th>
                                <th>Dalam Tumpukan (DL)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $specimens = ['fe_1_5mm' => 'Fe 1.5 mm', 'non_fe_2mm' => 'Non Fe 2 mm', 'sus_2_5mm' => 'SUS
                            2.5 mm'];
                            $positions = ['d', 't', 'b', 'dl'];
                            $posIndex = 0;
                            @endphp
                            @foreach ($specimens as $specimenKey => $specimenName)
                            <tr>
                                <td>{{ $specimenName }}</td>
                                @foreach ($positions as $posKey)
                                @php
                                $pos = $detail->positions->where('specimen', $specimenKey)->where('position',
                                $posKey)->first();
                                @endphp
                                <td>
                                    <input type="hidden" name="details[{{ $i }}][positions][{{ $posIndex }}][specimen]"
                                        value="{{ $specimenKey }}">
                                    <input type="hidden" name="details[{{ $i }}][positions][{{ $posIndex }}][position]"
                                        value="{{ $posKey }}">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                            name="details[{{ $i }}][positions][{{ $posIndex }}][status]" value="1"
                                            {{ $pos && $pos->status ? 'checked' : '' }}>
                                        <label class="form-check-label">OK</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                            name="details[{{ $i }}][positions][{{ $posIndex }}][status]" value="0"
                                            {{ $pos && !$pos->status ? 'checked' : '' }}>
                                        <label class="form-check-label">Tidak OK</label>
                                    </div>
                                </td>
                                @php $posIndex++; @endphp
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="mb-3 mt-3 col-md-6">
                            <label>Tindakan Perbaikan</label>
                            <input type="text" name="details[{{ $i }}][corrective_action]" class="form-control"
                                value="{{ $detail->corrective_action }}">
                        </div>
                        <div class="mb-3 mt-3 col-md-6">
                            <label>Verifikasi Setelah Perbaikan</label>
                            <select name="details[{{ $i }}][verification]" class="form-control">
                                <option value="">-- Pilih Verifikasi --</option>
                                <option value="0" {{ $detail->verification == 0 ? 'selected' : '' }}>Tidak OK</option>
                                <option value="1" {{ $detail->verification == 1 ? 'selected' : '' }}>OK</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endforeach

                <button type="submit" class="btn btn-success">Perbarui</button>
                <a href="{{ route('report_md_products.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection