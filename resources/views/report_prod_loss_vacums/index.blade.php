@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Laporan Verifikasi Loss Vacuum</h4>
            <a href="{{ route('report_prod_loss_vacums.create') }}" class="btn btn-primary btn-sm">+ Tambah Laporan</a>
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
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Jumlah Produk</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->details->count() }}</td>
                        <td>{{ $report->created_by ?? '-' }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Delete --}}
                            <form action="{{ route('report_prod_loss_vacums.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_prod_loss_vacums.known', $report->id) }}" method="POST"
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
                            <form action="{{ route('report_prod_loss_vacums.approve', $report->id) }}" method="POST"
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
                            <a href="{{ route('report_prod_loss_vacums.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-dark" target="_blank" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="5">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Jenis Produk</th>
                                            @foreach ($report->details as $detail)
                                            <td colspan="2">{{ $detail->product->product_name ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <th>Kode Produksi</th>
                                            @foreach ($report->details as $detail)
                                            <td colspan="2">{{ $detail->production_code }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <th>Mesin Vacum (Manual/Colimatic/CFS)</th>
                                            @foreach ($report->details as $detail)
                                            <td colspan="2">{{ $detail->vacum_machine }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <th>Jumlah Sampel (pack)</th>
                                            @foreach ($report->details as $detail)
                                            <td colspan="2">{{ $detail->sample_amount }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <th rowspan="2">Hasil Pemeriksaan</th>
                                            @foreach ($report->details as $detail)

                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach ($report->details as $detail)
                                            <th>Jumlah Pack</th>
                                            <th>%</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $categories = [
                                        'Produk bagus',
                                        'Seal tidak sempurna',
                                        'Melipat',
                                        'Casing terjepit',
                                        'Top bergeser',
                                        'Seal terlalu panas',
                                        'Seal kurang panas',
                                        'Sobek',
                                        'Isi per pack tidak sesuai',
                                        'Penataan produk tidak rapi',
                                        'Produk tidak utuh',
                                        'Lain-lain',
                                        ];
                                        @endphp

                                        @foreach ($categories as $cat)
                                        <tr>
                                            <td class="text-start">- {{ $cat }}</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $def = $detail->defects->firstWhere('category', $cat);
                                            @endphp
                                            <td>{{ $def->pack_amount ?? '-' }}</td>
                                            <td>{{ $def->percentage ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <a href="{{ route('report_prod_loss_vacums.add-detail', $report->uuid) }}"
                                    class="btn btn-sm btn-secondary">
                                    Tambah Detail Produk
                                </a>
                            </div>

                        </td>
                    </tr>
                    @endforeach

                    @if ($reports->isEmpty())
                    <tr>
                        <td colspan="5" class="text-muted">Belum ada laporan.</td>
                    </tr>
                    @endif
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