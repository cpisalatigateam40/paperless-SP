@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Laporan Verifikasi Pasteurisasi</h4>
            <a href="{{ route('report_pasteurs.create') }}" class="btn btn-primary btn-sm">Tambah Report</a>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .4rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @can('edit report')
                                <a href="{{ route('report_pasteurs.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                <form action="{{ route('report_pasteurs.destroy', $report->uuid) }}" method="POST"
                                    onsubmit="return confirm('Hapus report ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_pasteurs.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_pasteurs.approve', $report->id) }}" method="POST"
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

                                <a href="{{ route('report_pasteurs.export_pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        {{-- Detail Collapse --}}
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="6">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="text-center align-middle">
                                            <tr>
                                                <th style="width: 220px;">Keterangan</th>
                                                @foreach($report->details as $detail)
                                                <th>{{ $detail->product->product_name ?? '-' }} -
                                                    {{ $detail->product->nett_weight ?? '-' }} g</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Info Produk --}}
                                            <tr>
                                                <td>Nomor Program</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->program_number ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Kode Produk</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->product_code ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Untuk Kemasan (gr)</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->for_packaging_gr ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jumlah Troly/Pack</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->trolley_count ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Suhu Produk</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->product_temp ?? '-' }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Step 1-7 --}}
                                            @php
                                            $standardSteps = [
                                            1 => 'Water Injection',
                                            2 => 'Up Temperature',
                                            3 => 'Pasteurisasi',
                                            4 => 'Hot Water Recycling',
                                            5 => 'Cooling Water Injection',
                                            6 => 'Cooling Constant Temp.',
                                            7 => 'Raw Cooling Water',
                                            ];
                                            @endphp

                                            @foreach($standardSteps as $order => $name)
                                            <tr class="bg-light">
                                                <td><strong>{{ $order }}. {{ $name }}</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Mulai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step->standardStep?->start_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Selesai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step->standardStep?->end_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Temp. Air (°C)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step->standardStep?->water_temp ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Pressure (MPa)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step->standardStep?->pressure ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            @endforeach

                                            {{-- Step 8: Drainage --}}
                                            <tr class="bg-light">
                                                <td><strong>8. Drainage Pressure</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Mulai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $drainage = $detail->steps->firstWhere('step_order', 8); @endphp
                                                <td>{{ $drainage->drainageStep?->start_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Selesai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $drainage = $detail->steps->firstWhere('step_order', 8); @endphp
                                                <td>{{ $drainage->drainageStep?->end_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Step 9: Finish --}}
                                            <tr class="bg-light">
                                                <td><strong>9. Finish Produk</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Suhu Pusat Produk</td>
                                                @foreach($report->details as $detail)
                                                @php $finish = $detail->steps->firstWhere('step_order', 9); @endphp
                                                <td>{{ $finish->finishStep?->product_core_temp ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Sortasi</td>
                                                @foreach($report->details as $detail)
                                                @php $finish = $detail->steps->firstWhere('step_order', 9); @endphp
                                                <td>{{ $finish->finishStep?->sortation ?? '-' }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Paraf --}}
                                            <tr class="bg-light">
                                                <td><strong>Paraf</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>QC</td>
                                                @foreach($report->details as $detail)
                                                <td>
                                                    @if($detail->qc_paraf)
                                                    <img src="{{ asset('storage/' . $detail->qc_paraf) }}" alt="QC"
                                                        width="60">
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Produksi</td>
                                                @foreach($report->details as $detail)
                                                <td>
                                                    @if($detail->production_paraf)
                                                    <img src="{{ asset('storage/' . $detail->production_paraf) }}"
                                                        alt="Produksi" width="60">
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="d-flex justify-content-end mt-3">
                                        <a href="{{ route('report_pasteurs.add_detail', $report->uuid) }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            Tambah Detail
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data laporan</td>
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