@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Penerapan GMP Karyawan & Sanitasi Area (Data Audit)
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('gmp.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, section, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('gmp.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('gmp.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Data Operasional
                </a>
                @endhasanyrole
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
            @endif
            @if ($errors->any())
                <div id="error-alert" class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


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
                        @forelse($records as $header)
                        <tr>
                            <td class="align-middle">{{ $records->firstItem() + $loop->index }}</td>
                            <td class="align-middle">{{ $header->date->format('d-m-Y') }}</td>
                            <td class="align-middle">{{ $header->shift }}</td>
                            <td class="align-middle">
                                <span class="badge {{ $header->section === 'gmp_karyawan' ? 'bg-info' : 'bg-secondary' }}"
                                    style="color: white !important;">
                                    {{ $header->section === 'gmp_karyawan' ? 'GMP Karyawan' : 'Sanitasi Area' }}
                                </span>
                            </td>
                            <td class="align-middle">{{ $header->created_by }}
                                @if($header->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $header->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @can('edit report')
                                <a href="{{ $header->is_audit
                                        ? route('gmp.edit', $header)
                                        : route('gmp.copy-to-audit', $header->uuid) }}"
                                    class="btn btn-sm btn-warning"
                                    title="{{ $header->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus: hanya salinan audit --}}
                                @if($header->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('gmp.destroy', $header) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin hapus data audit ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- KNOWN --}}
                                @can('known report')
                                    @if(!$header->known_by)
                                        @if($header->is_audit)
                                        <form action="{{ route('gmp.known', $header->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Diketahui">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        @endif
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
                                        @if($header->is_audit)
                                        <form action="{{ route('gmp.approve', $header->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $header->approved_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Export PDF --}}
                                <a href="{{ route('gmp.export', $header) }}" class="btn btn-sm btn-outline-secondary"
                                    title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$header" route-prefix="gmp" />
                                @endhasanyrole
                            </td>
                        </tr>

                        <tr id="detail-{{ $header->id }}" class="d-none">
                            <td colspan="6">
                                @foreach($header->waktuPemeriksaans as $waktuIndex => $waktu)
                                <div class="mb-3">
                                    <p class="fw-bold mb-1">
                                        Waktu Pemeriksaan {{ $waktuIndex + 1 }}
                                        @if($waktu->jam_pemeriksaan)
                                            - {{ \Carbon\Carbon::parse($waktu->jam_pemeriksaan)->format('H:i') }} WIB
                                        @endif
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
                        <tr><td colspan="6" class="text-center">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => { $('#success-alert, #info-alert, #error-alert').fadeOut('slow'); }, 3000);

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