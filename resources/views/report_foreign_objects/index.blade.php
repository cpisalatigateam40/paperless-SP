@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Pemeriksaan Kontaminasi</h4>
            <a href="{{ route('report-foreign-objects.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="align-middle">Tanggal</th>
                        <th class="align-middle">Shift</th>
                        <th class="align-middle">Area</th>
                        <th class="align-middle">Section</th>
                        <th class="align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date->format('d-m-Y') }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->section->section_name ?? '-' }}</td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report-foreign-objects.destroy', $report->uuid) }}" method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Yakin ingin menghapus seluruh laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Hapus Laporan
                                </button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report-foreign-objects.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report-foreign-objects.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>
                        </td>
                    </tr>

                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="5">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th class="align-middle">Jam</th>
                                            <th class="align-middle">Produk</th>
                                            <th class="align-middle">Kode Produksi</th>
                                            <th class="align-middle">Jenis Kontaminan</th>
                                            <th class="align-middle">Bukti</th>
                                            <th class="align-middle">Tahapan Analisis</th>
                                            <th class="align-middle">Asal Kontaminan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report->details as $detail)
                                        <tr>
                                            <td>{{ $detail->time }}</td>
                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->production_code }}</td>
                                            <td>{{ $detail->contaminant_type }}</td>
                                            <td>
                                                @if($detail->evidence)
                                                <a href="{{ asset('storage/' . $detail->evidence) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $detail->evidence) }}" alt="Bukti"
                                                        width="60">
                                                </a>
                                                @endif
                                            </td>
                                            <td>{{ $detail->analysis_stage }}</td>
                                            <td>{{ $detail->contaminant_origin }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada detail</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2 d-flex justify-content-end">
                                <a href="{{ route('report-foreign-objects.add-detail', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    Tambah Detail Temuan Kontaminasi
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
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