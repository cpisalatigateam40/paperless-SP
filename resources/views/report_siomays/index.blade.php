@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Laporan Pemeriksaan Pembuatan Kulit Siomay, Gioza & Mandu</h5>
            <a href="{{ route('report_siomays.create') }}" class="btn btn-sm btn-success">+ Tambah Laporan</a>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Produk</th>
                        <th>Waktu</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $i => $r)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $r->date }}</td>
                        <td>{{ $r->shift }}</td>
                        <td>{{ $r->product->product_name ?? '-' }}</td>
                        <td>{{ $r->start_time }} - {{ $r->end_time }}</td>
                        <td>{{ $r->created_by }}</td>
                        <td class="d-flex" style="gap: .3rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $r->id }}" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <form action="{{ route('report_siomays.destroy', $r->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            {{-- Known --}}
                            @can('known report')
                            @if(!$r->known_by)
                            <form action="{{ route('report_siomays.known', $r->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                <i class="fas fa-check"></i> {{ $r->known_by }}
                            </span>
                            @endif
                            @else
                            @if($r->known_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                <i class="fas fa-check"></i> {{ $r->known_by }}
                            </span>
                            @endif
                            @endcan

                            {{-- Approve --}}
                            @can('approve report')
                            @if(!$r->approved_by)
                            <form action="{{ route('report_siomays.approve', $r->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                    <i class="fas fa-thumbs-up"></i>
                                </button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                <i class="fas fa-check"></i> {{ $r->approved_by }}
                            </span>
                            @endif
                            @else
                            @if($r->approved_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                <i class="fas fa-check"></i> {{ $r->approved_by }}
                            </span>
                            @endif
                            @endcan

                            <a href="{{ route('report_siomays.export_pdf', $r->uuid) }}"
                                class="btn btn-outline-secondary btn-sm" title="Export PDF" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    {{-- Detail Collapse --}}
                    <tr class="collapse" id="detail-{{ $r->id }}">
                        <td colspan="7">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle text-center">
                                    {{-- Header Informasi --}}
                                    <tr>
                                        <th class="text-start">Nama Produk</th>
                                        <td colspan="16" class="text-start" style="text-align: start !important;">
                                            {{ $r->product->product_name }}</td>
                                    </tr>

                                    <tr>
                                        <th class="text-start">Kode Produksi</th>
                                        <td colspan="16" class="text-start" style="text-align: start !important;">
                                            {{ $r->production_code }}</td>
                                    </tr>

                                    <tr>
                                        <th class="text-start">Waktu (Start - Stop)</th>
                                        <td colspan="16" class="text-start" style="text-align: start !important;">
                                            {{ $r->start_time }} -
                                            {{ $r->end_time }}</td>
                                    </tr>

                                    {{-- Header Kolom Utama --}}
                                    <tr>
                                        <th rowspan="2">Pukul</th>
                                        <th rowspan="2">Tahapan Proses</th>
                                        <th colspan="3">Bahan Baku</th>
                                        <th colspan="6">Parameter Pemasakan</th>
                                        <th colspan="4">Produk Organoleptik</th>
                                        <th rowspan="2">Catatan</th>
                                    </tr>
                                    <tr>
                                        <th>Jenis Bahan</th>
                                        <th>Jumlah (Kg)</th>
                                        <th>Sensori</th>

                                        <th>Lama Proses (menit)</th>
                                        <th>Mixing Paddle On</th>
                                        <th>Mixing Paddle Off</th>
                                        <th>Pressure (Bar)</th>
                                        <th>Target Temp (°C)</th>
                                        <th>Actual Temp (°C)</th>

                                        <th>Warna</th>
                                        <th>Aroma</th>
                                        <th>Rasa</th>
                                        <th>Tekstur</th>
                                    </tr>

                                    {{-- Isi Data --}}
                                    @foreach($r->details as $d)
                                    {{-- baris utama per proses --}}
                                    <tr>
                                        <td>{{ $d->time }}</td>
                                        <td>{{ $d->process_step }}</td>

                                        {{-- bahan baku ditaruh di cell bersarang --}}
                                        <td colspan="3" class="p-0">
                                            <table class="table table-sm mb-0 table-borderless">
                                                @foreach($d->rawMaterials as $rm)
                                                <tr>
                                                    <td style="text-align: start !important;">
                                                        {{ $rm->rawMaterial->material_name ?? '-' }}</td>
                                                    <td style="text-align: start !important;">{{ $rm->amount }}</td>
                                                    <td style="text-align: start !important;">{{ $rm->sensory }}</td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </td>

                                        <td>{{ $d->duration }}</td>
                                        <td>{{ $d->mixing_paddle_on ? '✔' : '-' }}</td>
                                        <td>{{ $d->mixing_paddle_off ? '✔' : '-' }}</td>
                                        <td>{{ $d->pressure }}</td>
                                        <td>{{ $d->target_temperature }}</td>
                                        <td>{{ $d->actual_temperature }}</td>

                                        <td>{{ $d->color }}</td>
                                        <td>{{ $d->aroma }}</td>
                                        <td>{{ $d->taste }}</td>
                                        <td>{{ $d->texture }}</td>

                                        <td>{{ $d->notes }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('report_siomays.add_detail', $r->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary mt-2" title="Tambah Detail">
                                    Tambah Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">Belum ada laporan</td>
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