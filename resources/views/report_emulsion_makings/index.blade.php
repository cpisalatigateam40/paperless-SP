@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Pembuatan Emulsi</h4>
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

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
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
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ optional($report->area)->name }}</td>
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

                                @can('edit report')
                                <a href="{{ route('report_emulsion_makings.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus --}}
                                <form action="{{ route('report_emulsion_makings.destroy', $report->uuid) }}"
                                    method="POST" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_emulsion_makings.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_emulsion_makings.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_emulsion_makings.export-pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
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
                                                <th>Kesesuaian Formula</th>
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
                                                <td>{{ $detail->conformity ?? '-' }}</td>
                                                @else
                                                <td>-</td>
                                                <td>-</td>
                                                @endif
                                                @endforeach
                                            </tr>
                                            @endforeach


                                            {{-- Start aging --}}
                                            <tr>
                                                <td colspan="2">Waktu Awal Proses</td>
                                                @foreach($report->header->agings ?? [] as $aging)
                                                <td colspan="2">{{ $aging->start_aging ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            {{-- Finish aging --}}
                                            <tr>
                                                <td colspan="2">Waktu Akhir Proses</td>
                                                @foreach($report->header->agings ?? [] as $aging)
                                                <td colspan="2">{{ $aging->finish_aging ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td colspan="2">Sensori Warna</td>
                                                @foreach($report->header->agings ?? [] as $aging)
                                                <td colspan="2">{{ $aging->sensory_color ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td colspan="2">Sensori Texture</td>
                                                @foreach($report->header->agings ?? [] as $aging)
                                                <td colspan="2">{{ $aging->sensory_texture ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td colspan="2">Suhu Emulsi After Proses</td>
                                                @foreach($report->header->agings ?? [] as $aging)
                                                <td colspan="2">{{ $aging->temp_after ?? '-' }}</td>
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