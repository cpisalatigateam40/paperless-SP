@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Retain Sample</h4>
            <a href="{{ route('report_retain_samples.create') }}" class="btn btn-primary btn-sm">+ Tambah Laporan</a>
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
            <table class="table table-bordered text-center">
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
                    @forelse ($reports as $report)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
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
                            <form action="{{ route('report_retain_samples.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_retain_samples.known', $report->id) }}" method="POST"
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
                            <form action="{{ route('report_retain_samples.approve', $report->id) }}" method="POST"
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
                            <a href="{{ route('report_retain_samples.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="7">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ABF/IQF</th>
                                            <th>Jam Masuk</th>
                                            <th colspan="2">Suhu (°C)</th>
                                            <th colspan="2">Speed</th>
                                            <th colspan="2">Sampel QC</th>
                                            <th colspan="2">Nama & Tanda Tangan</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th>Room</th>
                                            <th>Suction</th>
                                            <th>Display</th>
                                            <th>Aktual</th>
                                            <th>Nama Produk</th>
                                            <th>Kode Produksi</th>
                                            <th>In</th>
                                            <th>Out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report->details as $key => $detail)
                                        <tr>
                                            {{-- Jika baris pertama, tampilkan semua kolom detail --}}
                                            @if ($key == 0 || (
                                            $key > 0 && (
                                            $report->details[$key]->line_type != $report->details[$key-1]->line_type ||
                                            $report->details[$key]->time_in != $report->details[$key-1]->time_in
                                            )
                                            ))
                                            <td>{{ $detail->line_type ?? '-' }}</td>
                                            <td>{{ $detail->time_in ?? '-' }}</td>
                                            <td>{{ $detail->room_temp ?? '-' }}</td>
                                            <td>{{ $detail->suction_temp ?? '-' }}</td>
                                            <td>{{ $detail->display_speed ?? '-' }}</td>
                                            <td>{{ $detail->actual_speed ?? '-' }}</td>
                                            @else
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            @endif

                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->production_code ?? '-' }}</td>
                                            <td>{{ $detail->signature_in ?? '-' }}</td>
                                            <td>{{ $detail->signature_out ?? '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="10">Tidak ada detail</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <a href="{{ route('report_retain_samples.add-detail', $report->uuid) }}"
                                    class="btn btn-sm btn-secondary">
                                    + Tambah Detail
                                </a>
                            </div>
                        </td>
                    </tr>


                    @empty
                    <tr>
                        <td colspan="7">Belum ada laporan.</td>
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