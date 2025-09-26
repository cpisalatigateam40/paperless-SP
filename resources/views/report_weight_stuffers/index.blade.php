@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Berat Stuffer</h4>
            <a href="{{ route('report_weight_stuffers.create') }}" class="btn btn-primary btn-sm">+ Tambah Laporan</a>
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
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->created_at->format('H:i') }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Hapus --}}
                            <form action="{{ route('report_weight_stuffers.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_weight_stuffers.known', $report->id) }}" method="POST"
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
                            <form action="{{ route('report_weight_stuffers.approve', $report->id) }}" method="POST"
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
                            <a href="{{ route('report_weight_stuffers.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="6">
                            <div class="table-responsive">
                                @php
                                $details = $report->details;
                                @endphp
                                <table class="table table-bordered table-sm text-center align-middle mb-4">
                                    {{-- Heading baris nama produk --}}
                                    <tr>
                                        <th class="text-start">Nama Produk</th>
                                        @foreach ($details as $d)
                                        <th>{{ $d->product->product_name ?? '-' }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="text-start">Kode Produksi</th>
                                        @foreach ($details as $d)
                                        <td>{{ $d->production_code }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="text-start">Waktu Proses</th>
                                        @foreach ($details as $d)
                                        <td>{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="text-start">Mesin Stuffer</th>
                                        @foreach ($details as $d)
                                        <td>{{ $d->townsend ? 'Townsend' : 'Hitech' }}</td>
                                        @endforeach
                                    </tr>

                                    @php
                                    $labels = [
                                    'Kecepatan Stuffer (rpm)' => 'speed',
                                    'Ukuran Casing<br><small>(Aktual Diameter)</small>' => 'casing',
                                    'Standar Berat (gr)' => 'standard',
                                    'Berat Aktual (gr)' => 'actual_weight',
                                    'Rata-rata Berat Aktual (gr)' => 'avg',
                                    'Standar Panjang' => 'standard_long',
                                    'Panjang Aktual' => 'actual_long',
                                    'Rata-rata Panjang Aktual' => 'avg_long',
                                    'Catatan' => 'notes',
                                    ];
                                    @endphp

                                    @foreach ($labels as $label => $key)
                                    <tr>
                                        <td class="text-start">{!! $label !!}</td>
                                        @foreach ($details as $d)
                                        @php
                                        // pilih mesin sesuai data yang ada
                                        $stuffer = $d->townsend ?? $d->hitech;
                                        $case = $d->cases->first();
                                        $weight = $d->weights->first();

                                        @endphp

                                        @switch($key)
                                        @case('speed')
                                        <td>{{ $stuffer?->stuffer_speed ?? '-' }}</td>
                                        @break

                                        @case('casing')
                                        <td>{{ $case?->actual_case_2 ?? '-' }}</td>
                                        @break

                                        @case('standard')
                                        <td>{{ $d->weight_standard ?? '-' }}</td>
                                        @break

                                        @case('actual_weight')
                                        <td>
                                            @if($d->weights->count() > 0)
                                            {{ $d->weights->pluck('actual_weight')->filter()->implode(' / ') }}
                                            @else
                                            -
                                            @endif
                                        </td>
                                        @break

                                        @case('avg')
                                        <td>{{ $stuffer?->avg_weight ?? '-' }}</td>
                                        @break

                                        @case('standard_long')
                                        <td>{{ $d->long_standard ?? '-' }}</td>
                                        @break

                                        @case('actual_long')
                                        <td>
                                            @if($d->weights->count() > 0)
                                            {{ $d->weights->pluck('actual_long')->filter()->implode(' / ') }}
                                            @else
                                            -
                                            @endif
                                        </td>
                                        @break

                                        @case('avg_long')
                                        <td>{{ $stuffer?->avg_long ?? '-' }}</td>
                                        @break

                                        @case('notes')
                                        <td>{{ $stuffer?->notes ?? '-' }}</td>
                                        @break
                                        @endswitch
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </table>



                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('report_weight_stuffers.add-detail', $report->uuid) }}"
                                    class="btn btn-secondary btn-sm">
                                    Tambah Detail
                                </a>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="6">Belum ada data laporan.</td>
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