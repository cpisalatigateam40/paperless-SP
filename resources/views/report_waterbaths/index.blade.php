@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Laporan Verifikasi Pasteurisasi Waterbath</h4>
            <a href="{{ route('report_waterbaths.create') }}" class="btn btn-primary">Tambah Laporan</a>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
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
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .2rem;">
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <!-- @can('edit report')
                                <a href="{{ route('report_waterbaths.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->
                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_waterbaths.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <form action="{{ route('report_waterbaths.destroy', $report->uuid) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_waterbaths.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_waterbaths.approve', $report->id) }}" method="POST"
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

                                <a href="{{ route('report_waterbaths.export_pdf', $report->uuid) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="7">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Produk</th>
                                                <th>Gramase</th>
                                                <th>Batch</th>
                                                <th>Jumlah</th>
                                                <th>Satuan</th>
                                                <th>Pasteurisasi</th>
                                                <th>Cooling Shock</th>
                                                <th>Dripping</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $max = max(
                                            $report->details->count(),
                                            $report->pasteurisasi->count(),
                                            $report->coolingShocks->count(),
                                            $report->drippings->count()
                                            );
                                            @endphp

                                            @for($i = 0; $i < $max; $i++) <tr>
                                                {{-- Detail Produk --}}
                                                <td>{{ $report->details[$i]->product->product_name ?? '-' }}</td>
                                                <td>{{ $report->details[$i]->product->nett_weight ?? '-' }} g</td>
                                                <td>{{ $report->details[$i]->batch_code ?? '-' }}</td>
                                                <td>{{ $report->details[$i]->amount ?? '-' }}</td>
                                                <td>{{ $report->details[$i]->unit ?? '-' }}</td>

                                                {{-- Pasteurisasi --}}
                                                <td>
                                                    @if(isset($report->pasteurisasi[$i]))
                                                    Suhu Awal Produk:
                                                    {{ $report->pasteurisasi[$i]->initial_product_temp }}
                                                    <br>
                                                    Suhu Awal Air: {{ $report->pasteurisasi[$i]->initial_water_temp }}
                                                    <br>
                                                    Start Pasteurisasi:
                                                    {{ $report->pasteurisasi[$i]->start_time_pasteur }}
                                                    <br>
                                                    Stop Pasteurisasi:
                                                    {{ $report->pasteurisasi[$i]->stop_time_pasteur }}
                                                    <br>
                                                    Suhu air setelah produk dimasukkan panel:
                                                    {{ $report->pasteurisasi[$i]->water_temp_after_input_panel }} <br>
                                                    Suhu air setelah produk dimasukkan aktual:
                                                    {{ $report->pasteurisasi[$i]->water_temp_after_input_actual }} <br>
                                                    Suhu air setting:
                                                    {{ $report->pasteurisasi[$i]->water_temp_setting }} <br>
                                                    Suhu air aktual:
                                                    {{ $report->pasteurisasi[$i]->water_temp_actual }} <br>
                                                    Suhu akhir air:
                                                    {{ $report->pasteurisasi[$i]->water_temp_final }} <br>
                                                    Suhu akhir produk:
                                                    {{ $report->pasteurisasi[$i]->product_temp_final }} <br>
                                                    @endif
                                                </td>

                                                {{-- Cooling Shock --}}
                                                <td>
                                                    @if(isset($report->coolingShocks[$i]))
                                                    Suhu Awal Air: {{ $report->coolingShocks[$i]->initial_water_temp }}
                                                    <br>
                                                    Start Pasteurisasi:
                                                    {{ $report->coolingShocks[$i]->start_time_pasteur }}
                                                    <br>
                                                    Stop Pasteurisasi:
                                                    {{ $report->coolingShocks[$i]->stop_time_pasteur }}
                                                    <br>
                                                    Suhu air setting:
                                                    {{ $report->coolingShocks[$i]->water_temp_setting }}
                                                    <br>
                                                    Suhu air aktual: {{ $report->coolingShocks[$i]->water_temp_actual }}
                                                    <br>
                                                    Suhu akhir air: {{ $report->coolingShocks[$i]->water_temp_final }}
                                                    <br>
                                                    Suhu akhir produk:
                                                    {{ $report->coolingShocks[$i]->product_temp_final }}
                                                    <br>

                                                    @endif
                                                </td>

                                                {{-- Dripping --}}
                                                <td>
                                                    @if(isset($report->drippings[$i]))
                                                    Start Pasteurisasi: {{ $report->drippings[$i]->start_time_pasteur }}
                                                    <br>
                                                    Stop Pasteurisasi: {{ $report->drippings[$i]->stop_time_pasteur }}
                                                    <br>
                                                    Suhu Zona Panas: {{ $report->drippings[$i]->hot_zone_temperature }}
                                                    <br>
                                                    Suhu Zona Dingin:
                                                    {{ $report->drippings[$i]->cold_zone_temperature }}
                                                    <br>
                                                    Suhu Akhir Produk: {{ $report->drippings[$i]->product_temp_final }}
                                                    @endif
                                                </td>

                                                <td>{{ $report->details[$i]->note ?? '-' }}</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-2">
                    <a href="{{ route('report_waterbaths.add_detail', $report->uuid) }}"
                        class="btn btn-sm btn-secondary">
                        + Tambah Detail
                    </a>
                </div>
            </div>
            </td>
            </tr>




            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada data laporan</td>
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