@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5> Laporan Verifikasi Timbangan & Thermometer</h5>
            <a href="{{ route('report-scales.create') }}" class="btn btn-primary btn-sm">+ Tambah Laporan</a>
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
                    <thead class="table-light">
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
                        @forelse ($reports as $report)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Update --}}
                                <a href="{{ route('report-scales.edit', $report->uuid) }}"
                                    class="btn btn-warning btn-sm" title="Update">
                                    <i class="fas fa-pen"></i>
                                </a>

                                @can('edit report')
                                <a href="{{ route('report-scales.edit-next', $report->uuid) }}"
                                    class="btn btn-sm btn-danger" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus --}}
                                <form action="{{ route('report-scales.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report-scales.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report-scales.approve', $report->id) }}" method="POST"
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

                                {{-- Export PDF --}}
                                <a href="{{ route('report-scales.export-pdf', $report->uuid) }}" target="_blank"
                                    class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>
                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="6">
                                {{-- TIMBANGAN --}}
                                <h6 class="fw-bold mt-3">1. Pemeriksaan Timbangan</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th rowspan="3">No</th>
                                            <th rowspan="3">Jenis dan Kode Timbangan</th>
                                            <th colspan="3">
                                                Pemeriksaan Pukul:
                                                {{ $report->details->isNotEmpty() && $report->details->pluck('time_1')->filter()->first()
                                                        ? \Carbon\Carbon::parse($report->details->pluck('time_1')->filter()->first())->format('H:i')
                                                        : '-' }}
                                            </th>
                                            <th colspan="3">
                                                Pemeriksaan Pukul:
                                                {{ $report->details->isNotEmpty() && $report->details->pluck('time_2')->filter()->last()
                                                        ? \Carbon\Carbon::parse($report->details->pluck('time_2')->filter()->last())->format('H:i')
                                                        : '-' }}
                                            </th>
                                            <th rowspan="3">Keterangan</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3">Standart Berat</th>
                                            <th colspan="3">Standart Berat</th>
                                        </tr>
                                        <tr>
                                            <th>1000 Gr</th>
                                            <th>5000 Gr</th>
                                            <th>10000 Gr</th>
                                            <th>1000 Gr</th>
                                            <th>5000 Gr</th>
                                            <th>10000 Gr</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($report->details as $i => $d)
                                        @php
                                        $m1 = $d->measurements->where('inspection_time_index',
                                        1)->keyBy('standard_weight');
                                        $m2 = $d->measurements->where('inspection_time_index',
                                        2)->keyBy('standard_weight');
                                        @endphp
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ $d->scale->type ?? '' }} - {{ $d->scale->code ?? '' }}</td>
                                            <td>{{ $m1->get(1000)->measured_value ?? '' }}</td>
                                            <td>{{ $m1->get(5000)->measured_value ?? '' }}</td>
                                            <td>{{ $m1->get(10000)->measured_value ?? '' }}</td>
                                            <td>{{ $m2->get(1000)->measured_value ?? '' }}</td>
                                            <td>{{ $m2->get(5000)->measured_value ?? '' }}</td>
                                            <td>{{ $m2->get(10000)->measured_value ?? '' }}</td>
                                            <td>{{ $d->notes }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                {{-- THERMOMETER --}}
                                <h6 class="fw-bold mt-4">2. Pemeriksaan Thermometer</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th rowspan="3">No</th>
                                            <th rowspan="3">Jenis dan Kode Timbangan</th>
                                            <th colspan="2">
                                                Pemeriksaan Pukul:
                                                {{ optional($report->thermometerDetails->pluck('time_1')->filter()->first())->format('H:i') ?? '-' }}
                                            </th>
                                            <th colspan="2">
                                                Pemeriksaan Pukul:
                                                {{ optional($report->thermometerDetails->pluck('time_2')->filter()->last())->format('H:i') ?? '-' }}
                                            </th>

                                            <th rowspan="3">Keterangan</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Standart Suhu</th>
                                            <th colspan="2">Standart Suhu</th>
                                        </tr>
                                        <tr>
                                            <th>0°C</th>
                                            <th>100°C</th>
                                            <th>0°C</th>
                                            <th>100°C</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($report->thermometerDetails as $i => $d)
                                        @php
                                        $m1 = $d->measurements->where('inspection_time_index',
                                        1)->keyBy('standard_temperature');
                                        $m2 = $d->measurements->where('inspection_time_index',
                                        2)->keyBy('standard_temperature');
                                        @endphp
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ $d->thermometer->type ?? '' }} - {{ $d->thermometer->code ?? '' }}
                                            </td>
                                            <td>{{ $m1->get(0)->measured_value ?? '' }}</td>
                                            <td>{{ $m1->get(100)->measured_value ?? '' }}</td>
                                            <td>{{ $m2->get(0)->measured_value ?? '' }}</td>
                                            <td>{{ $m2->get(100)->measured_value ?? '' }}</td>
                                            <td>{{ $d->note }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada laporan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

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

document.querySelectorAll('.toggle-detail').forEach(button => {
    button.addEventListener('click', function() {
        const target = document.querySelector(this.dataset.target);
        const isHidden = target.classList.contains('d-none');

        // Sembunyikan semua detail
        document.querySelectorAll('tr[id^="detail-"]').forEach(el => el.classList.add('d-none'));
        document.querySelectorAll('.toggle-detail').forEach(b => b.textContent = 'Lihat Detail');

        // Tampilkan yang diklik
        if (isHidden) {
            target.classList.remove('d-none');
            this.textContent = 'Sembunyikan Detail';
        }
    });
});
</script>
@endsection