@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Daftar Laporan GMP Karyawan & Kontrol Sanitasi</h5>
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Area</th>
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
                                    <td> {{ $report->details->first()->section_name ?? '-' }}</td>
                                    <td>{{ $report->created_by }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#detail-{{ $report->id }}">
                                            Lihat Detail
                                        </button>

                                         <a href="{{ route('gmp-employee.edit', $report->uuid) }}" class="btn btn-sm btn-warning">
                                            Update Laporan
                                        </a>

                                        <form action="{{ route('gmp-employee.destroy', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>

                                        <a href="{{ route('gmp-employee.export.pdf', $report->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary">🖨 Cetak PDF</a>

                                        @can('approve report')
                                        @if(!$report->approved_by)
                                            <form action="{{ route('gmp-employee.approve', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                        @else
                                            <span class="badge bg-success" style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                                Disetujui oleh {{ $report->approved_by }}
                                            </span>
                                        @endif
                                        @else
                                            @if($report->approved_by)
                                                <span class="badge bg-success" style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                                    Disetujui oleh {{ $report->approved_by }}
                                                </span>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>

                                {{-- Detail Row --}}
                                <tr class="collapse" id="detail-{{ $report->id }}">
                                    <td colspan="6">
                                        <strong>Area:</strong> {{ $report->area->name ?? '-' }}<br><br>

                                        <h6 class="fw-bold mt-4">GMP Karyawan</h6>
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Jam Inspeksi</th>
                                                    <th>Bagian</th>
                                                    <th>Nama Karyawan</th>
                                                    <th>Catatan</th>
                                                    <th>Tindakan Koreksi</th>
                                                    <th>Verifikasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report->details as $i => $detail)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $detail->inspection_hour }}</td>
                                                        <td>{{ $detail->section_name }}</td>
                                                        <td>{{ $detail->employee_name }}</td>
                                                        <td>{{ $detail->notes }}</td>
                                                        <td>{{ $detail->corrective_action }}</td>
                                                        <td>{{ $detail->verification ? '✔' : '✘' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('gmp-employee.detail.create', $report->id) }}" class="btn btn-sm btn-primary mt-3">+ Tambah Detail</a>
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
                                                <thead class="table-light text-center align-middle">
                                                    <tr>
                                                        <th rowspan="3" style="border: 1px solid rgb(226, 220, 220);">No</th>
                                                        <th rowspan="3" style="border: 1px solid rgb(226, 220, 220);">Area</th>
                                                        <th rowspan="3" style="border: 1px solid rgb(226, 220, 220);">Std Klorin (ppm)</th>
                                                        <th colspan="4" style="border: 1px solid rgb(226, 220, 220);">Hasil Pengecekan</th>
                                                        <th rowspan="3" style="border: 1px solid rgb(226, 220, 220);">Keterangan</th>
                                                        <th rowspan="3" style="border: 1px solid rgb(226, 220, 220);">Tindakan Koreksi</th>
                                                        <th rowspan="3" style="border: 1px solid rgb(226, 220, 220);">Verifikasi</th>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="2" style="border: 1px solid rgb(226, 220, 220);">Jam 1: <span style="font-weight: 400">{{ $hour1 }}</span> </th>
                                                        <th colspan="2" style="border: 1px solid rgb(226, 220, 220);">Jam 2: <span style="font-weight: 400">{{ $hour2 }}</span> </th>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid rgb(226, 220, 220);">Kadar Klorin (ppm)</th>
                                                        <th style="border: 1px solid rgb(226, 220, 220);">Suhu (°C)</th>
                                                        <th style="border: 1px solid rgb(226, 220, 220);">Kadar Klorin (ppm)</th>
                                                        <th style="border: 1px solid rgb(226, 220, 220);">Suhu (°C)</th>
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
                                                                    @if($sanitationCheck->verification === 1)
                                                                        ✔
                                                                    @elseif($sanitationCheck->verification === 0)
                                                                        ✘
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                            </tr>
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