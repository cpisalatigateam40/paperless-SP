@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="m-0">Laporan Verifikasi Pemasakan Dengan Steamer</h4>
            
            <div class="d-flex gap-2" style="gap: .4rem;">

                {{-- 🔍 SEARCH --}}
                <form method="GET"
                    action="{{ route('report_rtg_steamers.index') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari laporan..."
                        value="{{ request('search') }}"
                    >

                    {{-- 🔍 BUTTON CARI --}}
                    <button type="submit" class="btn btn-outline-primary">
                        Cari
                    </button>

                    {{-- 🔄 RESET --}}
                    @if(request('search') || request('section'))
                        <a href="{{ route('report_rtg_steamers.index') }}"
                        class="btn btn-danger"
                        title="Reset Filter">
                            Reset
                        </a>
                    @endif

                </form>

                @can('create report')
                <a href="{{ route('report_rtg_steamers.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="text-center fw-semibold">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Ketidaksesuaian</th>
                            <th>Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $i => $report)
                        <tr>
                            <td class="text-center">{{ $i + $reports->firstItem() }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>
                                @if ($report->ketidaksesuaian > 0)
                                Ada
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $report->product->product_name ?? '-' }} -
                                {{ $report->product->nett_weight ?? '-' }} g</td>
                            <td>
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <!-- @can('edit report')
                                <a href="{{ route('report_rtg_steamers.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->
                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_rtg_steamers.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @can('delete report')
                                <form action="{{ route('report_rtg_steamers.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_rtg_steamers.known', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
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
                                <form action="{{ route('report_rtg_steamers.approve', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
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

                                <a href="{{ route('report_rtg_steamers.export_pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        {{-- Detail Collapse --}}
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="8">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle small text-center">
                                        <tbody>
                                            <tr>
                                                <th>Steamer</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->steamer }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Kode Prod.</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->production_code }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Jumlah Trolly</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->trolley_count }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>T. Ruang (°C)</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->room_temp }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>T. Produk (°C)</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->product_temp }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Waktu (Menit)</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->time_minute }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Jam Mulai</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->start_time }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Jam Selesai</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->end_time }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Kematangan</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->sensory_ripeness }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Rasa</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->sensory_taste }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Aroma</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->sensory_aroma }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Tekstur</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->sensory_texture }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Warna</th>
                                                @foreach ($report->details as $detail)
                                                <td>{{ $detail->sensory_color }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Paraf QC</th>
                                                @foreach ($report->details as $detail)
                                                <td>
                                                    @if($detail->qc_paraf)
                                                    <img src="{{ asset('storage/' . $detail->qc_paraf) }}" alt="QC"
                                                        width="60">
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            <!-- <tr>
                                                <th>Paraf Produksi</th>
                                                @foreach ($report->details as $detail)
                                                <td>
                                                    @if($detail->production_paraf)
                                                    <img src="{{ asset('storage/' . $detail->production_paraf) }}"
                                                        alt="Produksi" width="60">
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr> -->
                                        </tbody>
                                    </table>
                                </div>
                                @can('create report')
                                <div class="d-flex justify-content-end mt-2">
                                    <a href="{{ route('report_rtg_steamers.add_detail', $report->uuid) }}"
                                        class="btn btn-outline-secondary btn-sm" title="Tambah Detail">
                                        Tambah Detail
                                    </a>
                                </div>
                                @endcan
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada laporan</td>
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