@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4 class="mb-4">Daftar Pemeriksaan Metal Detector Produk</h4>
            <a href="{{ route('report_md_products.create') }}" class="btn btn-primary mb-3">Tambah Report</a>
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
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form method="POST" action="{{ route('report_md_products.destroy', $report->uuid) }}"
                                onsubmit="return confirm('Hapus report ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_md_products.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_md_products.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>
                        </td>
                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="7">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">Waktu Pengecekan</th>
                                            <th rowspan="2" class="align-middle">Nama Produk</th>
                                            <th rowspan="2" class="align-middle">Kode Produksi</th>
                                            <th rowspan="2" class="align-middle">Best Before</th>
                                            <th rowspan="2" class="align-middle">No. Program</th>
                                            <th colspan="4" class="align-middle">Fe 1.5 mm</th>
                                            <th colspan="4" class="align-middle">Non Fe 2 mm</th>
                                            <th colspan="4" class="align-middle">SUS 2.5 mm</th>
                                            <th rowspan="2" class="align-middle">Tindakan Perbaikan</th>
                                            <th rowspan="2" class="align-middle">Verifikasi setelah perbaikan</th>
                                        </tr>
                                        <tr>
                                            <th>D</th>
                                            <th>T</th>
                                            <th>B</th>
                                            <th>DL</th>
                                            <th>D</th>
                                            <th>T</th>
                                            <th>B</th>
                                            <th>DL</th>
                                            <th>D</th>
                                            <th>T</th>
                                            <th>B</th>
                                            <th>DL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report->details as $detail)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }}</td>
                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->production_code }}</td>
                                            <td>{{ $detail->best_before }}</td>
                                            <td>{{ $detail->program_number }}</td>

                                            {{-- Verifikasi Specimen --}}
                                            @php
                                            $specimens = ['fe_1_5mm', 'non_fe_2mm', 'sus_2_5mm'];
                                            $positions = ['d', 't', 'b', 'dl'];
                                            @endphp
                                            @foreach ($specimens as $specimen)
                                            @foreach ($positions as $pos)
                                            @php
                                            $posDetail = $detail->positions
                                            ->where('specimen', $specimen)
                                            ->where('position', $pos)
                                            ->first();
                                            @endphp
                                            <td>{{ $posDetail ? ($posDetail->status ? '✓' : '×') : '-' }}</td>
                                            @endforeach
                                            @endforeach

                                            <td>{{ $detail->corrective_action }}</td>
                                            <td>{{ $detail->verification ? 'Ya' : 'Tidak' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="21" class="text-center">Tidak ada detail</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-2 d-flex justify-content-end">
                                    <a href="{{ route('report_md_products.add-detail', $report->uuid) }}"
                                        class="btn btn-sm btn-secondary">
                                        Tambah Detail Pemeriksaan
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada report</td>
                    </tr>
                    @endforelse
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