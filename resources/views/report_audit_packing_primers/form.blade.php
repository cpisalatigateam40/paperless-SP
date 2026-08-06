@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                {{ $isEdit ? 'Edit' : 'Tambah' }} Checklist Audit Kepatuhan Proses Packing Primer
            </h4>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $isEdit ? route('report_audit_packing_primers.update', $report->uuid) : route('report_audit_packing_primers.store') }}"
                  method="POST">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                {{-- A. INFORMASI AKTIVITAS --}}
                <h5>A. Informasi Aktivitas</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hari, Tanggal</label>
                        <input type="date" name="date" class="form-control"
                               value="{{ old('date', $isEdit ? optional($report->date)->format('Y-m-d') : '') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu, Shift</label>
                        <input type="text" name="shift" class="form-control" placeholder="mis: 08:00 / Shift 1"
                               value="{{ old('shift', $report->shift ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Area</label>
                        <select name="section_uuid" class="form-select form-control" required>
                            <option value="">-- Pilih Area --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->uuid }}"
                                    {{ old('section_uuid', $report->section_uuid ?? '') == $section->uuid ? 'selected' : '' }}>
                                    {{ $section->section_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tujuan</label>
                        <textarea name="tujuan" class="form-control" rows="1">{{ old('tujuan', $report->tujuan ?? 'Memastikan tidak terdapat potensi ketidaksesuaian Food Safety dan Food Quality selama proses packing primer') }}</textarea>
                    </div>
                </div>

                {{-- B. HASIL VERIFIKASI (header) --}}
                <h5>B. Hasil Verifikasi</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Line</label>
                        <input type="text" name="line" class="form-control"
                               value="{{ old('line', $report->line ?? '') }}" placeholder="mis: 1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Produk</label>
                        <select name="product_uuid" class="form-select form-control">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->uuid }}"
                                    {{ old('product_uuid', $report->product_uuid ?? '') == $product->uuid ? 'selected' : '' }}>
                                    {{ $product->product_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control"
                               value="{{ old('production_code', $report->production_code ?? '') }}" placeholder="mis: QG23801AA0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Karyawan</label>
                        <input type="text" name="karyawan" class="form-control"
                               value="{{ old('karyawan', $report->karyawan ?? '') }}" placeholder="mis: Budi">
                    </div>
                </div>

                {{-- TABEL CHECKLIST PER KATEGORI --}}
                @php
                    $categoryLabels = [
                        'food_safety' => 'FOOD SAFETY',
                        'food_quality' => 'FOOD QUALITY',
                        'process_compliance' => 'PROCESS COMPLIANCE',
                    ];
                    $existingDetails = $isEdit ? $report->details->keyBy('item_uuid') : collect();
                @endphp

                @foreach ($categoryLabels as $categoryKey => $label)
                    <h6 class="mt-4">{{ $label }}</h6>
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 3%">No</th>
                                <th>Item Verifikasi</th>
                                <th style="width: 7%" class="text-center">Yes</th>
                                <th style="width: 7%" class="text-center">No</th>
                                <th style="width: 25%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items[$categoryKey] ?? [] as $index => $item)
                                @php $detail = $existingDetails->get($item->uuid); @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->item_verifikasi }}</td>
                                    <td class="text-center">
                                        <input type="radio" name="verifikasi[{{ $item->uuid }}]" value="yes"
                                               {{ old('verifikasi.' . $item->uuid, $detail->verifikasi ?? '') == 'yes' ? 'checked' : '' }}
                                               required>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio" name="verifikasi[{{ $item->uuid }}]" value="no"
                                               {{ old('verifikasi.' . $item->uuid, $detail->verifikasi ?? '') == 'no' ? 'checked' : '' }}
                                               required>
                                    </td>
                                    <td>
                                        <input type="text" name="keterangan[{{ $item->uuid }}]" class="form-control"
                                               value="{{ old('keterangan.' . $item->uuid, $detail->keterangan ?? '') }}" placeholder="masukkan keterangan (opsional)">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Belum ada item master untuk kategori ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endforeach

                {{-- C. HASIL AUDIT & KRITERIA PENILAIAN (manual) --}}
                <h5 class="mt-4">C. Hasil Audit & Kriteria Penilaian</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Hasil Audit</label>
                        <select name="audit_score" id="audit_score" class="form-select form-control">
                            <option value="">-- Pilih --</option>
                            @php $currentScore = old('audit_score', $report->audit_score ?? ''); @endphp
                            <option value="10/10" {{ $currentScore == '10/10' ? 'selected' : '' }}>10/10</option>
                            <option value="9/10" {{ $currentScore == '9/10' ? 'selected' : '' }}>9/10</option>
                            <option value="<=8/10" {{ $currentScore == '<=8/10' ? 'selected' : '' }}>&le;8/10</option>
                            <option value="food_safety" {{ $currentScore == 'food_safety' ? 'selected' : '' }}>Food Safety</option>
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Tindakan</label>
                        <textarea name="tindakan" id="tindakan" class="form-control" rows="1" placeholder="Masukkan tindakan (terisi otomatis)">{{ old('tindakan', $report->tindakan ?? '') }}</textarea>
                        <div class="form-text italic"><small><i>Terisi otomatis sesuai Hasil Audit, tapi tetap bisa diedit manual.</i></small>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('report_audit_packing_primers.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const tindakanMap = {
            '10/10': 'Proses sesuai, produksi dilanjutkan.',
            '9/10': 'Lakukan perbaikan langsung (briefing, cleaning, penyesuaian mesin), kemudian verifikasi ulang.',
            '<=8/10': 'Tim melakukan investigasi, produk dihold sesuai kebutuhan. Verifikasi efektivitas proses sebelum produksi dilanjutkan.',
            'food_safety': 'Hentikan sementara proses, isolasi produk terdampak, investigasi akar penyebab, dan tindakan korektif sesuai prosedur.',
        };

        const auditScoreEl = document.getElementById('audit_score');
        const tindakanEl = document.getElementById('tindakan');

        auditScoreEl.addEventListener('change', function () {
            if (tindakanMap[this.value]) {
                tindakanEl.value = tindakanMap[this.value];
            }
        });
    })();
</script>
@endsection