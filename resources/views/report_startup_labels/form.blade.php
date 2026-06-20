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
                'packaging' => $d->packaging,
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

                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', $isEdit && $report->date ? $report->date->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Shift</label>
                        <input type="text" name="shift" class="form-control" placeholder="Contoh: 1, 2, 3"
                        value="{{ old('shift', $isEdit ? $report->shift : session('shift_number') . '-' . session('shift_group')) }}" required>
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
                                <th style="min-width: 180px;">Packaging (MC/PL)</th>
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
                                            {{ $product->product_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $i }}][packaging]" class="form-control"
                                        value="{{ $detail['packaging'] ?? '' }}">
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
                                    <select name="details[{{ $i }}][result]" class="form-control">
                                        <option value="">- Pilih -</option>
                                        <option value="OK" @selected(($detail['result'] ?? null) == 'OK')>OK</option>
                                        <option value="Tidak OK" @selected(($detail['result'] ?? null) == 'Tidak OK')>Tidak OK</option>
                                    </select>
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

                <div class="mt-4 d-flex gap-2" style="gap: .4rem;">
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Laporan' }}
                    </button>
                    <a href="{{ route('report_startup_labels.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {

    let rowIndex = {{ count($detailsData) }};

    $('#add-detail-row').on('click', function() {

        let template = `
<tr class="detail-row">
    <td>
        <select name="details[__INDEX__][product_uuid]" class="form-control">
            <option value="">- Pilih Produk -</option>
            @foreach($products as $product)
            <option value="{{ $product->uuid }}">
                {{ $product->product_name }}
            </option>
            @endforeach
        </select>
    </td>

    <td>
        <input type="text"
               name="details[__INDEX__][packaging]"
               class="form-control">
    </td>

    <td>
        <input type="time"
               name="details[__INDEX__][time]"
               class="form-control">
    </td>

    <td>
        <input type="text"
               name="details[__INDEX__][production_code]"
               class="form-control">
    </td>

    <td>
        <input type="date"
               name="details[__INDEX__][best_before]"
               class="form-control">
    </td>

    <td>
        <select name="details[__INDEX__][result]"
                class="form-control">
            <option value="">- Pilih -</option>
            <option value="OK">OK</option>
            <option value="Tidak OK">Tidak OK</option>
        </select>
    </td>

    <td>
        <input type="text"
               name="details[__INDEX__][corrective_action]"
               class="form-control">
    </td>

    <td class="text-center">
        <button type="button"
                class="btn btn-sm btn-danger remove-detail-row"
                title="Hapus baris">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
`;

        template = template.replaceAll('__INDEX__', rowIndex);

        $('#detail-rows').append(template);

        rowIndex++;
    });

    $(document).on('click', '.remove-detail-row', function() {

        if ($('#detail-rows tr').length > 1) {

            $(this).closest('tr').remove();

        } else {

            $(this).closest('tr').find('input').val('');
            $(this).closest('tr').find('select').val('');
        }
    });

    $('form').on('submit', function() {

        console.clear();

        console.log('===== DETAIL YANG DIKIRIM =====');

        $('#detail-rows tr').each(function(index) {

            console.log(
                index,
                $(this).find('select,input').first().attr('name')
            );

        });

    });

});
</script>
@endsection