@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Checklist Audit Kepatuhan Proses Packing Primer</h5>

            <div class="d-flex align-items-center" style="gap: .4rem;">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report_audit_packing_primers.index') }}">
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    <input type="hidden" name="section_uuid" value="{{ request('section_uuid') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <select name="area"
                            class="form-select form-control form-control"
                            onchange="this.form.submit()">
                        <option value="">Semua Area</option>

                        @foreach($areas as $area)
                            <option value="{{ $area->uuid }}"
                                {{ request('area') == $area->uuid ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endhasanyrole

                {{-- 🔍 SEARCH --}}
                <form method="GET"
                    action="{{ route('report_audit_packing_primers.index') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

                    {{-- pertahankan filter lain yang mungkin sedang aktif --}}
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    <input type="hidden" name="section_uuid" value="{{ request('section_uuid') }}">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari nama produk..."
                        value="{{ request('search') }}"
                    >

                    {{-- 🔍 BUTTON CARI --}}
                    <button type="submit" class="btn btn-outline-primary">
                        Cari
                    </button>

                    {{-- 🔄 RESET --}}
                    @if(request('search') || request('date') || request('section_uuid'))
                        <a href="{{ route('report_audit_packing_primers.index') }}"
                        class="btn btn-danger"
                        title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </form>

                <x-export-pdf-modal
                    :route="route('report_audit_packing_primers.exportPdfBulk')"
                    title="Audit Packing Primer"
                    modal-id="modalExportPdfAuditPackingPrimer"
                    :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                />

                @can('create report')
                <a href="{{ route('report_audit_packing_primers.create') }}" class="btn btn-primary">
                    Tambah Laporan
                </a>
                @endcan
            </div>
            
        </div>
        <div class="card-body" style="padding-top: 1rem !important;">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary">Filter</button>
                    <a href="{{ route('report_audit_packing_primers.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form> -->

            <x-report-sort
                :sort-options="[
                    'latest' => 'Terbaru',
                    'production_code' => 'Kode Produksi',
                    'report_date' => 'Tanggal Laporan',
                    'submitted_at' => 'Tanggal Submit',
                ]"
                :with-date-filter="true"
            />

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Area</th>
                        <th>Line</th>
                        <th>Produk</th>
                        <th>Kode Produksi</th>
                        <th>Hasil Audit</th>
                        <th style="width: 200px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ optional($report->date)->format('d-m-Y') }}</td>
                            <td>{{ $report->section->section_name ?? '-' }}</td>
                            <td>{{ $report->line ?? '-' }}</td>
                            <td>{{ $report->product->product_name ?? '-' }}</td>
                            <td>{{ $report->production_code ?? '-' }}</td>
                            <td>{{ $report->audit_score ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->uuid }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
            
                                @can('edit report')
                                <a href="{{ route('report_audit_packing_primers.edit', $report->uuid) }}"
                                   class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                @endcan

                                @can('delete report')
                                <form action="{{ route('report_audit_packing_primers.destroy', $report->uuid) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus checklist ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan

                                <a href="{{ route('report_audit_packing_primers.export-pdf', $report->uuid) }}" target="_blank"
                                    class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->uuid }}" class="d-none">
                            <td colspan="8">
                                <div class="p-3 bg-light">

                                    {{-- A. INFORMASI AKTIVITAS --}}
                                    <h6 class="fw-bold">A. Informasi Aktivitas</h6>
                                    <table class="table table-sm table-borderless mb-3" style="max-width: 500px">
                                        <tr>
                                            <td style="width: 160px">Hari, Tanggal</td>
                                            <td>: {{ optional($report->date)->translatedFormat('l, d-m-Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Waktu, Shift</td>
                                            <td>: {{ $report->shift ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Area</td>
                                            <td>: {{ $report->section->section_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="align-top">Tujuan</td>
                                            <td>: {{ $report->tujuan ?? '-' }}</td>
                                        </tr>
                                    </table>

                                    {{-- B. HASIL VERIFIKASI (header) --}}
                                    <h6 class="fw-bold">B. Hasil Verifikasi</h6>
                                    <table class="table table-sm table-borderless mb-3" style="max-width: 500px">
                                        <tr>
                                            <td style="width: 160px">Line</td>
                                            <td>: {{ $report->line ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Produk</td>
                                            <td>: {{ $report->product->product_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kode Produksi</td>
                                            <td>: {{ $report->production_code ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Karyawan</td>
                                            <td>: {{ $report->karyawan ?? '-' }}</td>
                                        </tr>
                                    </table>

                                    {{-- TABEL PER KATEGORI, sama seperti form QM 29/00 --}}
                                    @php
                                        $categoryLabels = [
                                            'food_safety' => 'FOOD SAFETY',
                                            'food_quality' => 'FOOD QUALITY',
                                            'process_compliance' => 'PROCESS COMPLIANCE',
                                        ];
                                        $groupedDetails = $report->details->groupBy(fn ($d) => $d->item->category ?? '');
                                    @endphp

                                    @foreach ($categoryLabels as $categoryKey => $label)
                                        <h6 class="fw-bold mt-3">{{ $label }}</h6>
                                        <table class="table table-sm table-bordered mb-3">
                                            <thead class="text-center table-light">
                                                <tr>
                                                    <th style="width: 4%">No</th>
                                                    <th>Item Verifikasi</th>
                                                    <th style="width: 7%">Yes</th>
                                                    <th style="width: 7%">No</th>
                                                    <th style="width: 25%">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($groupedDetails->get($categoryKey, collect()) as $index => $detail)
                                                    <tr>
                                                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                                                        <td class="align-middle">{{ $detail->item->item_verifikasi ?? '-' }}</td>
                                                        <td class="text-center align-middle">
                                                            {{ $detail->verifikasi == 'yes' ? '✓' : '' }}
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            {{ $detail->verifikasi == 'no' ? '✓' : '' }}
                                                        </td>
                                                        <td class="align-middle">{{ $detail->keterangan ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Tidak ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    @endforeach

                                    {{-- C. HASIL AUDIT & KRITERIA PENILAIAN --}}
                                    <h6 class="fw-bold mt-3">C. Hasil Audit & Kriteria Penilaian</h6>
                                    <table class="table table-sm table-borderless mb-0" style="max-width: 700px">
                                        <tr>
                                            <td style="width: 160px">Hasil Audit</td>
                                            <td>: {{ $report->audit_score ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="align-top">Tindakan</td>
                                            <td>: {{ $report->tindakan ?? '-' }}</td>
                                        </tr>
                                    </table>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $reports->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.dataset.target);
            target.classList.toggle('d-none');
        });
    });
</script>
@endsection