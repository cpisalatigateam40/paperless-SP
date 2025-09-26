@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan Verifikasi Pemasakan Baso</h5>
            <a href="{{ route('report_baso_cookings.create') }}" class="btn btn-sm btn-success">+ Tambah Laporan</a>
        </div>

        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered  align-middle text-center">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Produk</th>
                            <th>STD Suhu Pusat</th>
                            <th>STD Berat akhir/potong</th>
                            <th>Set suhu tangki perebusan 1</th>
                            <th>Set suhu tangki perebusan 2</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->product->product_name ?? '-' }}</td>
                            <td>{{ $report->std_core_temp ?? '-' }}</td>
                            <td>{{ $report->std_weight ?? '-' }}</td>
                            <td>{{ $report->set_boiling_1 ?? '-' }}</td>
                            <td>{{ $report->set_boiling_2 ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex align-items-center" style="gap: .2rem;">
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('report_baso_cookings.edit', $report->uuid) }}"
                                    class="btn btn-warning btn-sm" title="Update">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_baso_cookings.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_baso_cookings.approve', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_baso_cookings.destroy', $report->uuid) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus laporan?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"> <i class="fas fa-trash"></i></button>
                                </form>
                                <a href="{{ route('report_baso_cookings.export_pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                            </td>
                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="11">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-center align-middle">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th rowspan="2" class="align-middle">Kode Produksi</th>
                                                <th rowspan="2" class="align-middle">Pukul</th>
                                                <th rowspan="2">Emulsi (°C)</th>
                                                <th rowspan="2">Air Tangki I (°C)</th>
                                                <th rowspan="2">Air Tangki II (°C)</th>
                                                <th rowspan="2">Berat Awal (gr)</th>

                                                <th colspan="6">Suhu Baso (°C)</th>

                                                <th colspan="5" rowspan="2">Uji Sensori</th>
                                                <th rowspan="2">Berat Akhir (gr)</th>
                                                <th colspan="2" rowspan="2">Paraf</th>
                                            </tr>
                                            <tr>
                                                {{-- Suhu Baso --}}
                                                <th>1</th>
                                                <th>2</th>
                                                <th>3</th>
                                                <th>4</th>
                                                <th>5</th>
                                                <th>Rata-rata</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $awal = $detail->temperatures->where('time_type', 'awal')->first();
                                            $akhir = $detail->temperatures->where('time_type', 'akhir')->first();
                                            @endphp

                                            {{-- Baris 1 (awal) --}}
                                            <tr>
                                                <td>{{ $detail->production_code }}</td>
                                                <td>{{ $awal?->time_recorded ? \Carbon\Carbon::parse($awal->time_recorded)->format('H:i') : '-' }}
                                                </td>
                                                <td>{{ $detail->emulsion_temp }}</td>
                                                <td>{{ $detail->boiling_tank_temp_1 }}</td>
                                                <td>{{ $detail->boiling_tank_temp_2 }}</td>
                                                <td>{{ $detail->initial_weight }}</td>

                                                {{-- Suhu baso awal --}}
                                                <td>{{ $awal?->baso_temp_1 }}</td>
                                                <td>{{ $awal?->baso_temp_2 }}</td>
                                                <td>{{ $awal?->baso_temp_3 }}</td>
                                                <td>{{ $awal?->baso_temp_4 }}</td>
                                                <td>{{ $awal?->baso_temp_5 }}</td>
                                                <td>{{ $awal?->avg_baso_temp }}</td>

                                                {{-- Sensori --}}
                                                <td rowspan="2">{{ $detail->sensory_shape ? 'OK' : 'Tdk OK' }}</td>
                                                <td rowspan="2">{{ $detail->sensory_taste ? 'OK' : 'Tdk OK' }}</td>
                                                <td rowspan="2">{{ $detail->sensory_aroma ? 'OK' : 'Tdk OK' }}</td>
                                                <td rowspan="2">{{ $detail->sensory_texture ? 'OK' : 'Tdk OK' }}</td>
                                                <td rowspan="2">{{ $detail->sensory_color ? 'OK' : 'Tdk OK' }}</td>

                                                {{-- Berat akhir & paraf --}}
                                                <td rowspan="2">{{ $detail->final_weight }}</td>
                                                <td rowspan="2">
                                                    @if ($detail->qc_paraf)
                                                    <img src="{{ asset('storage/'.$detail->qc_paraf) }}" width="60">
                                                    @endif
                                                </td>
                                                <td rowspan="2">
                                                    @if ($detail->prod_paraf)
                                                    <img src="{{ asset('storage/'.$detail->prod_paraf) }}" width="60">
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Baris 2 (akhir) --}}
                                            <tr>
                                                <td></td>
                                                <td>{{ $akhir?->time_recorded ? \Carbon\Carbon::parse($akhir->time_recorded)->format('H:i') : '-' }}
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>

                                                {{-- Suhu baso akhir --}}
                                                <td>{{ $akhir?->baso_temp_1 }}</td>
                                                <td>{{ $akhir?->baso_temp_2 }}</td>
                                                <td>{{ $akhir?->baso_temp_3 }}</td>
                                                <td>{{ $akhir?->baso_temp_4 }}</td>
                                                <td>{{ $akhir?->baso_temp_5 }}</td>
                                                <td>{{ $akhir?->avg_baso_temp }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <a href="{{ route('report_baso_cookings.add_detail', $report->uuid) }}"
                                        class="btn btn-outline-secondary btn-sm" title="Tambah Detail">
                                        Tambah Detail
                                    </a>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">Belum ada data laporan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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