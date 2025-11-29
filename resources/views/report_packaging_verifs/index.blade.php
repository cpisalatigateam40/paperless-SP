@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Pemeriksaan Kemasan Plastik</h4>
            <a href="{{ route('report_packaging_verifs.create') }}" class="btn btn-primary btn-sm">+ Tambah Report</a>
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
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat oleh</th>
                            <th>Action</th>
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
                                <a href="{{ route('report_packaging_verifs.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Delete --}}
                                <form action="{{ route('report_packaging_verifs.destroy', $report->uuid) }}"
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
                                <form action="{{ route('report_packaging_verifs.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report_packaging_verifs.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_packaging_verifs.export-pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="100%">
                                <div class="table-responsive">
                                    @php $details = $report->details; @endphp

                                    <table class="table table-bordered table-sm text-center align-middle mb-4">
                                        <tr>
                                            <th rowspan="2">Jam</th>
                                            <th rowspan="2">Produk</th>
                                            <th rowspan="2">Gramase</th>
                                            <th rowspan="2">Upload MD BPOM, QR Code, Kode Produksi, dan Expire Date</th>
                                            <!-- <th rowspan="2">Upload MD BPOM</th>
                                            <th rowspan="2">Upload QR Code</th>
                                            <th rowspan="2">Upload Kode Produksi & Best Before</th> -->
                                            <th colspan="2">In cutting</th>
                                            <th colspan="2">Proses Pengemasan</th>
                                            <th colspan="2">Sampling Kemasan</th>
                                            <th colspan="2">Hasil Sealing</th>
                                            <th rowspan="2">Isi Per-Pack</th>
                                            <th colspan="3">Panjang Produk Per Pcs</th>
                                            <th colspan="3">Berat Produk Per Pcs</th>
                                            <th colspan="3">Berat Produk Per Pack (gr)</th>
                                            <th rowspan="2">Verifikasi MD</th>
                                            <th rowspan="2">Keterangan</th>
                                        </tr>
                                        <tr>
                                            <th>Manual</th>
                                            <th>Mesin</th>
                                            <th>Thermoformer</th>
                                            <th>Manual</th>

                                            <th>Jumlah Sampling</th>
                                            <th>Hasil Sampling</th>


                                            <th>Kondisi Seal</th>
                                            <th>Vacum</th>
                                            <th>Standar</th>
                                            <th>Aktual</th>
                                            <th>Rata-Rata</th>
                                            <th>Standar</th>
                                            <th>Aktual</th>
                                            <th>Rata-Rata</th>
                                            <th>Standar</th>
                                            <th>Aktual</th>
                                            <th>Rata-Rata</th>
                                        </tr>

                                        @foreach($report->details as $d)
                                        @php $checklist = $d->checklist; @endphp

                                        @for($i = 1; $i <= 5; $i++) <tr>
                                            @if($i == 1)
                                            <td rowspan="5">{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
                                            <td rowspan="5">{{ $d->product->product_name ?? '-' }}</td>
                                            <td rowspan="5">{{ $d->product->nett_weight ?? '-' }} g</td>
                                            <!-- <td rowspan="5">
                                                @if($d->upload_md)
                                                <a href="{{ asset('storage/' . $d->upload_md) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $d->upload_md) }}" alt="Bukti"
                                                        width="60">
                                                </a>
                                                @endif
                                            </td>
                                            <td rowspan="5">
                                                @if($d->upload_qr)
                                                <a href="{{ asset('storage/' . $d->upload_qr) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $d->upload_qr) }}" alt="Bukti"
                                                        width="60">
                                                </a>
                                                @endif
                                            </td>
                                            <td rowspan="5">
                                                @if($d->upload_ed)
                                                <a href="{{ asset('storage/' . $d->upload_ed) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $d->upload_ed) }}" alt="Bukti"
                                                        width="60">
                                                </a>
                                                @endif
                                            </td> -->
                                            <td rowspan="5">
                                                @if(!empty($d->upload_md_multi))
                                                    @php
                                                        $files = json_decode($d->upload_md_multi, true);
                                                    @endphp

                                                    @foreach($files as $file)
                                                        <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $file) }}" alt="Bukti" width="60" style="margin: 4px;">
                                                        </a>
                                                    @endforeach
                                                @endif
                                            </td>


                                            {{-- In cutting manual & mesin sama, rowspan --}}
                                            <td rowspan="5">{{ $checklist?->in_cutting_manual_1 ?? '-' }}</td>
                                            <td rowspan="5">{{ $checklist?->in_cutting_machine_1 ?? '-' }}</td>

                                            {{-- Packaging thermoformer & manual, rowspan --}}
                                            <td rowspan="5">{{ $checklist?->packaging_thermoformer_1 ?? '-' }}</td>
                                            <td rowspan="5">{{ $checklist?->packaging_manual_1 ?? '-' }}</td>

                                            <td rowspan="5">{{ $checklist?->sampling_amount ?? '-' }}
                                                {{ $checklist?->unit ?? '-' }}</td>
                                            <td rowspan="5">{{ $checklist?->sampling_result ?? '-' }}</td>
                                            @endif

                                            {{-- Hasil sealing & isi per-pack, per baris --}}
                                            <td>{{ $checklist?->{'sealing_condition_' . $i} ?? '-' }}</td>
                                            <td>{{ $checklist?->{'sealing_vacuum_' . $i} ?? '-' }}</td>
                                            <td>{{ $checklist?->{'content_per_pack_' . $i} ?? '-' }}</td>

                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->standard_long_pcs ?? '-' }}</td>
                                            @endif
                                            <td>{{ $checklist?->{'actual_long_pcs_' . $i} ?? '-' }}</td>
                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->avg_long_pcs ?? '-' }}</td>
                                            @endif

                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->standard_weight_pcs ?? '-' }}</td>
                                            @endif
                                            <td>{{ $checklist?->{'actual_weight_pcs_' . $i} ?? '-' }}</td>
                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->avg_weight_pcs ?? '-' }}</td>
                                            @endif


                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->standard_weight ?? '-' }}</td>
                                            @endif
                                            <td>{{ $checklist?->{'actual_weight_' . $i} ?? '-' }}</td>
                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->avg_weight ?? '-' }}</td>
                                            @endif

                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->verif_md ?? '-' }}</td>
                                            @endif
                                            @if($i == 1)
                                            <td rowspan="5">{{ $checklist?->notes ?? '-' }}</td>
                                            @endif
                        </tr>
                        @endfor
                        @endforeach

                </table>

            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('report_packaging_verifs.add-detail', $report->uuid) }}"
                    class="btn btn-secondary btn-sm">
                    Tambah Detail
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