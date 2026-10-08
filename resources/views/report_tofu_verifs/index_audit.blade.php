@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Hasil Produksi Tofu
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_tofu_verifs.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_tofu_verifs.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_tofu_verifs.index') }}" class="btn btn-sm btn-outline-secondary">
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
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Kode Produksi</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        @php
                            $codes = $report->productInfos->pluck('production_code')->filter()->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;

                            $user = auth()->user();
                            $canEdit = ! $user->hasRole('auditor')
                                && ($user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2)));
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
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex" style="gap: .2rem;">
                                @php
                                    $user = auth()->user();
                                    $canManage = $user->hasAnyRole(['admin', 'superadmin', 'SPV QC']);
                                    $hasRules = method_exists($report, 'hasAuditNormalizeRules') && $report->hasAuditNormalizeRules();

                                    // ungu: masih ada ketidaksesuaian yang bisa diubah otomatis
                                    $showAuto = $canManage && $hasRules && $report->wouldNormalizeAudit();

                                    // kuning: sudah tidak ada yang perlu diubah otomatis
                                    $showEdit = ! $showAuto
                                        && $canEdit
                                        && ! $user->hasRole('auditor');
                                @endphp

                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($showAuto)
                                    {{-- Edit Otomatis (ungu solid, ikon saja), redirect ke halaman edit --}}
                                    <form action="{{ route('report_tofu_verifs.auto-audit', $report->uuid) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </form>
                                @elseif($showEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report_tofu_verifs.edit', $report->uuid)
                                            : route('report_tofu_verifs.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Export PDF --}}
                                <a href="{{ route('report_tofu_verifs.export', $report->uuid) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="8">
                                <div class="table-responsive mt-2">
                                    @php
                                        $products = $report->productInfos;
                                        $weights = $report->weightVerifs;
                                        $defects = $report->defectVerifs;

                                        $getValue = function ($collection, $type, $index, $field) {
                                            return optional($collection->where('weight_category', $type)
                                                ->values()->get($index))->{$field} ?? '-';
                                        };
                                        $getDefect = function ($collection, $type, $index, $field) {
                                            return optional($collection->where('defect_type', $type)
                                                ->values()->get($index))->{$field} ?? '-';
                                        };

                                        $defectTypes = [
                                            'hole' => 'Berlubang',
                                            'stain' => 'Noda',
                                            'asymmetry' => 'Bentuk tidak bulat simetris',
                                            'other' => 'Lain-lain',
                                            'good' => 'Produk bagus',
                                            'note' => 'Keterangan',
                                        ];
                                    @endphp

                                    <table class="table table-bordered table-sm text-center align-middle">
                                        <tbody>
                                            <tr>
                                                <td class="text-start">Kode Produksi</td>
                                                @foreach($products as $p)
                                                <td>{{ $p->production_code }}</td>
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <td class="text-start">Best Before</td>
                                                @foreach($products as $p)
                                                <td>{{ $p->expired_date }}</td>
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <td class="text-start">Jumlah Sampel (pcs)</td>
                                                @foreach($products as $p)
                                                <td>{{ $p->sample_amount }}</td>
                                                @endforeach
                                            </tr>

                                            <tr class="table-light">
                                                <th class="text-start" colspan="{{ $products->count() + 1 }}">
                                                    Pemeriksaan Berat</th>
                                            </tr>

                                            <tr>
                                                <td class="text-start">- Under (&lt; 11gr/pc)</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getValue($weights, 'under', $i, 'turus') }}<br>
                                                    Jumlah: {{ $getValue($weights, 'under', $i, 'total') }}<br>
                                                    %: {{ $getValue($weights, 'under', $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <td class="text-start">- Standart (11 - 13 gr/pc)</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getValue($weights, 'standard', $i, 'turus') }}<br>
                                                    Jumlah: {{ $getValue($weights, 'standard', $i, 'total') }}<br>
                                                    %: {{ $getValue($weights, 'standard', $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <td class="text-start">- Over (&gt;13 gr/pc)</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getValue($weights, 'over', $i, 'turus') }}<br>
                                                    Jumlah: {{ $getValue($weights, 'over', $i, 'total') }}<br>
                                                    %: {{ $getValue($weights, 'over', $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>

                                            <tr class="table-light">
                                                <th class="text-start" colspan="{{ $products->count() + 1 }}">
                                                    Pemeriksaan Defect</th>
                                            </tr>

                                            @foreach($defectTypes as $key => $label)
                                            <tr>
                                                <td class="text-start">- {{ $label }}</td>
                                                @foreach ($products as $i => $p)
                                                <td>
                                                    Turus: {{ $getDefect($defects, $key, $i, 'turus') }}<br>
                                                    Jumlah: {{ $getDefect($defects, $key, $i, 'total') }}<br>
                                                    %: {{ $getDefect($defects, $key, $i, 'percentage') }}
                                                </td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No reports found.</td>
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