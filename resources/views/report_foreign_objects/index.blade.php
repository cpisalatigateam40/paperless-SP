@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Kontaminasi Benda Asing</h4>
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

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Waktu</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Section</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->date->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->section->section_name ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex align-items-center" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('edit report')
                                <a href="{{ route('report-foreign-objects.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus --}}
                                <form action="{{ route('report-foreign-objects.destroy', $report->uuid) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus seluruh laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report-foreign-objects.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report-foreign-objects.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report-foreign-objects.export-pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="7">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Jam</th>
                                                <th class="align-middle">Produk</th>
                                                <th class="align-middle">Gramase</th>
                                                <th class="align-middle">Kode Produksi</th>
                                                <th class="align-middle">Jenis Kontaminan</th>
                                                <th class="align-middle">Bukti</th>
                                                <th class="align-middle">Tahapan Analisis</th>
                                                <th class="align-middle">Asal Kontaminan</th>
                                                <th class="align-middle">Keterangan</th>
                                                <th class="align-middle">Paraf QC</th>
                                                <th class="align-middle">Paraf Produksi</th>
                                                <th class="align-middle">Paraf Engineering</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($report->details as $detail)
                                            <tr>
                                                <td>{{ $detail->time }}</td>
                                                <td>{{ $detail->product->product_name ?? '-' }}</td>
                                                <td>{{ $detail->product->nett_weight ?? '-' }} g</td>
                                                <td>{{ $detail->production_code }}</td>
                                                <td>{{ $detail->contaminant_type }}</td>
                                                <td>
                                                    @if($detail->evidence)
                                                    <a href="{{ asset('storage/' . $detail->evidence) }}"
                                                        target="_blank">
                                                        <img src="{{ asset('storage/' . $detail->evidence) }}"
                                                            alt="Bukti" width="60">
                                                    </a>
                                                    @endif
                                                </td>
                                                <td>{{ $detail->analysis_stage }}</td>
                                                <td>{{ $detail->contaminant_origin }}</td>
                                                <td>{{ $detail->notes }}</td>

                                                {{-- Tambahan kolom paraf --}}
                                                <td>
                                                    @if($detail->qc_paraf)
                                                    <img src="{{ asset('storage/' . $detail->qc_paraf) }}" alt="QC"
                                                        width="60">
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($detail->production_paraf)
                                                    <img src="{{ asset('storage/' . $detail->production_paraf) }}"
                                                        alt="Produksi" width="60">
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($detail->engineering_paraf)
                                                    <img src="{{ asset('storage/' . $detail->engineering_paraf) }}"
                                                        alt="Engineering" width="60">
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="11" class="text-center">Tidak ada detail</td>
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