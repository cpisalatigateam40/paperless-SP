@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Laporan Pemeriksaan Proses Thawing</h4>

            <div class="d-flex align-items-center" style="gap:.4rem;">

                <form method="GET" action="{{ route('report_thawings.index') }}" class="d-flex align-items-center"
                    style="gap:.4rem;">
                    <input type="text" name="search" class="form-control" placeholder="Cari laporan..."
                        value="{{ request('search') }}">

                    <button type="submit" class="btn btn-outline-primary">
                        Cari
                    </button>

                    @if(request('search'))
                    <a href="{{ route('report_thawings.index') }}" class="btn btn-danger">
                        Reset
                    </a>
                    @endif
                </form>

                {{-- Tombol Export Excel --}}
                <x-export-excel-modal 
                    :route="route('report_thawings.export')" 
                    title="Pemeriksaan Thawing" />

                @can('create report')
                <a href="{{ route('report_thawings.create') }}" class="btn btn-primary btn-sm">
                    Tambah Laporan
                </a>
                @endcan

            </div>
        </div>


        <div class="card-body">

            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif


            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th width="400" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($reports as $i => $report)

                        <tr>

                            <td>{{ $i + $reports->firstItem() }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}
                            </td>

                            <td class="text-center">
                                {{ $report->shift }}
                            </td>

                            <td>
                                {{ $report->created_at->format('H:i') }}
                            </td>

                            <td>
                                {{ $report->area->name ?? '-' }}
                            </td>

                            <td>
                                {{ $report->created_by }}
                            </td>

                            <td class="text-center">

                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->uuid }}">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @php
                                $user = auth()->user();
                                $canEdit = $user->hasRole(['admin', 'SPV QC']) ||
                                $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                <a href="{{ route('report_thawings.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_thawings.known', $report->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Ketahui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    <i class="fas fa-check"></i> {{ $report->known_by }}
                                </span>
                                @endif
                                @else
                                @if($report->known_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    <i class="fas fa-check"></i> {{ $report->known_by }}
                                </span>
                                @endif
                                @endcan

                                {{-- Approve --}}
                                @can('approve report')
                                @if(!$report->approved_by)
                                <form action="{{ route('report_thawings.approve', $report->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Setujui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-thumbs-up"></i>
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    <i class="fas fa-check"></i> {{ $report->approved_by }}
                                </span>
                                @endif
                                @else
                                @if($report->approved_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    <i class="fas fa-check"></i> {{ $report->approved_by }}
                                </span>
                                @endif
                                @endcan

                                <form action="{{ route('report_thawings.destroy',$report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>

                                <a href="{{ route('report_thawings.export_pdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                            </td>

                        </tr>


                        {{-- DETAIL ROW --}}
                        <tr id="detail-{{ $report->uuid }}" class="d-none">

                            <td colspan="8">

                                <div class="table-responsive">

                                    <table class="table table-sm table-bordered mb-0">

                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>Waktu Thawing Awal</th>
                                                <th>Waktu Thawing Akhir</th>
                                                <th>Kondisi Awal Kemasan RM (utuh/sobek)</th>
                                                <th>Nama Bahan Baku</th>
                                                <th>Kode Produksi</th>
                                                <th>Jumlah</th>
                                                <th>Kondisi Ruang</th>
                                                <th>Waktu Pemeriksaan</th>
                                                <th>Suhu Ruang (&deg;C)</th>
                                                <th>Suhu Air Thawing(&deg;C)</th>
                                                <th>Suhu Produk (&deg;C)</th>
                                                <th>Kondisi Produk</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse($report->details as $detail)

                                            <tr>

                                                <td>
                                                    {{ $detail->start_thawing_time ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->end_thawing_time ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($detail->package_condition) ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->rawMaterial->material_name ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->production_code ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->qty ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($detail->room_condition) ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->inspection_time ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->room_temp ? $detail->room_temp.' °C' : '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->water_temp ? $detail->water_temp.' °C' : '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->product_temp ? $detail->product_temp.' °C' : '-' }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($detail->product_condition) ?? '-' }}
                                                </td>

                                            </tr>

                                            @empty

                                            <tr>
                                                <td colspan="12" class="text-center text-muted">
                                                    Tidak ada detail
                                                </td>
                                            </tr>

                                            @endforelse

                                        </tbody>

                                    </table>

                                    @can('create report')
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('report_thawings.details.create', $report->uuid) }}"
                                            class="btn btn-sm btn-secondary mt-2">
                                            + Tambah Detail
                                        </a>
                                    </div>
                                    @endcan

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Tidak ada data laporan
                            </td>
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
setTimeout(function() {
    $('#success-alert').fadeOut('slow')
}, 3000)

$('.toggle-detail').click(function() {
    const target = $($(this).data('target'))
    $('tr[id^="detail-"]').not(target).addClass('d-none')
    target.toggleClass('d-none')
})
</script>

@endsection