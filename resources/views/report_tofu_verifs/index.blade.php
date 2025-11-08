@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan Verifikasi Produk Tofu</h5>
            <a href="{{ route('report_tofu_verifs.create') }}" class="btn btn-sm btn-primary">+ New Report</a>
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

            <div class="table-responsive">
                <table class="table  table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $index => $report)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit --}}
                                <a href="{{ route('report_tofu_verifs.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Update Laporan">
                                    <i class="fas fa-pen"></i>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('report_tofu_verifs.destroy', $report->uuid) }}" method="POST"
                                    onsubmit="return confirm('Delete this report?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_tofu_verifs.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_tofu_verifs.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_tofu_verifs.export', $report->uuid) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="7">
                                <div class="table-responsive mt-2">
                                    @php
                                    $products = $report->productInfos;
                                    $weights = $report->weightVerifs;
                                    $defects = $report->defectVerifs;

                                    $getValue = function ($collection, $type, $index, $field) {
                                    return optional($collection->where(
                                    'weight_category',
                                    $type
                                    )->values()->get($index))->{$field} ?? '-';
                                    };
                                    $getDefect = function ($collection, $type, $index, $field) {
                                    return optional($collection->where(
                                    'defect_type',
                                    $type
                                    )->values()->get($index))->{$field} ?? '-';
                                    };
                                    @endphp

                                    <table class="table table-bordered table-sm text-center align-middle">
                                        <tbody>
                                            {{-- Row: Kode Produksi --}}
                                            <tr>
                                                <td class="text-start">Kode Produksi</td>
                                                @foreach($products as $p)
                                                <td>{{ $p->production_code }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Row: Expired Date --}}
                                            <tr>
                                                <td class="text-start">Expired Date</td>
                                                @foreach($products as $p)
                                                <td>{{ $p->expired_date }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Row: Jumlah Sampel --}}
                                            <tr>
                                                <td class="text-start">Jumlah Sampel (pcs)</td>
                                                @foreach($products as $p)
                                                <td>{{ $p->sample_amount }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Header: Pemeriksaan Berat --}}
                                            <tr class="table-light">
                                                <th class="text-start" colspan="{{ $products->count() + 1 }}">
                                                    Pemeriksaan Berat</th>
                                            </tr>

                                            {{-- Berat: Under --}}
                                            <tr>
                                                <td class="text-start">- Under (&lt; 11gr/pc)</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getValue($weights, 'under', $i, 'turus') }}<br>
                                                    Jumlah: {{ $getValue($weights, 'under', $i, 'total') }}<br>
                                                    %: {{ $getValue($weights, 'under', $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>

                                            {{-- Berat: Standard --}}
                                            <tr>
                                                <td class="text-start">- Standart (11 - 13 gr/pc)</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getValue($weights, 'standard', $i, 'turus') }}<br>
                                                    Jumlah: {{ $getValue($weights, 'standard', $i, 'total') }}<br>
                                                    %: {{ $getValue($weights, 'standard', $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>

                                            {{-- Berat: Over --}}
                                            <tr>
                                                <td class="text-start">- Over (&gt;13 gr/pc)</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getValue($weights, 'over', $i, 'turus') }}<br>
                                                    Jumlah: {{ $getValue($weights, 'over', $i, 'total') }}<br>
                                                    %: {{ $getValue($weights, 'over', $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>

                                            {{-- Header: Pemeriksaan Defect --}}
                                            <tr class="table-light">
                                                <th class="text-start" colspan="{{ $products->count() + 1 }}">
                                                    Pemeriksaan Defect</th>
                                            </tr>

                                            @php
                                            $defectTypes = [
                                            'hole' => 'Berlubang',
                                            'stain' => 'Noda',
                                            'asymmetry' => 'Bentuk tidak bulat simetris',
                                            'other' => 'Lain-lain',
                                            'good' => 'Produk bagus',
                                            'note' => 'Keterangan',
                                            ];
                                            @endphp

                                            {{-- Loop setiap jenis defect --}}
                                            @foreach($defectTypes as $key => $label)
                                            <tr>
                                                <td class="text-start">- {{ $label }}</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getDefect($defects, $key, $i, 'turus') }}<br>
                                                    Jumlah: {{ $getDefect($defects, $key, $i, 'total') }}<br>
                                                    %: {{ $getDefect($defects, $key, $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>
                                            @endforeach

                                            {{-- Keterangan (jika ingin tambahkan nanti) --}}
                                            {{-- <tr>
                                                        <td class="text-start">Keterangan</td>
                                                        @foreach ($products as $p)
                                                        <td>-</td>
                                                        @endforeach
                                                    </tr> --}}
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="6">No reports found.</td>
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