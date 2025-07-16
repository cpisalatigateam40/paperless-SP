@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Laporan Fessman Cooking</h4>
            <a href="{{ route('report_fessman_cookings.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Section</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->section->section_name ?? '-' }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button type="button" class="btn btn-sm btn-info" onclick="toggleDetail({{ $report->id }})">
                                <i class="bi bi-eye"></i> Lihat Detail
                            </button>

                            <a href="{{ route('report_fessman_cookings.edit', $report->uuid) }}"
                                class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Update Laporan
                            </a>

                            <form action="{{ route('report_fessman_cookings.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_fessman_cookings.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_fessman_cookings.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary">
                                🖨 Cetak PDF
                            </a>

                        </td>
                    </tr>

                    <tr id="detail-{{ $report->id }}" class="d-none">
                        <td colspan="6">
                            <div class="table-responsive">
                                <table class="table table-bordered small">
                                    <tbody>
                                        {{-- Produk --}}
                                        <tr>
                                            <td>Nama Produk</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Kode Produksi</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->production_code ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Untuk Kemasan (gr)</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->packaging_weight ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Jumlah Trolley</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->trolley_count ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Jam Mulai</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->start_time ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Jam Selesai</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->end_time ?? '-' }}</td>
                                            @endforeach
                                        </tr>

                                        {{-- A. Tahap Pemasakan --}}
                                        <tr class="table-secondary">
                                            <td colspan="{{ 1 + $report->details->count() }}"
                                                class="text-start fw-semibold">
                                                A. Tahap Pemasakan (Setting / Aktual)
                                            </td>
                                        </tr>
                                        @php
                                        $steps = [
                                        'DRYING 1','DRYING 2','DRYING 3','DRYING 4','DRYING 5','DOOR OPENING SECTION 1',
                                        'PUT CORE PROBE','SMOKING 2','LP STEAM COOKING 1','LP STEAM COOKING 2',
                                        'STEAM SUCTION','DOOR OPENING SECTION 1','REMOVE CORE PROBE','FURTHER TRANSPORT'
                                        ];
                                        $fields = [
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ['db'=>'room_temp','label'=>'Suhu Ruang (°C)'],
                                        ['db'=>'air_circulation','label'=>'Sirkulasi Udara'],
                                        ['db'=>'product_temp','label'=>'Suhu Produk (°C)'],
                                        ['db'=>'actual_product_temp','label'=>'Suhu Aktual Produk']
                                        ];
                                        @endphp

                                        @foreach ($steps as $stepName)
                                        <tr style="background-color: seashell;">
                                            <td>{{ $stepName }}</td>
                                            @foreach ($report->details as $detail)
                                            <td></td>
                                            @endforeach
                                        </tr>
                                        @foreach ($fields as $field)
                                        @php
                                        $hasAnyValue = false;
                                        foreach ($report->details as $detail) {
                                        $stepData = optional($detail?->processSteps)->first(function($s) use($stepName)
                                        {
                                        return strcasecmp(trim($s->step_name), trim($stepName)) === 0;
                                        });

                                        if ($field['db'] == 'actual_product_temp') {
                                        if (!empty($stepData?->actual_product_temp)) {
                                        $hasAnyValue = true;
                                        break;
                                        }
                                        } else {
                                        if (!empty($stepData?->{$field['db'].'_1'}) ||
                                        !empty($stepData?->{$field['db'].'_2'})) {
                                        $hasAnyValue = true;
                                        break;
                                        }
                                        }
                                        }
                                        @endphp

                                        @if($hasAnyValue)
                                        <tr>
                                            <td>{{ $field['label'] }}</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $stepData = optional($detail?->processSteps)->first(function($s)
                                            use($stepName) {
                                            return trim($s->step_name) == trim($stepName);
                                            });
                                            $v1 = $stepData[$field['db'].'_1'] ?? '-';
                                            $v2 = $stepData[$field['db'].'_2'] ?? '-';
                                            $actual = $stepData['actual_product_temp'] ?? '-';
                                            @endphp
                                            @if($field['db']=='actual_product_temp')
                                            <td>{{ $actual !== null && $actual !== '' ? $actual : '-' }}</td>
                                            @else
                                            <td>{{ ($v1 !== '-' || $v2 !== '-') ? "$v1 / $v2" : '-' }}</td>
                                            @endif
                                            @endforeach
                                        </tr>
                                        @endif
                                        @endforeach
                                        @endforeach

                                        {{-- B. Sensorik --}}
                                        <tr class="table-secondary">
                                            <td colspan="{{ 1 + $report->details->count() }}"
                                                class="text-start fw-semibold">B. Sensorik</td>
                                        </tr>
                                        @foreach(['ripeness'=>'Kematangan','aroma'=>'Aroma','taste'=>'Rasa','texture'=>'Tekstur','color'=>'Warna']
                                        as $field=>$label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            @foreach ($report->details as $detail)
                                            @php $value = optional($detail->sensoryCheck)->$field; @endphp
                                            <td>{{ $value === null ? '-' : ($value ? 'OK' : 'Tidak OK') }}</td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td>Bisa / Tidak Bisa Di Ulir</td>
                                            @foreach ($report->details as $detail)
                                            @php $value = optional($detail->sensoryCheck)->can_be_twisted; @endphp
                                            <td>{{ $value === null ? '-' : ($value ? 'Bisa' : 'Tidak Bisa') }}</td>
                                            @endforeach
                                        </tr>

                                        {{-- C. Tahap Cooling --}}
                                        <tr class="table-secondary">
                                            <td colspan="{{ 1 + $report->details->count() }}"
                                                class="text-start fw-semibold">
                                                C. Tahap Cooling (Setting / Aktual)
                                            </td>
                                        </tr>
                                        @php
                                        $coolingSteps = [
                                        'AIR COOLING WITH SHOWER INTER 1',
                                        'BLOWER SHOWER OUT SECTION 2',
                                        'AIR COOLING WITH SHOWER INTER 2',
                                        'OUT TRANSPORT',
                                        'SUHU PRODUK KELUAR',
                                        'COOKING LOSS'
                                        ];
                                        $coolingFields = [
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ['db'=>'rh','label'=>'RH (%)'],
                                        ['db'=>'product_temp_after_exit','label'=>'Suhu Pusat Produk Setelah Keluar
                                        (°C)'],
                                        ['db'=>'avg_product_temp_after_exit','label'=>'Suhu Rata-rata Pusat Produk'],
                                        ['db'=>'raw_weight','label'=>'Berat Mentah'],
                                        ['db'=>'cooked_weight','label'=>'Berat Matang'],
                                        ['db'=>'loss_kg','label'=>'Loss (kg)'],
                                        ['db'=>'loss_percent','label'=>'Loss (%)'],
                                        ];
                                        @endphp

                                        @foreach ($coolingSteps as $stepName)
                                        <tr style="background-color: seashell;">
                                            <td>{{ $stepName }}</td>
                                            @foreach ($report->details as $detail)
                                            <td></td>
                                            @endforeach
                                        </tr>
                                        @foreach ($coolingFields as $field)
                                        @php
                                        $hasAnyValue = false;
                                        foreach ($report->details as $detail) {
                                        $stepData = optional($detail?->coolingDowns)->first(function($s) use($stepName)
                                        {
                                        return trim($s->step_name) == trim($stepName);
                                        });
                                        if
                                        (in_array($field['db'],['avg_product_temp_after_exit','raw_weight','cooked_weight','loss_kg','loss_percent']))
                                        {
                                        if (!empty($stepData?->{$field['db']})) { $hasAnyValue = true; break; }
                                        } elseif($field['db']=='product_temp_after_exit') {
                                        if (!empty($stepData?->product_temp_after_exit_1) ||
                                        !empty($stepData?->product_temp_after_exit_2) ||
                                        !empty($stepData?->product_temp_after_exit_3)) {
                                        $hasAnyValue = true; break;
                                        }
                                        } else {
                                        if (!empty($stepData?->{$field['db'].'_1'}) ||
                                        !empty($stepData?->{$field['db'].'_2'})) {
                                        $hasAnyValue = true; break;
                                        }
                                        }
                                        }
                                        @endphp
                                        @if($hasAnyValue)
                                        <tr>
                                            <td>{{ $field['label'] }}</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $stepData = optional($detail?->coolingDowns)->first(function($s)
                                            use($stepName) {
                                            return trim($s->step_name) == trim($stepName);
                                            });
                                            $v1 = $stepData[$field['db'].'_1'] ?? '-';
                                            $v2 = $stepData[$field['db'].'_2'] ?? '-';
                                            $v3 = $stepData[$field['db'].'_3'] ?? '-';
                                            $single = $stepData[$field['db']] ?? '-';
                                            @endphp

                                            @if(in_array($field['db'],['avg_product_temp_after_exit','raw_weight','cooked_weight','loss_kg','loss_percent']))
                                            <td>{{ $single }}</td>
                                            @elseif($field['db']=='product_temp_after_exit')
                                            <td>{{ "$v1 / $v2 / $v3" }}</td>
                                            @else
                                            <td>{{ "$v1 / $v2" }}</td>
                                            @endif
                                            @endforeach
                                        </tr>
                                        @endif
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
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

function toggleDetail(id) {
    let row = document.getElementById('detail-' + id);
    if (row.classList.contains('d-none')) {
        row.classList.remove('d-none');
    } else {
        row.classList.add('d-none');
    }
}
</script>
@endsection