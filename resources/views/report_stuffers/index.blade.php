@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Report Stuffers</h4>

            <a href="{{ route('report_stuffers.create') }}" class="btn btn-primary btn-sm">+ Buat Report Baru</a>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ optional($report->area)->name }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button type="button" class="btn btn-sm btn-info"
                                onclick="toggleDetail({{ $report->id }})">Lihat Detail</button>

                            <form action="{{ route('report_stuffers.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin hapus?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_stuffers.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_stuffers.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>

                        </td>
                    </tr>

                    <tr id="detail-{{ $report->id }}" class="d-none">
                        <td colspan="5">
                            <div class="mb-3">
                                <h6>Rekap Stuffer</h6>
                                <table class="table table-sm table-bordered text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2">No</th>
                                            <th rowspan="2">Produk</th>
                                            <th rowspan="2">Standar Berat (gram)</th>
                                            <th colspan="2">HITECH</th>
                                            <th colspan="2">TOWNSEND</th>
                                            <th rowspan="2">Keterangan</th>
                                        </tr>
                                        <tr>
                                            <th>Range</th>
                                            <th>Avg</th>
                                            <th>Range</th>
                                            <th>Avg</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $grouped = $report->detailStuffers->groupBy('product_uuid'); @endphp
                                        @forelse ($grouped as $product_uuid => $items)
                                        @php
                                        $product = optional($items->first()->product)->product_name ?? '-';
                                        $standard = $items->first()->standard_weight ?? '-';
                                        $note = $items->first()->note ?? '-';
                                        $hitech = $items->firstWhere('machine_name','Hitech');
                                        $townsend = $items->firstWhere('machine_name','Townsend');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $product }}</td>
                                            <td>{{ $standard }}</td>
                                            <td>{{ $hitech->range ?? '-' }}</td>
                                            <td>{{ $hitech->avg ?? '-' }}</td>
                                            <td>{{ $townsend->range ?? '-' }}</td>
                                            <td>{{ $townsend->avg ?? '-' }}</td>
                                            <td>{{ $note }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                <h6>Cooking Loss</h6>
                                <table class="table table-sm table-bordered text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>% Cooking Loss Fessmann</th>
                                            <th>% Cooking Loss Maurer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $groupedLoss = $report->cookingLossStuffers->groupBy('product_uuid');
                                        @endphp
                                        @forelse ($groupedLoss as $product_uuid => $items)
                                        @php
                                        $product = optional($items->first()->product)->product_name ?? '-';
                                        $fessmann = $items->firstWhere('machine_name','Fessmann');
                                        $maurer = $items->firstWhere('machine_name','Maurer');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $product }}</td>
                                            <td>{{ $fessmann->percentage ?? '-' }}</td>
                                            <td>{{ $maurer->percentage ?? '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <a href="{{ route('report_stuffers.add-detail', $report->uuid) }}"
                                class="btn btn-sm btn-primary mb-1 mt-3">+ Tambah Detail</a>
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

function toggleDetail(id) {
    let row = document.getElementById('detail-' + id);
    row.classList.toggle('d-none');
}
</script>
@endsection