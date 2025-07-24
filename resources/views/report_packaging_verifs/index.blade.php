@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Pemeriksaan Kemasan Plastik</h4>
            <a href="{{ route('report_packaging_verifs.create') }}" class="btn btn-primary btn-sm">+ Tambah Report</a>
        </div>

        <div class="card-body">
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
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Dibuat oleh</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ optional($report->area)->name }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_packaging_verifs.destroy', $report->uuid) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Yakin hapus?')"
                                    class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_packaging_verifs.approve', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                Disetujui oleh {{ $report->approved_by }}
                            </span>
                            @endif
                            @else
                            @if($report->approved_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                Disetujui oleh {{ $report->approved_by }}
                            </span>
                            @endif
                            @endcan

                            <a href="{{ route('report_packaging_verifs.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-outline-secondary btn-sm">
                                🖨 Cetak PDF
                            </a>

                        </td>
                    </tr>

                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="100%">
                            <div class="table-responsive">
                                @php
                                $details = $report->details;
                                @endphp

                                <table class="table table-bordered table-sm text-center align-middle mb-4">
                                    {{-- Header atas --}}
                                    <tr>
                                        <th rowspan="2">Jam</th>
                                        <th rowspan="2">Produk</th>
                                        <th rowspan="2">Kode Produksi</th>
                                        <th rowspan="2">Expired date</th>
                                        <th colspan="2">In cutting</th>
                                        <th colspan="2">Proses Pengemasan</th>
                                        <th colspan="2">Hasil Sealing</th>
                                        <th rowspan="2">Isi Per-Pack</th>
                                        <th colspan="2">Berat Produk Per Plastik (gr)</th>
                                    </tr>
                                    <tr>
                                        <th>Manual</th>
                                        <th>Mesin</th>
                                        <th>Thermoformer</th>
                                        <th>Manual</th>
                                        <th>Kondisi Seal</th>
                                        <th>Vacum</th>
                                        <th>Standar</th>
                                        <th>Aktual</th>
                                    </tr>

                                    {{-- Data detail --}}
                                    @foreach($details as $d)
                                    @php
                                    $checklist = $d->checklist;
                                    @endphp

                                    @for($i = 1; $i <= 5; $i++) <tr>
                                        @if($i == 1)
                                        <td rowspan="5">{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
                                        <td rowspan="5">{{ $d->product->product_name ?? '-' }}</td>
                                        <td rowspan="5">{{ $d->production_code }}</td>
                                        <td rowspan="5">{{ $d->expired_date }}</td>
                                        @endif

                                        <td>{{ $checklist?->{'in_cutting_manual_' . $i} ?? '-' }}</td>
                                        <td>{{ $checklist?->{'in_cutting_machine_' . $i} ?? '-' }}</td>
                                        <td>{{ $checklist?->{'packaging_thermoformer_' . $i} ?? '-' }}</td>
                                        <td>{{ $checklist?->{'packaging_manual_' . $i} ?? '-' }}</td>
                                        <td>{{ $checklist?->{'sealing_condition_' . $i} ?? '-' }}</td>
                                        <td>{{ $checklist?->{'sealing_vacuum_' . $i} ?? '-' }}</td>
                                        <td>{{ $checklist?->{'content_per_pack_' . $i} ?? '-' }}</td>
                                        @if($i == 1)
                                        <td rowspan="5">{{ $checklist?->standard_weight ?? '-' }}</td>
                                        @endif
                                        <td>{{ $checklist?->{'actual_weight_' . $i} ?? '-' }}</td>
                    </tr>
                    @endfor

                    {{-- Tambahkan baris QC & KR --}}
                    <tr>
                        <td colspan="3" class="text-start">
                            QC: {{ $d->qc_verif ?? '-' }}
                        </td>
                        <td colspan="3" class="text-start">
                            KR: {{ $d->kr_verif ?? '-' }}
                        </td>
                        <td colspan="7"></td>
                    </tr>
                    @endforeach


            </table>
        </div>
        <div class="d-flex justify-content-end">
            <a href="{{ route('report_packaging_verifs.add-detail', $report->uuid) }}" class="btn btn-secondary btn-sm">
                Tambah Detail
            </a>
        </div>

        </td>
        </tr>
        @endforeach
        </tbody>
        </table>
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