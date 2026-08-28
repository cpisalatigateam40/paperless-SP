@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Verifikasi Penerapan GMP Karyawan & Sanitasi Area</h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('gmp.index') }}">
                    <input type="hidden" name="section" value="{{ request('section') }}">
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

                <button type="button"
                    class="btn btn-outline-secondary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#oldReportModal">

                    <i class="bx bx-history"></i>
                    Akses Data Lama

                </button>

                <form method="GET" action="{{ route('gmp.index') }}" class="d-flex align-items-center" style="gap: .4rem;">
                    <select name="section" class="form-select form-control" onchange="this.form.submit()">
                        <option value="">Semua Section</option>
                        <option value="gmp_karyawan" {{ request('section') == 'gmp_karyawan' ? 'selected' : '' }}>GMP Karyawan</option>
                        <option value="sanitasi_area" {{ request('section') == 'sanitasi_area' ? 'selected' : '' }}>Sanitasi Area</option>
                    </select>

                    <!-- <input type="text" name="search" class="form-control" placeholder="Cari laporan..." value="{{ request('search') }}">

                    <button type="submit" class="btn btn-outline-primary">Cari</button> -->

                    @if(request('search') || request('section'))
                        <a href="{{ route('gmp.index') }}" class="btn btn-danger" title="Reset Filter">Reset</a>
                    @endif
                </form>

                {{-- Buttons --}}
                <div class="d-flex gap-2">
                    @role('Produksi')
                    <button type="button" class="btn btn-warning btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalBulkKnown">
                        <i class="fas fa-check-double"></i> Approve (Produksi)
                    </button>
                    @endrole

                    @role('SPV QC')
                    <button type="button" class="btn btn-success btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalBulkApprove">
                        <i class="fas fa-check-circle"></i> Approve (QC)
                    </button>
                    @endrole
                </div>

                

                <x-export-pdf-modal
                    :route="route('gmp.export_pdf_bulk')"
                    title="GMP"
                    modal-id="gmp"
                    :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                />

                <x-export-excel-modal 
                :route="route('gmp.export_excel')" 
                title="Verifikasi GMP Karyawan & Sanitasi Area" />

                {{-- Modals --}}
                @role('Produksi')
                <x-bulk-approval-modal
                    prefix="known"
                    title="Produksi"
                    color="warning"
                    icon="fa-check-double"
                    action-route="gmp.bulk-known"
                    count-route="gmp.bulk-known-count"
                    label="Approve Semua"
                />
                @endrole

                @role('SPV QC')
                <x-bulk-approval-modal
                    prefix="approve"
                    title="QC"
                    color="success"
                    icon="fa-check-circle"
                    action-route="gmp.bulk-approve"
                    count-route="gmp.bulk-approve-count"
                    label="Approve Semua"
                />
                @endrole

                @can('create report')
                <a href="{{ route('gmp.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
                @endcan
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif

            <x-report-sort
                :sort-options="[
                    'latest' => 'Terbaru',
                    'report_date' => 'Tanggal Laporan',
                    'submitted_at' => 'Tanggal Submit',
                ]"
                :with-date-filter="true"
            />

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle">No</th>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Section</th>
                            <th class="align-middle">Diperiksa oleh</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($headers as $i => $header)
                        <tr>
                            <td class="align-middle">{{ $i + $headers->firstItem() }}</td>
                            <td class="align-middle">{{ $header->date->format('d-m-Y') }}</td>
                            <td class="align-middle">{{ $header->shift }}</td>
                            <td class="align-middle">
                                <span class="badge {{ $header->section === 'gmp_karyawan' ? 'bg-info' : 'bg-secondary' }}" style="color: white !important;">
                                    {{ $header->section === 'gmp_karyawan' ? 'GMP Karyawan' : 'Sanitasi Area' }}
                                </span>
                            </td>
                            <td class="align-middle">{{ $header->created_by }}</td>
                            <td class="">
                                <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $header->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('edit report')
                                <a href="{{ route('gmp.edit', $header) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @can('delete report')
                                <form action="{{ route('gmp.destroy', $header) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin hapus laporan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan

                                {{-- KNOWN --}}
                                @can('known report')
                                    @if(!$header->known_by)
                                    <form action="{{ route('gmp.known', $header->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $header->known_by }}
                                    </span>
                                    @endif
                                @endcan

                                {{-- APPROVE --}}
                                @can('approve report')
                                    @if(!$header->approved_by)
                                    <form action="{{ route('gmp.approve', $header->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $header->approved_by }}
                                    </span>
                                    @endif
                                @endcan

                                <a href="{{ route('gmp.export', $header) }}" class="btn btn-sm btn-outline-secondary" title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        <tr id="detail-{{ $header->id }}" class="d-none">
                            <td colspan="8">
                                @foreach($header->waktuPemeriksaans as $waktuIndex => $waktu)
                                <div class="mb-3">
                                    <p class="fw-bold mb-1">
                                        Waktu Pemeriksaan {{ $waktuIndex + 1 }}
                                        @if($waktu->jam_pemeriksaan) - {{ \Carbon\Carbon::parse($waktu->jam_pemeriksaan)->format('H:i') }} WIB @endif
                                    </p>

                                    @if($header->section === 'gmp_karyawan')
                                    <table class="table table-sm table-bordered mb-1">
                                        <thead class="text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>Area</th>
                                                <th>Nama Karyawan</th>
                                                <th>Seragam & APD lengkap</th>
                                                <th>Sarung tangan utuh</th>
                                                <th>Sepatu boots bersih</th>
                                                <th>Tidak pakai perhiasan & jam tangan</th>
                                                <th>Kuku & tangan bersih, tanpa luka</th>
                                                <th>Kuku tidak panjang & tidak cat kuku</th>
                                                <th>Perilaku & kebiasaan kerja</th>
                                                <th>Potensi cross contamination</th>
                                                <th>Tindakan Koreksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($waktu->employeeChecks as $j => $emp)
                                            <tr>
                                                <td class="text-center">{{ $j + 1 }}</td>
                                                <td>{{ $emp->section->section_name ?? '-' }}</td>
                                                <td>{{ $emp->employee_name }}</td>
                                                @foreach(['seragam_apd_lengkap','sarung_tangan_utuh','sepatu_boots_bersih','tidak_pakai_perhiasan','kuku_tangan_bersih','kuku_tidak_panjang','perilaku_kerja','potensi_cross_contamination'] as $field)
                                                <td class="text-center">
                                                    @if(is_null($emp->$field)) -
                                                    @elseif($emp->$field) <span class="text-success">Ok</span>
                                                    @else <span class="text-danger">Tidak OK</span>
                                                    @endif
                                                </td>
                                                @endforeach
                                                <td>{{ $emp->tindakan_koreksi ?: '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="12" class="text-center">Tidak ada data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <p class="small text-muted mb-0">Keterangan: {{ $waktu->catatan ?: '-' }}</p>
                                    @else
                                    <table class="table table-sm table-bordered mb-1">
                                        <thead class="text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>Area</th>
                                                <th>Item Verifikasi</th>
                                                <th>Std. Klorin</th>
                                                <th>Kadar Klorin (ppm)</th>
                                                <th>Suhu (°C)</th>
                                                <th>Tindakan Koreksi</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($waktu->sanitationChecks as $j => $san)
                                            <tr>
                                                <td class="text-center">{{ $j + 1 }}</td>
                                                <td>{{ $san->section->section_name ?? '-' }}</td>
                                                <td>{{ $san->item_verifikasi }}</td>
                                                <td class="text-end">{{ $san->standar_klorin ?? '-' }}</td>
                                                <td class="text-end">{{ $san->kadar_klorin ?? '-' }}</td>
                                                <td class="text-end">{{ $san->suhu ?? '-' }}</td>
                                                <td>{{ $san->tindakan_koreksi ?: '-' }}</td>
                                                <td>{{ $san->keterangan ?: '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="8" class="text-center">Tidak ada data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <p class="small text-muted mb-0">Catatan: {{ $waktu->catatan ?: '-' }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $headers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="oldReportModal" tabindex="-1" aria-labelledby="oldReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="oldReportModalLabel">
                    Akses Data Lama
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <p class="text-muted mb-3">
                    Pilih jenis report yang ingin dibuka.
                </p>

                <div class="d-grid gap-3">

                    <a href="{{ route('gmp-employee.index') }}"
                        class="btn btn-outline-primary d-flex justify-content-between align-items-center mb-3">

                        <span>
                            <i class="bx bx-file me-2"></i>
                            Verifikasi Penerapan GMP Karyawan & Sanitasi Area
                        </span>

                        <i class="bx bx-chevron-right"></i>

                    </a>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => { $('#success-alert').fadeOut('slow'); }, 3000);

    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        $('.toggle-detail').not(this).html('<i class="fas fa-eye"></i>');
        $('tr[id^="detail-"]').addClass('d-none');

        if (isHidden) {
            target.removeClass('d-none');
            $(this).html('<i class="fas fa-eye-slash"></i>');
        } else {
            target.addClass('d-none');
            $(this).html('<i class="fas fa-eye"></i>');
        }
    });
});
</script>
@endsection