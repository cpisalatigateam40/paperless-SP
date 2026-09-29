@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>
                    <i class="fas fa-user-shield mr-1"></i>
                    Verifikasi Bahan Baku dan Bahan Penunjang (Data Audit)
                </h5>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    {{-- SEARCH --}}
                    <form method="GET" action="{{ route('report_rm_arrivals.audit') }}" class="d-flex align-items-center"
                        style="gap: .4rem;">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control form-control-sm mr-2"
                                placeholder="Cari catatan, shift..." value="{{ request('search') }}">
                            <select name="per_page" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                @foreach([5, 10, 25] as $n)
                                    <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                        {{ $n }} / halaman
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
                        </div>
                        @if(request('search') || request('per_page'))
                            <a href="{{ route('report_rm_arrivals.audit') }}" class="btn btn-sm btn-outline-danger">Reset</a>
                        @endif
                    </form>

                    <div class="vr"></div>

                    <a href="{{ route('report_rm_arrivals.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Data Operasional
                    </a>
                </div>
            </div>

            <div class="card-body" style="padding-top: 1rem !important;">
                {{-- Alert --}}
                @if(session('success'))
                    <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('info'))
                    <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
                @endif
                @if ($errors->any())
                    <div id="error-alert" class="alert alert-danger">
                        <ul>@foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-muted small">
                    Daftar gabungan salinan data audit dan data operasional yang belum di-copy.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Waktu</th>
                                <th>Area</th>
                                <th>Section</th>
                                <th>Kode Produksi</th>
                                <th>Ketidaksesuaian</th>
                                <th>Dibuat oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $report)
                                @php
                                    $codes = $report->details->pluck('production_code')->filter()->implode(', ');
                                    $collapseId = 'codes-' . $report->uuid;
                                @endphp
                                <tr>
                                    <td>{{ $records->firstItem() + $loop->index }}</td>
                                    <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                                    <td>{{ $report->shift }}</td>
                                    <td>{{ $report->created_at->format('H:i') }}</td>
                                    <td>{{ $report->area->name ?? '-' }}</td>
                                    <td>
                                        {{ $report->section->section_name ?? '-' }}
                                        @if($report->is_audit)
                                            <span class="badge bg-secondary">Audit</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($codes)
                                            @if(strlen($codes) > 50)
                                                <span id="{{ $collapseId }}-short">
                                                    {{ \Illuminate\Support\Str::limit($codes, 50) }}
                                                    <a class="ms-1" href="#"
                                                        onclick="toggleCodes('{{ $collapseId }}'); return false;">Show more</a>
                                                </span>
                                                <span id="{{ $collapseId }}-full" class="d-none">
                                                    {{ $codes }}
                                                    <a class="ms-1" href="#"
                                                        onclick="toggleCodes('{{ $collapseId }}'); return false;">Show less</a>
                                                </span>
                                            @else
                                                {{ $codes }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $report->ketidaksesuaian > 0 ? 'Ada' : '-' }}</td>
                                    <td>{{ $report->created_by }}</td>
                                    <td>
                                        {{-- Toggle Detail --}}
                                        <button class="btn btn-sm btn-info toggle-detail"
                                            data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($report->is_audit)
                                            {{-- Edit data audit --}}
                                            <a href="{{ route('report_rm_arrivals.edit', $report->uuid) }}"
                                                class="btn btn-sm btn-warning" title="Edit Data Audit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- Known --}}
                                            @can('known report')
                                                @if(!$report->known_by)
                                                    <form action="{{ route('report_rm_arrivals.known', $report->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Ketahui laporan ini?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-success"
                                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                                        ✔ {{ $report->known_by }}
                                                    </span>
                                                @endif
                                            @else
                                                @if($report->known_by)
                                                    <span class="badge bg-success"
                                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                                        ✔ {{ $report->known_by }}
                                                    </span>
                                                @endif
                                            @endcan

                                            {{-- Approve --}}
                                            @can('approve report')
                                                @if(!$report->approved_by)
                                                    <form action="{{ route('report_rm_arrivals.approve', $report->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Setujui laporan ini?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                            <i class="fas fa-thumbs-up"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-success"
                                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                                        ✔ {{ $report->approved_by }}
                                                    </span>
                                                @endif
                                            @else
                                                @if($report->approved_by)
                                                    <span class="badge bg-success"
                                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                                        ✔ {{ $report->approved_by }}
                                                    </span>
                                                @endif
                                            @endcan
                                        @else
                                            {{-- Belum ada salinan audit: buat salinan lalu buka form edit --}}
                                            <a href="{{ route('report_rm_arrivals.copy-to-audit', $report->uuid) }}"
                                                class="btn btn-sm btn-outline-primary" title="Salin & Edit Data Audit">
                                                <i class="fas fa-user-shield"></i>
                                            </a>
                                        @endif

                                        {{-- Export PDF --}}
                                        <a href="{{ route('report_rm_arrivals.export-pdf', $report->uuid) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary" title="Export PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>

                                {{-- DETAIL --}}
                                <tr id="detail-{{ $report->id }}" class="d-none">
                                    <td colspan="10">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr class="text-center align-middle">
                                                    <th rowspan="2" class="align-middle">Jam</th>
                                                    <th rowspan="2" class="align-middle">Raw Material</th>
                                                    <th rowspan="2" class="align-middle">Kondisi RM</th>
                                                    <th rowspan="2" class="align-middle">Produsen / Supplier</th>
                                                    <th rowspan="2" class="align-middle">Kode Produksi / Expired Date</th>
                                                    <th rowspan="2" class="align-middle">Kondisi Kemasan</th>
                                                    <th colspan="2" class="align-middle">Kondisi Bahan</th>
                                                    <th rowspan="2" class="align-middle">Kontaminasi</th>
                                                    <th rowspan="2" class="align-middle">Status</th>
                                                    <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                                    <th rowspan="2" class="align-middle">Catatan</th>
                                                </tr>
                                                <tr class="text-center align-middle">
                                                    <th class="align-middle">Suhu Bahan (°C)</th>
                                                    <th class="align-middle">Sensorik</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($report->details as $detail)
                                                    <tr>
                                                        <td class="text-center">{{ $detail->time ?? '-' }}</td>
                                                        <td>
                                                            @if ($detail->material_type === 'raw')
                                                                {{ $detail->rawMaterial?->material_name }}
                                                            @else
                                                                {{ $detail->premix?->name }} (Premix)
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $detail->rm_condition }}</td>
                                                        <td>{{ implode(', ', explode(',', $detail->supplier)) }}</td>
                                                        <td>{{ $detail->production_code ?? '-' }}</td>
                                                        <td class="text-center">{{ $detail->packaging_condition }}</td>
                                                        <td class="text-center">{{ $detail->temperature }}</td>
                                                        <td class="text-center">
                                                            Kenampakan: {{ $detail->sensory_appearance }},
                                                            Aroma: {{ $detail->sensory_aroma }},
                                                            Warna: {{ $detail->sensory_color }}
                                                        </td>
                                                        <td class="text-center">{{ $detail->contamination }}</td>
                                                        <td class="text-center">{{ $detail->status }}</td>
                                                        <td>{{ $detail->corrective_action ?? '-' }}</td>
                                                        <td>{{ $detail->problem ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <p class="mt-2">Catatan: {{ $report->notes }}</p>

                                        @if($report->is_audit)
                                            @can('create report')
                                                <div class="mb-2 d-flex justify-content-end mt-3">
                                                    <a href="{{ route('report_rm_arrivals.add_detail', $report->uuid) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        + Tambah Pemeriksaan
                                                    </a>
                                                </div>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- PAGINATION --}}
                    <div class="d-flex justify-content-end mt-3">
                        {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            setTimeout(() => {
                $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
            }, 3000);

            $('.toggle-detail').on('click', function () {
                const target = $(this.dataset.target);
                const isHidden = target.hasClass('d-none');

                $('tr[id^="detail-"]').addClass('d-none');
                $('.toggle-detail').html('<i class="fas fa-eye"></i>');

                if (isHidden) {
                    target.removeClass('d-none');
                    $(this).html('<i class="fas fa-eye-slash"></i>');
                }
            });
        });

        function toggleCodes(id) {
            document.getElementById(id + '-short').classList.toggle('d-none');
            document.getElementById(id + '-full').classList.toggle('d-none');
        }
    </script>
@endsection