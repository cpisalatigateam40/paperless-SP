@extends('layouts.app')

@php
    $isEdit = isset($report);

    // Susun ulang data jadi per "batch" (1 batch = 1 produk + jam, berisi hasil semua item)
    $oldBatches = old('batches');

    if ($oldBatches) {
        $groupedBatches = $oldBatches;
    } elseif ($isEdit) {
        $groupedBatches = [];
        foreach ($report->details as $d) {
            $key = $d->product_uuid . '|' . $d->time;

            if (!isset($groupedBatches[$key])) {
                $groupedBatches[$key] = [
                    'product_uuid' => $d->product_uuid,
                    'time'         => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : null,
                    'items'        => [],
                ];
            }

            $groupedBatches[$key]['items'][$d->item_uuid] = [
                'result'             => $d->result,
                'explanation'        => $d->explanation,
                'notes'              => $d->notes,
                'corrective_action'  => $d->corrective_action,
            ];
        }
        $groupedBatches = array_values($groupedBatches);
    } else {
        $groupedBatches = [
            ['product_uuid' => null, 'time' => null, 'items' => []],
        ];
    }

    $nextBatchIndex = count($groupedBatches);
@endphp

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>{{ $isEdit ? 'Edit Laporan Pemeriksaan Kebersihan' : 'Tambah Laporan Pemeriksaan Kebersihan' }}</h4>
            <small class="text-muted">Setelah Pergantian Produk</small>
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
                action="{{ $isEdit ? route('report_changeover_cleanings.update', $report->uuid) : route('report_changeover_cleanings.store') }}">
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

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Pemeriksaan per Pergantian Produk</h5>
                    <button type="button" id="add-batch" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus"></i> Tambah Pergantian Produk
                    </button>
                </div>

                {{-- ===== BATCH (1 batch = 1 produk, berisi semua item) ===== --}}
                <div id="batches-container">
                    @foreach($groupedBatches as $batchIndex => $batch)
                    <div class="batch-section border rounded p-3 mb-3" data-batch-index="{{ $batchIndex }}">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Nama Produk</label>
                                <select name="batches[{{ $batchIndex }}][product_uuid]" class="form-control" required>
                                    <option value="">- Pilih Produk -</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        @selected(($batch['product_uuid'] ?? null) == $product->uuid)>
                                        {{ $product->product_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jam</label>
                                <input type="time" name="batches[{{ $batchIndex }}][time]" class="form-control"
                                    value="{{ $batch['time'] ?? '' }}">
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" class="btn btn-sm btn-danger remove-batch">
                                    <i class="fas fa-trash"></i> Hapus Pergantian Produk Ini
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 180px;">Item</th>
                                        <th style="min-width: 100px;">Hasil</th>
                                        <th style="min-width: 150px;">Penjelasan</th>
                                        <th style="min-width: 150px;">Keterangan</th>
                                        <th style="min-width: 150px;">Tindakan Koreksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items->groupBy('category') as $category => $categoryItems)

                                        <tr class="table-secondary">
                                            <td colspan="5" class="fw-bold">
                                                {{ $category }}
                                            </td>
                                        </tr>

                                        @foreach($categoryItems as $item)
                                        @php
                                            $itemData = $batch['items'][$item->uuid] ?? [];
                                        @endphp

                                        <tr>
                                            <td>
                                                <span class="ms-3">
                                                    {{ $item->name }}
                                                </span>
                                            </td>

                                            <td>
                                                <select
                                                    name="batches[{{ $batchIndex }}][items][{{ $item->uuid }}][result]"
                                                    class="form-control">
                                                    <option value="">-</option>

                                                    <option value="OK"
                                                        @selected(($itemData['result'] ?? 'OK') == 'OK')>
                                                        OK
                                                    </option>

                                                    <option value="Tidak OK"
                                                        @selected(($itemData['result'] ?? null) == 'Tidak OK')>
                                                        Tidak OK
                                                    </option>
                                                </select>
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    name="batches[{{ $batchIndex }}][items][{{ $item->uuid }}][explanation]"
                                                    class="form-control"
                                                    value="{{ $itemData['explanation'] ?? '' }}">
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    name="batches[{{ $batchIndex }}][items][{{ $item->uuid }}][notes]"
                                                    class="form-control"
                                                    value="{{ $itemData['notes'] ?? '' }}">
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    name="batches[{{ $batchIndex }}][items][{{ $item->uuid }}][corrective_action]"
                                                    class="form-control"
                                                    value="{{ $itemData['corrective_action'] ?? '' }}">
                                            </td>
                                        </tr>
                                        @endforeach

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>

                <input type="hidden" id="next-batch-index" value="{{ $nextBatchIndex }}">

                <div class="mt-4 d-flex gap-2" style="gap: .4rem;">
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Laporan' }}
                    </button>
                    <a href="{{ route('report_changeover_cleanings.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    let nextBatchIndex = parseInt($('#next-batch-index').val(), 10);

    const products = @json($products->map(fn($p) => ['uuid' => $p->uuid, 'name' => $p->product_name]));
    const items = @json($items->map(fn($i) => ['uuid' => $i->uuid, 'name' => $i->name]));

    function buildProductOptions() {
        let html = '<option value="">- Pilih Produk -</option>';
        products.forEach(function(p) {
            html += `<option value="${p.uuid}">${p.name}</option>`;
        });
        return html;
    }

    function buildItemRows(batchIndex) {

        let html = '';

        let grouped = {};

        items.forEach(function(item) {
            if (!grouped[item.category]) {
                grouped[item.category] = [];
            }

            grouped[item.category].push(item);
        });

        Object.keys(grouped).forEach(function(category) {

            html += `
                <tr class="table-secondary">
                    <td colspan="5"><strong>${category}</strong></td>
                </tr>
            `;

            grouped[category].forEach(function(item) {

                html += `
                    <tr>
                        <td>
                            <span class="ms-3">${item.name}</span>
                        </td>

                        <td>
                            <select
                                name="batches[${batchIndex}][items][${item.uuid}][result]"
                                class="form-control">

                                <option value="">-</option>
                                <option value="OK" selected>OK</option>
                                <option value="Tidak OK">Tidak OK</option>
                            </select>
                        </td>

                        <td>
                            <input
                                type="text"
                                name="batches[${batchIndex}][items][${item.uuid}][explanation]"
                                class="form-control">
                        </td>

                        <td>
                            <input
                                type="text"
                                name="batches[${batchIndex}][items][${item.uuid}][notes]"
                                class="form-control">
                        </td>

                        <td>
                            <input
                                type="text"
                                name="batches[${batchIndex}][items][${item.uuid}][corrective_action]"
                                class="form-control">
                        </td>
                    </tr>
                `;
            });
        });

        return html;
    }

    function buildBatch(batchIndex) {
        return `
            <div class="batch-section border rounded p-3 mb-3" data-batch-index="${batchIndex}">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-5">
                        <label class="form-label">Nama Produk</label>
                        <select name="batches[${batchIndex}][product_uuid]" class="form-control" required>
                            ${buildProductOptions()}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jam</label>
                        <input type="time" name="batches[${batchIndex}][time]" class="form-control">
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-batch">
                            <i class="fas fa-trash"></i> Hapus Pergantian Produk Ini
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">Item</th>
                                <th style="min-width: 100px;">Hasil</th>
                                <th style="min-width: 150px;">Penjelasan</th>
                                <th style="min-width: 150px;">Keterangan</th>
                                <th style="min-width: 150px;">Tindakan Koreksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${buildItemRows(batchIndex)}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    // Tambah pergantian produk baru (otomatis berisi semua item)
    $('#add-batch').on('click', function() {
        $('#batches-container').append(buildBatch(nextBatchIndex));
        nextBatchIndex++;
    });

    // Hapus satu batch pergantian produk (boleh sampai batch terakhir dihapus,
    // tapi validasi "batches" minimal 1 tetap berlaku saat submit)
    $(document).on('click', '.remove-batch', function() {
        $(this).closest('.batch-section').remove();
    });
});
</script>
@endsection