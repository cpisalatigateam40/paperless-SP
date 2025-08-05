@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Laporan Proses Produksi</h4>
            <a href="{{ route('report_process_productions.create') }}" class="btn btn-primary btn-sm">+ Tambah
                Laporan</a>
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
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Seksi</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->section->section_name ?? '-' }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->uuid }}" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            <a href="{{ route('report_process_productions.edit', $report->uuid) }}"
                                class="btn btn-warning btn-sm" title="Update Laporan">
                                <i class="fas fa-pen"></i>
                            </a>

                            {{-- Hapus --}}
                            <form action="{{ route('report_process_productions.destroy', $report->uuid) }}"
                                method="POST" onsubmit="return confirm('Hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_process_productions.known', $report->id) }}" method="POST"
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
                            <form action="{{ route('report_process_productions.approve', $report->id) }}" method="POST"
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
                            <a href="{{ route('report_process_productions.export', $report->uuid) }}"
                                class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>

                    {{-- DETAIL COLLAPSIBLE --}}
                    <tr class="collapse" id="detail-{{ $report->uuid }}">
                        <td colspan="100%">
                            <div class="mt-3">
                                @foreach ($report->detail as $detail)
                                <table class="table table-bordered table-sm mb-5">
                                    {{-- HEADER PRODUK --}}
                                    <tr>
                                        <th colspan="2">NAMA PRODUK</th>
                                        <td colspan="3">{{ $detail->product->product_name ?? '-' }}
                                            {{ $detail->product->nett_weight ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">KODE PRODUKSI</th>
                                        <td colspan="3">{{ $detail->production_code ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">NOMOR FORMULA</th>
                                        <td colspan="3">{{ $detail->formula->formula_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="5">WAKTU MIXING: {{ $detail->mixing_time ?? '-' }}</th>
                                    </tr>

                                    {{-- A. BAHAN BAKU --}}
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="5">A. BAHAN BAKU</td>
                                    </tr>
                                    <tr>
                                        <th>No</th>
                                        <th>Bahan</th>
                                        <th>Berat (kg)</th>
                                        <th>Sensorik</th>
                                        <th>Suhu (℃)</th>
                                    </tr>
                                    @php $i = 1; @endphp
                                    @foreach ($detail->items->filter(fn($item) => $item->formulation?->raw_material_uuid
                                    !== null) as $item)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $item->formulation->rawMaterial->material_name ?? '-' }}</td>
                                        <td>{{ $item->actual_weight }}</td>
                                        <td>{{ $item->sensory }}</td>
                                        <td>{{ $item->temperature }}</td>
                                    </tr>
                                    @endforeach

                                    {{-- B. PREMIX --}}
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="5">B. PREMIX / BAHAN TAMBAHAN</td>
                                    </tr>
                                    <tr>
                                        <th>No</th>
                                        <th>Bahan</th>
                                        <th>Berat (kg)</th>
                                        <th>Sensorik</th>
                                        <th>Suhu (℃)</th>
                                    </tr>
                                    @php $j = 1; @endphp
                                    @foreach ($detail->items->filter(fn($item) => $item->formulation?->premix_uuid !==
                                    null) as $item)
                                    <tr>
                                        <td>{{ $j++ }}</td>
                                        <td>{{ $item->formulation->premix->name ?? '-' }}</td>
                                        <td>{{ $item->actual_weight }}</td>
                                        <td>{{ $item->sensory }}</td>
                                        <td>{{ $item->temperature }}</td>
                                    </tr>
                                    @endforeach

                                    {{-- REWORK & TOTAL --}}
                                    <tr>
                                        <th colspan="2">REWORK (kg/%)</th>

                                        <td colspan="3">{{ $detail->rework_kg ?? '-' }} /
                                            {{ $detail->rework_percent ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">TOTAL BAHAN (kg)</th>
                                        <td colspan="3">{{ $detail->total_material ?? '-' }}</td>
                                    </tr>

                                    {{-- EMULSIFYING --}}
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="5">C. EMULSIFYING</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Standar suhu adonan (℃)</th>
                                        <td colspan="3">{{ $detail->emulsifying->standard_mixture_temp ?? '14 ± 2' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Aktual suhu adonan (℃)</th>
                                        <td colspan="3">
                                            {{ $detail->emulsifying->actual_mixture_temp_1 ?? '-' }} /
                                            {{ $detail->emulsifying->actual_mixture_temp_2 ?? '-' }} /
                                            {{ $detail->emulsifying->actual_mixture_temp_3 ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Rata-rata suhu adonan (℃)</th>
                                        <td colspan="3">{{ $detail->emulsifying->average_mixture_temp ?? '-' }}</td>
                                    </tr>

                                    {{-- SENSORIK --}}
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="5">D. SENSORIK</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Homogenitas</th>
                                        <td colspan="3">{{ $detail->sensoric->homogeneous ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Kekentalan</th>
                                        <td colspan="3">{{ $detail->sensoric->stiffness ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Aroma</th>
                                        <td colspan="3">{{ $detail->sensoric->aroma ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Benda Asing</th>
                                        <td colspan="3">{{ $detail->sensoric->foreign_object ?? '-' }}</td>
                                    </tr>

                                    {{-- TUMBLING --}}
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="5">E. TUMBLING</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Proses Tumbling</th>
                                        <td colspan="3">{{ $detail->tumbling->tumbling_process ?? '-' }}</td>
                                    </tr>

                                    {{-- AGING --}}
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="5">F. AGING</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Proses Aging</th>
                                        <td colspan="3">{{ $detail->aging->aging_process ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Hasil Stuffing</th>
                                        <td colspan="3">{{ $detail->aging->stuffing_result ?? '-' }}</td>
                                    </tr>
                                </table>
                                <hr> <br><br>
                                @endforeach

                                <!-- <div class="d-flex justify-content-end mt-2">
                                    <a href="{{ route('report_process_productions.add_detail', $report->uuid) }}"
                                        class="btn btn-secondary btn-sm">
                                        + Tambah Detail
                                    </a>
                                </div> -->
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