@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Pemeriksaan Pra Operasi</h4>
            <a href="{{ route('report_pre_operations.create') }}" class="btn btn-primary btn-sm">Tambah</a>
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
                        <th class="align-middle text-center">Tanggal</th>
                        <th class="align-middle text-center">Shift</th>
                        <th class="align-middle text-center">Area</th>
                        <th class="align-middle text-center">Produk</th>
                        <th class="align-middle text-center">Kode Produksi</th>
                        <th class="align-middle text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td class="align-middle text-center">{{ $report->date }}</td>
                        <td class="align-middle text-center">{{ $report->shift }}</td>
                        <td class="align-middle">{{ $report->area->name ?? '-' }}</td>
                        <td class="align-middle">{{ $report->product->product_name ?? '-' }}</td>
                        <td class="align-middle text-center">{{ $report->production_code }}</td>
                        <td class="align-middle text-center d-flex" style="gap: .3rem;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_pre_operations.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            <a href="{{ route('report_pre_operations.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary">
                                🖨 Cetak PDF
                            </a>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_pre_operations.approve', $report->id) }}" method="POST"
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
                                                <th colspan="4" class="align-middle text-center">Penilaian Kondisi</th>
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
                                            {{-- === BAHAN BAKU & PENUNJANG === --}}
                                            <tr>
                                                <td colspan="8" class="fw-bold">BAHAN BAKU & PENUNJANG</td>
                                            </tr>
                                            @foreach ($report->materials as $j => $item)
                                            <tr>
                                                <td class="text-center">{{ $j + 1 }}</td>
                                                <td class="text-left">{{ strtoupper($item->item) }}</td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [1,2]) ? $item->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [3,4]) ? $item->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [5,6]) ? $item->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [7,8]) ? $item->condition : '' }}</td>
                                                <td class="text-left">{{ $item->corrective_action }}</td>
                                                <td class="text-center">{!! $item->verification == '1' ? '✔' : '✘' !!}
                                                </td>
                                            </tr>
                                            @foreach($item->followups as $index => $followup)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td colspan="5">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                                <td>{{ $followup->notes }} - {{ $followup->corrective_action }}</td>
                                                <td class="text-center">{{ $followup->verification == '1' ? '✔' : '✘' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            {{-- === KEMASAN === --}}
                                            <tr>
                                                <td colspan="8" class="fw-bold">KEMASAN</td>
                                            </tr>
                                            @foreach ($report->packagings as $j => $item)
                                            <tr>
                                                <td class="text-center">{{ $j + 1 }}</td>
                                                <td class="text-left">{{ strtoupper($item->item) }}</td>
                                                <td class="text-center">
                                                    {{ in_array($item->condition, [1,2]) ? $item->condition : '' }}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-left">{{ $item->corrective_action }}</td>
                                                <td class="text-center">{!! $item->verification == '1' ? '✔' : '✘' !!}
                                                </td>
                                            </tr>
                                            @foreach($item->followups as $index => $followup)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td colspan="5">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                                <td>{{ $followup->notes }} - {{ $followup->corrective_action }}</td>
                                                <td class="text-center">{{ $followup->verification == '1' ? '✔' : '✘' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            {{-- === MESIN & PERALATAN === --}}
                                            <tr>
                                                <td colspan="8" class="fw-bold">MESIN & PERALATAN</td>
                                            </tr>
                                            @foreach ($report->equipments as $j => $eq)
                                            <tr>
                                                <td class="text-center">{{ $j + 1 }}</td>
                                                <td class="text-left">{{ strtoupper($eq->equipment->name ?? '-') }}</td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [1,2]) ? $eq->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [3,4]) ? $eq->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [5,6]) ? $eq->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($eq->condition, [7,8]) ? $eq->condition : '' }}</td>
                                                <td class="text-left">{{ $eq->corrective_action }}</td>
                                                <td class="text-center">{!! $eq->verification == '1' ? '✔' : '✘' !!}
                                                </td>
                                            </tr>
                                            @foreach($eq->followups as $index => $followup)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td colspan="5">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                                <td>{{ $followup->notes }} - {{ $followup->corrective_action }}</td>
                                                <td class="text-center">{{ $followup->verification == '1' ? '✔' : '✘' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            {{-- === KONDISI RUANGAN === --}}
                                            <tr>
                                                <td colspan="8" class="fw-bold">KONDISI RUANGAN</td>
                                            </tr>
                                            @foreach ($report->rooms as $j => $room)
                                            <tr>
                                                <td class="text-center">{{ $j + 1 }}</td>
                                                <td class="text-left">
                                                    {{ strtoupper($room->section->section_name ?? '-') }}</td>
                                                <td></td>
                                                <td class="text-center">
                                                    {{ in_array($room->condition, [3,4]) ? $room->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($room->condition, [5,6]) ? $room->condition : '' }}</td>
                                                <td class="text-center">
                                                    {{ in_array($room->condition, [7,8]) ? $room->condition : '' }}</td>
                                                <td class="text-left">{{ $room->corrective_action }}</td>
                                                <td class="text-center">{!! $room->verification == '1' ? '✔' : '✘' !!}
                                                </td>
                                            </tr>
                                            @foreach($room->followups as $index => $followup)
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
                                        <li>1 - Sesuai Spesifikasi</li>
                                        <li>2 - Tidak Sesuai Spesifikasi</li>
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