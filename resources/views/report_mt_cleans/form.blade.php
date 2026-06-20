@extends('layouts.app')

@php
    $isEdit = isset($report);

    $detailsData = old('details', $isEdit
        ? $report->details->map(function ($d) {
            return [
                'product_uuid'      => $d->product_uuid,
                'time'              => $d->time
                    ? \Illuminate\Support\Str::substr($d->time, 0, 5)
                    : null,
                'mt_1'              => $d->mt_1,
                'mt_2'              => $d->mt_2,
                'finding_type'      => $d->finding_type,
                'condition'         => $d->condition,
                'note'              => $d->note,
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
            <h4>
                {{ $isEdit
                    ? 'Edit Laporan Kebersihan Magnet Trap'
                    : 'Tambah Laporan Kebersihan Magnet Trap' }}
            </h4>
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
                action="{{ $isEdit
                    ? route('report_mt_cleans.update', $report->uuid)
                    : route('report_mt_cleans.store') }}">

                @csrf

                @if($isEdit)
                    @method('PUT')
                @endif

                {{-- HEADER --}}
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Tanggal
                        </label>

                        <input
                            type="date"
                            name="date"
                            class="form-control"
                            value="{{ old(
                                'date',
                                $isEdit && $report->date
                                    ? $report->date->format('Y-m-d')
                                    : now()->format('Y-m-d')
                            ) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Shift
                        </label>

                        <input
                            type="text"
                            name="shift"
                            class="form-control"
                            value="{{ old(
                                'shift',
                                $isEdit
                                    ? $report->shift
                                    : session('shift_number')
                                        . '-'
                                        . session('shift_group')
                            ) }}"
                            required>
                    </div>

                </div>

                <hr class="my-4">

                {{-- DETAIL --}}
                <div class="d-flex justify-content-between align-items-center mb-2">

                    <h5 class="mb-0">
                        Detail Pemeriksaan
                    </h5>

                    <button type="button"
                        id="add-detail-row"
                        class="btn btn-sm btn-outline-primary">

                        <i class="fas fa-plus"></i>
                        Tambah Baris
                    </button>

                </div>

                <div class="table-responsive">

                    <table class="table table-bordered align-middle"
                        id="detail-table">

                        <thead>
                            <tr>
                                <th style="min-width:180px">
                                    Produk
                                </th>

                                <th style="min-width:120px">
                                    Waktu
                                </th>

                                <th style="min-width:150px">
                                    MT 1
                                </th>

                                <th style="min-width:150px">
                                    MT 2
                                </th>

                                <th style="min-width:150px">
                                    Jenis Temuan
                                </th>

                                <th style="min-width:150px">
                                    Kondisi
                                </th>

                                <th style="min-width:200px">
                                    Catatan
                                </th>

                                <th style="min-width:200px">
                                    Tindakan Koreksi
                                </th>

                                <th width="50"></th>
                            </tr>
                        </thead>

                        <tbody id="detail-rows">

                            @foreach($detailsData as $i => $detail)

                            <tr class="detail-row">

                                <td>
                                    <select
                                        name="details[{{ $i }}][product_uuid]"
                                        class="form-control">

                                        <option value="">
                                            - Pilih Produk -
                                        </option>

                                        @foreach($products as $product)
                                        <option
                                            value="{{ $product->uuid }}"
                                            @selected(
                                                ($detail['product_uuid'] ?? null)
                                                == $product->uuid
                                            )>

                                            {{ $product->product_name }}
                                        </option>
                                        @endforeach

                                    </select>
                                </td>

                                <td>
                                    <input
                                        type="time"
                                        name="details[{{ $i }}][time]"
                                        class="form-control"
                                        value="{{ $detail['time'] ?? '' }}">
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="details[{{ $i }}][mt_1]"
                                        class="form-control"
                                        value="{{ $detail['mt_1'] ?? '' }}">
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="details[{{ $i }}][mt_2]"
                                        class="form-control"
                                        value="{{ $detail['mt_2'] ?? '' }}">
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="details[{{ $i }}][finding_type]"
                                        class="form-control"
                                        value="{{ $detail['finding_type'] ?? '' }}">
                                </td>

                               <td>
                                    <select
                                        name="details[{{ $i }}][condition]"
                                        class="form-control">

                                        <option value="">- Pilih -</option>

                                        <option value="Bersih"
                                            @selected(($detail['condition'] ?? null) == 'Bersih')>
                                            Bersih
                                        </option>

                                        <option value="Tidak Bersih"
                                            @selected(($detail['condition'] ?? null) == 'Tidak Bersih')>
                                            Tidak Bersih
                                        </option>

                                    </select>
                                </td>

                                <td>
                                    <textarea
                                        name="details[{{ $i }}][note]"
                                        class="form-control"
                                        rows="1">{{ $detail['note'] ?? '' }}</textarea>
                                </td>

                                <td>
                                    <textarea
                                        name="details[{{ $i }}][corrective_action]"
                                        class="form-control"
                                        rows="1">{{ $detail['corrective_action'] ?? '' }}</textarea>
                                </td>

                                <td class="text-center">
                                    <button type="button"
                                        class="btn btn-sm btn-danger remove-detail-row">

                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="mt-4 d-flex gap-2"
                    style="gap:.4rem;">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        {{ $isEdit
                            ? 'Simpan Perubahan'
                            : 'Simpan Laporan' }}

                    </button>

                    <a href="{{ route('report_mt_cleans.index') }}"
                        class="btn btn-secondary">

                        Batal
                    </a>

                </div>

            </form>

            {{-- TEMPLATE ROW --}}
            <table class="d-none">
                <tbody>

                    <tr id="detail-row-template">

                        <td>
                            <select
                                name="details[__INDEX__][product_uuid]"
                                class="form-control">

                                <option value="">
                                    - Pilih Produk -
                                </option>

                                @foreach($products as $product)
                                <option value="{{ $product->uuid }}">
                                    {{ $product->product_name }}
                                </option>
                                @endforeach

                            </select>
                        </td>

                        <td>
                            <input type="time"
                                name="details[__INDEX__][time]"
                                class="form-control">
                        </td>

                        <td>
                            <input type="text"
                                name="details[__INDEX__][mt_1]"
                                class="form-control">
                        </td>

                        <td>
                            <input type="text"
                                name="details[__INDEX__][mt_2]"
                                class="form-control">
                        </td>

                        <td>
                            <input type="text"
                                name="details[__INDEX__][finding_type]"
                                class="form-control">
                        </td>

                        <td>
                            <select
                                name="details[__INDEX__][condition]"
                                class="form-control">

                                <option value="">- Pilih -</option>
                                <option value="Bersih">Bersih</option>
                                <option value="Tidak Bersih">Tidak Bersih</option>

                            </select>
                        </td>

                        <td>
                            <textarea
                                name="details[__INDEX__][note]"
                                class="form-control"
                                rows="1"></textarea>
                        </td>

                        <td>
                            <textarea
                                name="details[__INDEX__][corrective_action]"
                                class="form-control"
                                rows="1"></textarea>
                        </td>

                        <td class="text-center">
                            <button type="button"
                                class="btn btn-sm btn-danger remove-detail-row">
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

    let rowIndex = $('#detail-rows tr').length;

    $('#add-detail-row').on('click', function () {

        let template = $('#detail-row-template')
            .clone();

        template.removeAttr('id');

        template.html(
            template.html().replaceAll(
                '__INDEX__',
                rowIndex
            )
        );

        $('#detail-rows').append(template);

        rowIndex++;
    });

    $(document).on(
        'click',
        '.remove-detail-row',
        function() {

            if (
                $('#detail-rows tr').length > 1
            ) {
                $(this)
                    .closest('tr')
                    .remove();
            } else {

                $(this)
                    .closest('tr')
                    .find('input')
                    .val('');

                $(this)
                    .closest('tr')
                    .find('textarea')
                    .val('');

                $(this)
                    .closest('tr')
                    .find('select')
                    .val('');
            }
        }
    );

});
</script>
@endsection