@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Pembekuan IQF & Pengemasan Karton Box</h4>
            <a href="{{ route('report_freez_packagings.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
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

                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Delete --}}
                                <form action="{{ route('report_freez_packagings.destroy', $report->uuid) }}"
                                    method="POST" onsubmit="return confirm('Hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_freez_packagings.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_freez_packagings.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_freez_packagings.export_pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="100%">
                                <div class="table-responsive p-2 border rounded">
                                    <table class="table table-bordered text-center table-sm align-middle"
                                        style="font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Waktu Pemeriksaan</th>
                                                <th rowspan="2">Nama Produk</th>
                                                <th rowspan="2">Gramase</th>
                                                <th rowspan="2">Kode Produksi</th>
                                                <th rowspan="2">Best Before</th>
                                                <th rowspan="2">Tindakan Koreksi</th>
                                                <th rowspan="2">Verifikasi Setelah Tindakan Koreksi</th>

                                                <th colspan="6">PEMBEKUAN</th>
                                                <th colspan="12">KARTONING</th>

                                            </tr>
                                            <tr>
                                                <th>Suhu Akhir</th>
                                                <th>Standar Suhu</th>
                                                <th>Suhu IQF Room</th>
                                                <th>Suhu IQF Suction</th>
                                                <th>Lama Pembekuan Display</th>
                                                <th>Lama Pembekuan Aktual</th>

                                                <th>Kondisi Karton</th>
                                                <th>Isi Bag</th>
                                                <th>Isi Binded</th>
                                                <th>Isi RTG</th>
                                                <th>Berat Standar (kg)</th>
                                                <th>Berat Karton 1</th>
                                                <th>Berat Karton 2</th>
                                                <th>Berat Karton 3</th>
                                                <th>Berat Karton 4</th>
                                                <th>Berat Karton 5</th>
                                                <th>Rata-Rata Berat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($report->details as $detail)
                                            <tr>
                                                <td class="align-middle">
                                                    {{ $detail->start_time ? \Carbon\Carbon::parse($detail->start_time)->format('H:i') : '-' }}
                                                    -
                                                    {{ $detail->end_time ? \Carbon\Carbon::parse($detail->end_time)->format('H:i') : '-' }}
                                                </td>
                                                <td class="align-middle">{{ $detail->product->product_name ?? '-' }}
                                                </td>
                                                <td class="align-middle">{{ $detail->product->nett_weight ?? '-' }} g
                                                </td>
                                                <td class="align-middle">{{ $detail->production_code ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->best_before ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->corrective_action ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->verif_after ?? '-' }}</td>

                                                {{-- Freezing --}}
                                                <td class="align-middle">
                                                    {{ $detail->freezing->end_product_temp ?? '-' }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $detail->freezing->standard_temp ?? '-' }}
                                                </td>
                                                <td class="align-middle">{{ $detail->freezing->iqf_room_temp ?? '-' }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $detail->freezing->iqf_suction_temp ?? '-' }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $detail->freezing->freezing_time_display ?? '-' }}</td>
                                                <td class="align-middle">
                                                    {{ $detail->freezing->freezing_time_actual ?? '-' }}</td>

                                                {{-- Kartoning --}}
                                                <td class="align-middle">
                                                    {{ $detail->kartoning->carton_condition ?? '-' }}
                                                </td>
                                                <td class="align-middle">{{ $detail->kartoning->content_bag ?? '-' }}
                                                </td>
                                                <td class="align-middle">{{ $detail->kartoning->content_binded ?? '-' }}
                                                </td>
                                                <td class="align-middle">{{ $detail->kartoning->content_rtg ?? '-' }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $detail->kartoning->carton_weight_standard ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_1 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_2 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_3 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_4 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_5 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->avg_weight ?? '-' }}
                                                </td>


                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('report_freez_packagings.add-detail', $report->uuid) }}"
                                            class="btn btn-sm btn-secondary mt-2">
                                            Tambah Detail
                                        </a>
                                    </div>

                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
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