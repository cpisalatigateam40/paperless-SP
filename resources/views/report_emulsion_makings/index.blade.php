@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Report Pembuatan Emulsi</h4>
            <a href="{{ route('report_emulsion_makings.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ optional($report->area)->name }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_emulsion_makings.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_emulsion_makings.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_emulsion_makings.export-pdf', $report->uuid) }}"
                                class="btn btn-outline-secondary btn-sm" target="_blank">
                                🖨 Cetak PDF
                            </a>
                        </td>
                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="100%">
                            <div class="table-responsive p-2">
                                <table class="table table-sm table-bordered text-center">
                                    <thead>
                                        {{-- Baris 1: Jenis Emulsi --}}
                                        <tr>
                                            <th style="width: 200px;" colspan="2">JENIS EMULSI</th>
                                            @foreach($report->header->agings ?? [] as $idx => $aging)
                                            <td colspan="2">{{ $report->header->emulsion_type ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        {{-- Baris 2: Kode Produksi --}}
                                        <tr>
                                            <th colspan="2">KODE PRODUKSI</th>
                                            @foreach($report->header->agings ?? [] as $idx => $aging)
                                            <td colspan="2">{{ $report->header->production_code ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        {{-- Baris 3: header kolom detail --}}
                                        <tr>
                                            <th rowspan="2">BAHAN BAKU</th>
                                            <th rowspan="2">Berat (kg)</th>
                                        </tr>
                                        <tr>
                                            @foreach($report->header->agings ?? [] as $aging)
                                            <th>Suhu (°C)</th>
                                            <th>Sensory</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Detail bahan baku --}}
                                        @foreach($report->header->details ?? [] as $detail)
                                        <tr>
                                            <td>{{ $detail->rawMaterial->material_name ?? '-' }}</td>
                                            <td>{{ $detail->weight ?? '-' }}</td>
                                            @foreach($report->header->agings ?? [] as $idx => $aging)
                                            @if($detail->aging_index == $idx)
                                            <td>{{ $detail->temperature ?? '-' }}</td>
                                            <td>{{ $detail->sensory ?? '-' }}</td>
                                            @else
                                            <td>-</td>
                                            <td>-</td>
                                            @endif
                                            @endforeach
                                        </tr>
                                        @endforeach


                                        {{-- Start aging --}}
                                        <tr>
                                            <td colspan="2">Start aging</td>
                                            @foreach($report->header->agings ?? [] as $aging)
                                            <td colspan="2">{{ $aging->start_aging ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        {{-- Finish aging --}}
                                        <tr>
                                            <td colspan="2">Finish aging</td>
                                            @foreach($report->header->agings ?? [] as $aging)
                                            <td colspan="2">{{ $aging->finish_aging ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        {{-- Hasil emulsi (sensory) --}}
                                        <tr>
                                            <td colspan="2">Hasil emulsi (sensory)</td>
                                            @foreach($report->header->agings ?? [] as $aging)
                                            <td colspan="2">{{ $aging->emulsion_result ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="d-flex justify-content-end mt-2">
                                    <a href="{{ route('report_emulsion_makings.add-detail', $report->uuid) }}"
                                        class="btn btn-secondary btn-sm">
                                        Tambah Detail
                                    </a>
                                </div>
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