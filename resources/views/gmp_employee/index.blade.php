@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5> Laporan Verifikasi GMP Karyawan & Kontrol Sanitasi</h5>
            <a href="{{ route('gmp-employee.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                @if(session('success'))
                <div id="success-alert" class="alert alert-success">
                    {{ session('success') }}
                </div>
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
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Waktu</th>
                                <th>Area</th>
                                <th>Ketidaksesuaian</th>
                                <th>Dibuat oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $index => $report)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $report->date }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>{{ $report->created_at->format('H:i') }}</td>
                                <td> {{ $report->details->first()->section_name ?? '-' }}</td>
                                <td>
                                    @if ($report->ketidaksesuaian > 0)
                                    Ada
                                    @else
                                    -
                                    @endif
                                </td>

                                <td>{{ $report->created_by }}</td>
                                <td class="d-flex flex-wrap align-items-center" style="gap: .3rem;">
                                    {{-- Lihat Detail --}}
                                    <button class="btn btn-sm btn-info" data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Update Laporan --}}
                                    <!-- <a href="{{ route('gmp-employee.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Update Laporan">
                                        <i class="fas fa-pen"></i>
                                    </a> -->

                                    @php
                                        $user = auth()->user();
                                        $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                    @endphp

                                    @if($canEdit)
                                        <a href="{{ route('gmp-employee.edit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning" title="Edit Laporan">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    @can('edit report')
                                    <a href="{{ route('gmp-employee.editnext', $report->uuid) }}"
                                        class="btn btn-sm btn-danger" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan

                                    {{-- Hapus --}}
                                    <form action="{{ route('gmp-employee.destroy', $report->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus laporan ini?')"
                                        class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>

                                    {{-- Known --}}
                                    @can('known report')
                                    @if(!$report->known_by)
                                    <form action="{{ route('gmp-employee.known', $report->id) }}" method="POST"
                                        onsubmit="return confirm('Ketahui laporan ini?')" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge bg-success text-white rounded-pill px-2"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                        title="Diketahui">
                                        <i class="fas fa-check"></i> {{ $report->known_by }}
                                    </span>
                                    @endif
                                    @else
                                    @if($report->known_by)
                                    <span class="badge bg-success text-white rounded-pill px-2"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                        title="Diketahui">
                                        <i class="fas fa-check"></i> {{ $report->known_by }}
                                    </span>
                                    @endif
                                    @endcan

                                    {{-- Approve --}}
                                    @can('approve report')
                                    @if(!$report->approved_by)
                                    <form action="{{ route('gmp-employee.approve', $report->id) }}" method="POST"
                                        onsubmit="return confirm('Setujui laporan ini?')" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge bg-success text-white rounded-pill px-2"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                        title="Disetujui">
                                        <i class="fas fa-check"></i> {{ $report->approved_by }}
                                    </span>
                                    @endif
                                    @else
                                    @if($report->approved_by)
                                    <span class="badge bg-success text-white rounded-pill px-2"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                        title="Disetujui">
                                        <i class="fas fa-check"></i> {{ $report->approved_by }}
                                    </span>
                                    @endif
                                    @endcan

                                    {{-- Cetak PDF --}}
                                    <a href="{{ route('gmp-employee.export.pdf', $report->uuid) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>

                            </tr>

                            {{-- Detail Row --}}
                            <tr class="collapse" id="detail-{{ $report->id }}">
                                <td colspan="8">
                                    <strong>Area:</strong> {{ $report->area->name ?? '-' }}<br><br>

                                    <h6 class="fw-bold mt-4">GMP Karyawan</h6>

                                    <table class="table table-sm mt-2">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">No</th>
                                                <th class="align-middle">Jam Inspeksi</th>
                                                <th class="align-middle">Bagian</th>
                                                <th class="align-middle">Nama Karyawan</th>
                                                <th class="align-middle">Catatan</th>
                                                <th class="align-middle">Tindakan Koreksi</th>
                                                <th class="align-middle">Verifikasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach($report->details as $detail)
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td>{{ $detail->inspection_hour }}</td>
                                                <td>{{ $detail->section_name }}</td>
                                                <td>{{ $detail->employee_name }}</td>
                                                <td>{{ $detail->notes }}</td>
                                                <td>{{ $detail->corrective_action }}</td>
                                                <td>{!! $detail->verification ? '✔' : '✘' !!}</td>
                                            </tr>
                                            {{-- Koreksi lanjutan --}}
                                            @foreach($detail->followups as $index => $followup)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td colspan="2">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                                <td></td>
                                                <td>{{ $followup->notes }}</td>
                                                <td>{{ $followup->action }}</td>
                                                <td>{!! $followup->verification ? '✔' : '✘' !!}</td>
                                            </tr>
                                            @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('gmp-employee.detail.create', $report->id) }}"
                                            class="btn btn-sm btn-primary mt-3">
                                            + Tambah Detail
                                        </a>
                                    </div>

                                    {{-- TABEL SANITASI AREA --}}
                                    @if($report->sanitationCheck && $report->sanitationCheck->count())
                                    <h6 class="fw-bold mt-4">Sanitasi Area</h6>
                                    @php
                                    $sanitationCheck = $report->sanitationCheck;
                                    $hour1 = $sanitationCheck?->hour_1 ?? 'Jam 1';
                                    $hour2 = $sanitationCheck?->hour_2 ?? 'Jam 2';
                                    @endphp

                                    <table class="table table-bordered table-sm mt-2">
                                        <thead class="text-center align-middle">
                                            <tr>
                                                <th rowspan="3">No</th>
                                                <th rowspan="3">Area</th>
                                                <th rowspan="3">Std Klorin (ppm)</th>
                                                <th colspan="4">Hasil Pengecekan</th>
                                                <th rowspan="3">Keterangan</th>
                                                <th rowspan="3">Tindakan Koreksi</th>
                                                <th rowspan="3">Verifikasi</th>
                                            </tr>
                                            <tr>
                                                <th colspan="2">Jam 1: <span
                                                        style="font-weight: 400">{{ $hour1 }}</span>
                                                </th>
                                                <th colspan="2">Jam 2: <span
                                                        style="font-weight: 400">{{ $hour2 }}</span>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>Kadar Klorin (ppm)</th>
                                                <th>Suhu (°C)</th>
                                                <th>Kadar Klorin (ppm)</th>
                                                <th>Suhu (°C)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @if(is_iterable($sanitationCheck->sanitationArea ?? null))
                                            @foreach($sanitationCheck->sanitationArea as $area)
                                            @php
                                            $jam1 = $area->sanitationResult->firstWhere('hour_to', 1);
                                            $jam2 = $area->sanitationResult->firstWhere('hour_to', 2);
                                            @endphp
                                            <tr class="text-center align-middle">
                                                <td>{{ $no++ }}</td>
                                                <td class="text-start">{{ $area->area_name ?? '-' }}</td>
                                                <td>{{ $area->chlorine_std ?? '-' }}</td>
                                                <td>{{ $jam1?->chlorine_level ?? '-' }}</td>
                                                <td>{{ $jam1?->temperature ?? '-' }}</td>
                                                <td>{{ $jam2?->chlorine_level ?? '-' }}</td>
                                                <td>{{ $jam2?->temperature ?? '-' }}</td>
                                                <td class="text-start">{{ $area->notes ?? '-' }}</td>
                                                <td class="text-start">{{ $area->corrective_action ?? '-' }}</td>
                                                <td>
                                                    @if($area->verification === 1)
                                                    ✔
                                                    @elseif($area->verification === 0)
                                                    ✘
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Koreksi lanjutan tampil rapi, catatan masuk ke kolom Keterangan --}}
                                            @foreach($area->followups as $index => $followup)
                                            <tr class="table-secondary text-center align-middle">
                                                <td></td> {{-- kosongkan no --}}
                                                <td colspan="6" class="text-start">↳ Koreksi Lanjutan #{{ $index + 1 }}
                                                </td>
                                                <td class="text-start">{{ $followup->notes ?? '-' }}</td>
                                                <td class="text-start">{{ $followup->action ?? '-' }}</td>
                                                <td>
                                                    @if($followup->verification === 1)
                                                    ✔
                                                    @elseif($followup->verification === 0)
                                                    ✘
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach

                                            @endforeach
                                            @endif
                                        </tbody>

                                    </table>
                                    @else
                                    <p class="text-muted">Belum ada data sanitasi area.</p>
                                    @endif
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada laporan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $reports->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection