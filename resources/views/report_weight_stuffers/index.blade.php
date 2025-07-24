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
                        <th>Dibuat Oleh</th>
                        <th>Jumlah Produk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td>{{ $report->details->count() }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_weight_stuffers.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_weight_stuffers.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_weight_stuffers.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
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
                                        <th colspan="2">{{ $d->product->product_name ?? '-' }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="text-start">Kode Produksi</th>
                                        @foreach ($details as $d)
                                        <td colspan="2">{{ $d->production_code }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="text-start">Waktu Proses</th>
                                        @foreach ($details as $d)
                                        <td colspan="2">{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="text-start">Mesin Stuffer</th>
                                        @foreach ($details as $d)
                                        <th>Townsend</th>
                                        <th>Hitech</th>
                                        @endforeach
                                    </tr>

                                    @php
                                    $labels = [
                                    'Kecepatan Stuffer (rpm)' => 'speed',
                                    'Ukuran Casing<br><small>(Aktual Panjang, Diameter)</small>' => 'casing',
                                    'Jumlah Trolley' => 'trolley',
                                    'Standar Berat (gr)' => 'standard',
                                    'Berat Aktual (gr)' => 'actual_weight',
                                    'Rata-rata Berat Aktual (gr)' => 'avg',
                                    'Catatan' => 'notes',
                                    ];
                                    @endphp

                                    @foreach ($labels as $label => $key)
                                    <tr>
                                        <td class="text-start">{!! $label !!}</td>
                                        @foreach ($details as $d)
                                        @php
                                        $t = $d->townsend;
                                        $h = $d->hitech;
                                        $cT = $d->cases->get(0);
                                        $cH = $d->cases->get(1);
                                        $wT = $d->weights->get(0);
                                        $wH = $d->weights->get(1);
                                        @endphp

                                        @switch($key)
                                        @case('speed')
                                        <td>{{ $t->stuffer_speed ?? '-' }}</td>
                                        <td>{{ $h->stuffer_speed ?? '-' }}</td>
                                        @break

                                        @case('casing')
                                        <td>{{ $cT?->actual_case_1 ?? '-' }} / {{ $cT?->actual_case_2 ?? '-' }}</td>
                                        <td>{{ $cH?->actual_case_1 ?? '-' }} / {{ $cH?->actual_case_2 ?? '-' }}</td>
                                        @break

                                        @case('trolley')
                                        <td>{{ $t->trolley_total ?? '-' }}</td>
                                        <td>{{ $h->trolley_total ?? '-' }}</td>
                                        @break

                                        @case('standard')
                                        <td colspan="2">{{ $d->weight_standard ?? '-' }}</td>
                                        @break

                                        @case('actual_weight')
                                        <td>
                                            {{ $wT?->actual_weight_1 ?? '-' }} /
                                            {{ $wT?->actual_weight_2 ?? '-' }} /
                                            {{ $wT?->actual_weight_3 ?? '-' }}
                                        </td>
                                        <td>
                                            {{ $wH?->actual_weight_1 ?? '-' }} /
                                            {{ $wH?->actual_weight_2 ?? '-' }} /
                                            {{ $wH?->actual_weight_3 ?? '-' }}
                                        </td>
                                        @break

                                        @case('avg')
                                        <td>{{ $t->avg_weight ?? '-' }}</td>
                                        <td>{{ $h->avg_weight ?? '-' }}</td>
                                        @break

                                        @case('notes')
                                        <td>{{ $t->notes ?? '-' }}</td>
                                        <td>{{ $h->notes ?? '-' }}</td>
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