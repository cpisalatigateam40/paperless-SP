@extends('layouts.app')

@php
    $isEdit = isset($report);

    // Data detail: ambil dari old() (kalau validasi gagal), atau dari $report (mode edit),
    // atau satu baris kosong (mode create / baru pertama kali buka form)
    $detailsData = old('details', $isEdit
        ? $report->details->map(function ($d) {
            return [
                'product_uuid'      => $d->product_uuid,
                'time'              => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : null,
                'production_code'   => $d->production_code,
                'best_before'       => $d->best_before ? $d->best_before->format('Y-m-d') : null,
                'result'            => $d->result,
                'corrective_action' => $d->corrective_action,
            ];
        })->toArray()
        : [[]]
    );

    if (empty($detailsData)) {
        $detailsData = [[]];
    }
@endphp

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>{{ $isEdit ? 'Edit Laporan Startup Label' : 'Tambah Laporan Startup Label' }}</h4>
        </div>

        <div class="card-body">
            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                action="{{ $isEdit ? route('report_startup_labels.update', $report->uuid) : route('report_startup_labels.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                {{-- ===== HEADER ===== --}}
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Area</label>
                        <select name="area_uuid" class="form-control">
                            <option value="">- Pilih Area -</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->uuid }}"
                                @selected(old('area_uuid', $isEdit ? $report->area_uuid : null) == $area->uuid)>
                                {{ $area->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', $isEdit && $report->date ? $report->date->format('Y-m-d') : null) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Shift</label>
                        <input type="text" name="shift" class="form-control" placeholder="Contoh: 1, 2, 3"
                            value="{{ old('shift', $isEdit ? $report->shift : null) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Dibuat Oleh</label>
                        <input type="text" name="created_by" class="form-control"
                            value="{{ old('created_by', $isEdit ? $report->created_by : null) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Diketahui Oleh</label>
                        <input type="text" name="known_by" class="form-control"
                            value="{{ old('known_by', $isEdit ? $report->known_by : null) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Disetujui Oleh</label>
                        <input type="text" name="approved_by" class="form-control"
                            value="{{ old('approved_by', $isEdit ? $report->approved_by : null) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Disetujui Pada</label>
                        <input type="datetime-local" name="approved_at" class="form-control"
                            value="{{ old('approved_at', $isEdit && $report->approved_at ? $report->approved_at->format('Y-m-d\TH:i') : null) }}">
                    </div>
                </div>

                <hr class="my-4">

                {{-- ===== DETAIL ===== --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Detail Produk</h5>
                    <button type="button" id="add-detail-row" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="detail-table">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">Produk</th>
                                <th style="min-width: 120px;">Waktu</th>
                                <th style="min-width: 150px;">Kode Produksi</th>
                                <th style="min-width: 150px;">Best Before</th>
                                <th style="min-width: 120px;">Hasil</th>
                                <th style="min-width: 200px;">Tindakan Koreksi</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="detail-rows">
                            @foreach($detailsData as $i => $detail)
                            <tr class="detail-row">
                                <td>
                                    <select name="details[{{ $i }}][product_uuid]" class="form-control">
                                        <option value="">- Pilih Produk -</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->uuid }}"
                                            @selected(($detail['product_uuid'] ?? null) == $product->uuid)>
                                            {{ $product->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="time" name="details[{{ $i }}][time]" class="form-control"
                                        value="{{ $detail['time'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                        value="{{ $detail['production_code'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="date" name="details[{{ $i }}][best_before]" class="form-control"
                                        value="{{ $detail['best_before'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $i }}][result]" class="form-control"
                                        value="{{ $detail['result'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $i }}][corrective_action]" class="form-control"
                                        value="{{ $detail['corrective_action'] ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-detail-row" title="Hapus baris">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Laporan' }}
                    </button>
                    <a href="{{ route('report_startup_labels.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>

            {{-- Template baris kosong di LUAR <form>, supaya tidak ikut ter-submit.
                 Dipakai JS sebagai master saat klik "Tambah Baris". --}}
            <table class="d-none">
                <tbody>
                    <tr id="detail-row-template">
                        <td>
                            <select name="details[__INDEX__][product_uuid]" class="form-control">
                                <option value="">- Pilih Produk -</option>
                                @foreach($products as $product)
                                <option value="{{ $product->uuid }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="time" name="details[__INDEX__][time]" class="form-control"></td>
                        <td><input type="text" name="details[__INDEX__][production_code]" class="form-control"></td>
                        <td><input type="date" name="details[__INDEX__][best_before]" class="form-control"></td>
                        <td><input type="text" name="details[__INDEX__][result]" class="form-control"></td>
                        <td><input type="text" name="details[__INDEX__][corrective_action]" class="form-control"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger remove-detail-row" title="Hapus baris">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    let rowIndex = {{ count($detailsData) }};

    // Tambah baris detail baru dari template
    $('#add-detail-row').on('click', function() {
        const template = document.getElementById('detail-row-template').outerHTML;
        const newRow = template.replaceAll('__INDEX__', rowIndex);
        $('#detail-rows').append(newRow);
        rowIndex++;
    });

    // Hapus baris detail (minimal 1 baris harus tetap ada)
    $(document).on('click', '.remove-detail-row', function() {
        if ($('#detail-rows tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            $(this).closest('tr').find('input').val('');
            $(this).closest('tr').find('select').val('');
        }
    });
});
</script>
@endsection