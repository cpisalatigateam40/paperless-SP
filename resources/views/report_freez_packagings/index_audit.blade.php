@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h6 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Pembekuan, Pengemasan Sekunder, dan Release Produk
            </h6>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_freez_packagings.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, catatan, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_freez_packagings.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                <x-audit-bulk-auto route-prefix="report_freez_packagings" />

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_freez_packagings.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Data Operasional
                </a>
                @endhasanyrole
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
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
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Kode Produksi</th>
                            <th>Ketidaksesuaian</th>
                            <th>Catatan</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        @php
                            $codes = $report->details->pluck('production_code')->filter()->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;

                            $count = 0;
                            foreach ($report->details as $dt) {
                                if ($dt->verif_after === 'x') {
                                    $count++;
                                    continue;
                                }
                                if (optional($dt->kartoning)->carton_condition === 'x') {
                                    $count++;
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>
                                @if($codes)
                                    @if(strlen($codes) > 50)
                                        <span id="{{ $collapseId }}-short">
                                            {{ \Illuminate\Support\Str::limit($codes, 50) }}
                                            <a class="ms-1" href="#" onclick="toggleCodes('{{ $collapseId }}'); return false;">Show more</a>
                                        </span>
                                        <span id="{{ $collapseId }}-full" class="d-none">
                                            {{ $codes }}
                                            <a class="ms-1" href="#" onclick="toggleCodes('{{ $collapseId }}'); return false;">Show less</a>
                                        </span>
                                    @else
                                        {{ $codes }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $count > 0 ? 'Ada' : '-' }}</td>
                            <td>{{ $report->notes }}</td>
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @can('edit report')
                                <a href="{{ $report->is_audit
                                        ? route('report_freez_packagings.edit', $report->uuid)
                                        : route('report_freez_packagings.copy-to-audit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning"
                                    title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Tambah detail: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('create report')
                                    <a href="{{ route('report_freez_packagings.add-detail', $report->uuid) }}"
                                        class="btn btn-sm btn-success" title="Tambah Detail">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                    @endcan
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_freez_packagings.destroy', $report->uuid) }}"
                                        method="POST" onsubmit="return confirm('Hapus data audit ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                    @if(!$report->known_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report_freez_packagings.known', $report->id) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        @endif
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
                                        @if($report->is_audit)
                                        <form action="{{ route('report_freez_packagings.approve', $report->id) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                        </form>
                                        @endif
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
                                <a href="{{ route('report_freez_packagings.export_pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_freez_packagings" />
                                @endhasanyrole
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="10">
                                <div class="table-responsive p-2 border rounded">
                                    <table class="table table-bordered text-center table-sm align-middle"
                                        style="font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Nama Produk</th>
                                                <th rowspan="2">Gramase</th>
                                                <th rowspan="2">Kode Produksi</th>
                                                <th rowspan="2">Best Before</th>
                                                <th rowspan="2">Release or Hold</th>
                                                <th rowspan="2">Tindakan Perbaikan</th>
                                                <th rowspan="2">Catatan Status Produk</th>
                                                <th rowspan="2">Waktu Proses</th>
                                                <th colspan="6">PEMBEKUAN</th>
                                                <th colspan="14">KARTONING</th>
                                            </tr>
                                            <tr>
                                                <th>Mesin</th>
                                                <th>Suhu Aktual Produk</th>
                                                <th>Standar Suhu</th>
                                                <th>Suhu Room IQF/ABF</th>
                                                <th>Dokumentasi</th>
                                                <th>Catatan Pembekuan</th>

                                                <th>Kondisi Karton</th>
                                                <th>Kondisi Label</th>
                                                <th>Isi per Kemasan Sekunder</th>
                                                <th>Isi per binded *prod. binded</th>
                                                <th>Isi per Inner *RTG</th>
                                                <th>Berat Standar (kg)</th>
                                                <th>Berat Karton 1</th>
                                                <th>Berat Karton 2</th>
                                                <th>Berat Karton 3</th>
                                                <th>Berat Karton 4</th>
                                                <th>Berat Karton 5</th>
                                                <th>Rata-Rata Berat</th>
                                                <th>Dokumentasi</th>
                                                <th>Catatan Pengemasan Sekunder</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($report->details as $detail)
                                            <tr>
                                                <td class="align-middle">{{ $detail->product->product_name ?? '-' }}</td>
                                                <td class="align-middle">
                                                    {{ !empty($detail->gramase)
                                                        ? $detail->gramase
                                                        : ($detail->product->nett_weight ?? '-') }} g
                                                </td>
                                                <td class="align-middle">{{ $detail->production_code ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->best_before ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->release_status ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->corrective_action ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->notes ?? '-' }}</td>
                                                <td class="align-middle">
                                                    {{ $detail->start_time ? \Carbon\Carbon::parse($detail->start_time)->format('H:i') : '-' }}
                                                    -
                                                    {{ $detail->end_time ? \Carbon\Carbon::parse($detail->end_time)->format('H:i') : '-' }}
                                                </td>

                                                {{-- Freezing --}}
                                                <td class="align-middle">
                                                    {{ $detail->freezing->iqf_machine ?? '-' }} ({{ $detail->freezing->machine_type ?? '-' }})
                                                </td>
                                                <td class="align-middle">
                                                    @if($detail->freezing && $detail->freezing->actualTemps->count())
                                                        @foreach($detail->freezing->actualTemps as $temp)
                                                            {{ number_format($temp->actual_temp, 2) }}°C
                                                            @if(!$loop->last)<br>@endif
                                                        @endforeach
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="align-middle">{{ $detail->freezing->standard_temp ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->freezing->iqf_room_temp ?? '-' }}</td>
                                                <td class="align-middle">
                                                    @if($detail->documentations->count())
                                                        <div class="d-flex flex-wrap justify-content-center gap-1">
                                                            @foreach($detail->documentations as $doc)
                                                                <a href="{{ asset('storage/'.$doc->image) }}" target="_blank">
                                                                    <img src="{{ asset('storage/'.$doc->image) }}"
                                                                        alt="Dokumentasi"
                                                                        style="width:50px;height:50px;object-fit:cover;border:1px solid #ddd;border-radius:4px;">
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="align-middle">{{ $detail->freezing->notes ?? '-' }}</td>

                                                {{-- Kartoning --}}
                                                <td class="align-middle">{{ $detail->kartoning->carton_condition ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->label_condition ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->content_bag ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->content_binded ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->content_rtg ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->carton_weight_standard ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_1 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_2 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_3 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_4 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->weight_5 ?? '-' }}</td>
                                                <td class="align-middle">{{ $detail->kartoning->avg_weight ?? '-' }}</td>
                                                <td class="align-middle">
                                                    @if($detail->kartoningDocumentations->count())
                                                        <div class="d-flex flex-wrap justify-content-center gap-1">
                                                            @foreach($detail->kartoningDocumentations as $doc)
                                                                <a href="{{ asset('storage/'.$doc->image) }}" target="_blank">
                                                                    <img src="{{ asset('storage/'.$doc->image) }}"
                                                                        alt="Dokumentasi"
                                                                        style="width:50px;height:50px;object-fit:cover;border:1px solid #ddd;border-radius:4px;">
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="align-middle">{{ $detail->kartoning->notes ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="22" class="text-center text-muted">Tidak ada detail</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
    }, 3000);
});

function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection