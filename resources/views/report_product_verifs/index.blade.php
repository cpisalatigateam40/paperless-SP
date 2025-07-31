@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Produk</h4>
            <a href="{{ route('report_product_verifs.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
        </div>
        <div class="card-body table-responsive">
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
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $i => $report)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Delete --}}
                            <form action="{{ route('report_product_verifs.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus laporan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_product_verifs.known', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Diketahui oleh">
                                <i class="fas fa-check"></i> {{ $report->known_by }}
                            </span>
                            @endif
                            @else
                            @if($report->known_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Diketahui oleh">
                                <i class="fas fa-check"></i> {{ $report->known_by }}
                            </span>
                            @endif
                            @endcan

                            {{-- Approve --}}
                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_product_verifs.approve', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                    <i class="fas fa-thumbs-up"></i>
                                </button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Disetujui oleh">
                                <i class="fas fa-check"></i> {{ $report->approved_by }}
                            </span>
                            @endif
                            @else
                            @if($report->approved_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Disetujui oleh">
                                <i class="fas fa-check"></i> {{ $report->approved_by }}
                            </span>
                            @endif
                            @endcan

                            {{-- Export PDF --}}
                            <a href="{{ route('report_product_verifs.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="100%">
                            <div class="table-responsive p-2 border rounded">
                                <table class="table table-bordered text-center table-sm align-middle"
                                    style="font-size: 13px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2">Jam</th>
                                            <th rowspan="2">Produk</th>
                                            <th rowspan="2">Kode Produksi</th>
                                            <th rowspan="2">Expired Date</th>
                                            <th colspan="2">Standar Panjang (mm)</th>
                                            <th colspan="2">Standar Berat (gr)</th>
                                            <th rowspan="2">Diameter (mm)</th>
                                        </tr>
                                        <tr>
                                            <th>Standar</th>
                                            <th>Aktual</th>
                                            <th>Standar</th>
                                            <th>Aktual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($report->details as $detail)
                                        @php
                                        $measurements = $detail->measurements;
                                        $rowspan = $measurements->count();
                                        @endphp

                                        @for ($i = 0; $i < $rowspan; $i++) <tr>
                                            @if ($i == 0)
                                            <td rowspan="{{ $rowspan }}">
                                                {{ $detail->jam ?? '-' }}
                                            </td>
                                            <td rowspan="{{ $rowspan }}">
                                                {{ $detail->product->product_name ?? '-' }}
                                            </td>
                                            <td rowspan="{{ $rowspan }}">
                                                {{ $detail->production_code ?? '-' }}
                                            </td>
                                            <td rowspan="{{ $rowspan }}">
                                                {{ $detail->expired_date ?? '-' }}
                                            </td>
                                            <td rowspan="{{ $rowspan }}">
                                                {{ $detail->long_standard ?? '-' }}
                                            </td>
                                            @endif

                                            {{-- Panjang Aktual --}}
                                            <td>
                                                {{ $measurements[$i]->length_actual ?? '-' }}
                                            </td>

                                            @if ($i == 0)
                                            <td rowspan="{{ $rowspan }}">
                                                {{ $detail->weight_standard ?? '-' }}
                                            </td>
                                            @endif

                                            {{-- Berat Aktual --}}
                                            <td>
                                                {{ $measurements[$i]->weight_actual ?? '-' }}
                                            </td>

                                            {{-- Diameter Aktual --}}
                                            <td>
                                                {{ $measurements[$i]->diameter_actual ?? '-' }}
                                            </td>
                    </tr>
                    @endfor
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            <a href="{{ route('report_product_verifs.add-detail', $report->uuid) }}"
                class="btn btn-sm btn-secondary mt-2">
                + Tambah Detail
            </a>
        </div>
        </td>
        </tr>
        @empty
        <tr>
            <td colspan="8">Belum ada data.</td>
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