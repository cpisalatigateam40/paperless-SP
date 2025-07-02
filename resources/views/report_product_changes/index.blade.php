@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Verifikasi Pergantian Produk</h4>
            <a href="{{ route('report_product_changes.create') }}" class="btn btn-primary btn-sm">Tambah</a>
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
                        <th class="align-middle text-center">No</th>
                        <th class="align-middle">Tanggal</th>
                        <th class="align-middle">Produk</th>
                        <th class="align-middle">Kode Produksi</th>
                        <th class="align-middle">Shift</th>
                        <th class="align-middle text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $i => $report)
                    <tr>
                        <td class="align-middle text-center">{{ $i + 1 }}</td>
                        <td class="align-middle">{{ $report->date }}</td>
                        <td class="align-middle">{{ $report->product->product_name ?? '-' }}</td>
                        <td class="align-middle">{{ $report->production_code }}</td>
                        <td class="align-middle">{{ $report->shift }}</td>
                        <td class="align-middle d-flex" style="gap: .3rem;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_product_changes.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus laporan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            <a href="{{ route('report_product_changes.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_product_changes.approve', $report->id) }}" method="POST"
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
                        </td>
                    </tr>

                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="6">
                            <div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="align-middle text-center">No</th>
                                                <th rowspan="2" class="align-middle text-left">Parameter Pengecekan</th>
                                                <th colspan="4" class="align-middle text-center">Penilaian Kondisi Bahan
                                                    / Peralatan</th>
                                                <th rowspan="2" class="align-middle text-center">Tindakan Koreksi</th>
                                                <th rowspan="2" class="align-middle text-center">Verifikasi</th>
                                            </tr>
                                            <tr>
                                                <th class="align-middle text-center">1 / 2</th>
                                                <th class="align-middle text-center">3 / 4</th>
                                                <th class="align-middle text-center">5 / 6</th>
                                                <th class="align-middle text-center">7 / 8</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- === SISA BAHAN DAN KEMASAN === --}}
                                            <tr>
                                                <td colspan="8" class="text-left fw-bold">SISA BAHAN DAN KEMASAN</td>
                                            </tr>
                                            @foreach ($report->materialLeftovers as $i => $item)
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td class="text-left">{{ strtoupper($item->item) }}</td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [1, 2]) ? $item->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [3, 4]) ? $item->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [5, 6]) ? $item->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [7, 8]) ? $item->condition : '' }}
                                                </td>
                                                <td class="text-left">{{ $item->corrective_action }}</td>
                                                <td class="text-center">
                                                    {!! $item->verification == '1' ? '✔' : '✘' !!}
                                                </td>
                                            </tr>
                                            @foreach($item->followups as $index => $followup)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td colspan="5">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                                <td>{{ $followup->notes }} - {{ $followup->corrective_action }}</td>
                                                <td class="text-center">
                                                    {{ $followup->verification == '1' ? '✔' : '✘' }}</td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            {{-- === MESIN DAN PERALATAN === --}}
                                            <tr>
                                                <td colspan="8" class="text-left fw-bold">MESIN DAN PERALATAN</td>
                                            </tr>
                                            @foreach ($report->equipments as $i => $eq)
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td class="text-left">{{ strtoupper($eq->equipment->name ?? '-') }}</td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [1, 2]) ? $eq->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [3, 4]) ? $eq->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [5, 6]) ? $eq->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [7, 8]) ? $eq->condition : '' }}
                                                </td>
                                                <td class="text-left">{{ $eq->corrective_action }}</td>
                                                <td class="text-center">
                                                    {!! $eq->verification == '1' ? '✔' : '✘' !!}
                                                </td>
                                            </tr>
                                            @foreach($eq->followups as $index => $followup)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td colspan="5">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                                <td>{{ $followup->notes }} - {{ $followup->corrective_action }}</td>
                                                <td class="text-center">
                                                    {{ $followup->verification == '1' ? '✔' : '✘' }}</td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            {{-- === KONDISI RUANGAN === --}}
                                            <tr>
                                                <td colspan="8" class="text-left fw-bold">KONDISI RUANGAN</td>
                                            </tr>
                                            @foreach ($report->sections as $i => $sec)
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td class="text-left">
                                                    {{ strtoupper($sec->section->section_name ?? '-') }}
                                                </td>
                                                <td></td> {{-- kolom 1/2 tidak berlaku --}}
                                                <td class="text-center">
                                                    {{ in_array($sec->condition, [3, 4]) ? $sec->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($sec->condition, [5, 6]) ? $sec->condition : '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ in_array($sec->condition, [7, 8]) ? $sec->condition : '' }}
                                                </td>
                                                <td class="text-left">{{ $sec->corrective_action }}</td>
                                                <td class="text-center">
                                                    {!! $sec->verification == '1' ? '✔' : '✘' !!}
                                                </td>
                                            </tr>
                                            @foreach($sec->followups as $index => $followup)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td colspan="5">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                                <td>{{ $followup->notes }} - {{ $followup->corrective_action }}</td>
                                                <td class="text-center">{{ $followup->verification == '1' ? '✔' : '✘' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3 p-3">
                                    <p><strong>Keterangan Pengecekan:</strong></p>
                                    <ul>
                                        <li>1 - Bersih, tidak ada sisa bahan/kemasan sebelumnya</li>
                                        <li>2 - Ada sisa bahan/kemasan sebelumnya</li>
                                        <li>3 - Bebas dari kontaminan dan bahan sebelumnya</li>
                                        <li>4 - Ada kontaminan atau sisa bahan sebelumnya</li>
                                        <li>5 - Bebas dari potensi kontaminasi allergen</li>
                                        <li>6 - Ada potensi kontaminasi allergen</li>
                                        <li>7 - Bersih, tidak ada kontaminan/kotoran, tidak tercium bau menyimpang</li>
                                        <li>8 - Tidak bersih, ada kontaminan/kotoran, tercium bau menyimpang</li>
                                    </ul>
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