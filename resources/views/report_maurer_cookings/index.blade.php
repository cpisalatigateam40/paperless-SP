@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Daftar Pemeriksaan Maurer</h5>

            <a href="{{ route('report_maurer_cookings.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
                        <th>Date</th>
                        <th>Area</th>
                        <th>Section</th>
                        <th>Shift</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->section->section_name ?? '-' }}</td>
                        <td>{{ $report->shift }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            {{-- Toggle Detail --}}
                            <button type="button" class="btn btn-sm btn-info" onclick="toggleDetail({{ $report->id }})"
                                title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Update --}}
                            <a href="{{ route('report_maurer_cookings.edit', $report->uuid) }}"
                                class="btn btn-sm btn-warning" title="Update Laporan">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- Delete --}}
                            <form action="{{ route('report_maurer_cookings.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_maurer_cookings.known', $report->id) }}" method="POST"
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
                            <form action="{{ route('report_maurer_cookings.approve', $report->id) }}" method="POST"
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

                            {{-- Export PDF --}}
                            <a href="{{ route('report_maurer_cookings.export_pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>

                    <tr id="detail-{{ $report->id }}" class="d-none">
                        <td colspan="6">
                            <div class="table-responsive">
                                <table class="table table-bordered small">

                                    {{-- Header produk --}}
                                    <tbody>
                                        {{-- Kode Produksi --}}
                                        <tr>
                                            <td>Nama Produk</td>
                                            @foreach ($report->details as $detail)
                                            <td> {{ $detail->product->product_name ?? '-' }} </td>
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
                                            <td>Jumlah Trolly</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->trolley_count ?? '-' }}</td>
                                            @endforeach
                                        </tr>

                                        {{-- A. Rumah Asap --}}
                                        <tr class="table-secondary">
                                            <td colspan="{{ 1 + $report->details->count() }}"
                                                class="text-start fw-semibold">
                                                A. Rumah Asap (Smoke House) (Setting/ Aktual)
                                            </td>
                                        </tr>

                                        @php
                                        $steps = [
                                        ['no'=>1,'name'=>'SHOWERING','fields'=>[['db'=>'time_minutes','label'=>'Waktu
                                        (menit)']]],
                                        ['no'=>2,'name'=>'WARMING','fields'=>[
                                        ['db'=>'room_temperature','label'=>'Suhu Ruang (°C)'],
                                        ['db'=>'rh','label'=>'RH (%)'],
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ]],
                                        ['no'=>3,'name'=>'DRYINGI','fields'=>[
                                        ['db'=>'room_temperature','label'=>'Suhu Ruang (°C)'],
                                        ['db'=>'rh','label'=>'RH (%)'],
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ]],
                                        ['no'=>4,'name'=>'DRYINGII','fields'=>[
                                        ['db'=>'room_temperature','label'=>'Suhu Ruang (°C)'],
                                        ['db'=>'rh','label'=>'RH (%)'],
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ]],
                                        ['no'=>5,'name'=>'DRYINGIII','fields'=>[
                                        ['db'=>'room_temperature','label'=>'Suhu Ruang (°C)'],
                                        ['db'=>'rh','label'=>'RH (%)'],
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ]],
                                        ['no'=>6,'name'=>'SMOKING','fields'=>[
                                        ['db'=>'room_temperature','label'=>'Suhu Ruang (°C)'],
                                        ['db'=>'rh','label'=>'RH (%)'],
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ]],
                                        ['no'=>7,'name'=>'COOKING','fields'=>[
                                        ['db'=>'room_temperature','label'=>'Suhu Ruang (°C)'],
                                        ['db'=>'product_temperature','label'=>'Suhu Produk (°C)'],
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ]],
                                        ['no'=>8,'name'=>'EVAKUASI','fields'=>[
                                        ['db'=>'time_minutes','label'=>'Waktu (menit)'],
                                        ]],
                                        ];
                                        @endphp

                                        @foreach ($steps as $step)
                                        <tr style="background-color: seashell;">
                                            <td>{{ $step['no'] }} {{ $step['name'] }}</td>
                                            @foreach ($report->details as $detail)
                                            <td></td>
                                            @endforeach
                                        </tr>
                                        @foreach ($step['fields'] as $field)
                                        <tr>
                                            <td>{{ $field['label'] }}</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $stepData = $detail->processSteps->first(function($s) use($step) {
                                            return str_starts_with(trim($s->step_name), trim($step['name']));
                                            });
                                            $v1 = $stepData ? ($stepData[$field['db'].'_1'] ?? '-') : '-';
                                            $v2 = $stepData ? ($stepData[$field['db'].'_2'] ?? '-') : '-';
                                            @endphp
                                            <td>{{ $v1 }} / {{ $v2 }}</td>

                                            @endforeach
                                        </tr>
                                        @endforeach
                                        @endforeach

                                        {{-- Lama Proses --}}
                                        <tr style="background-color: seashell;">
                                            <td>9. LAMA PROSES</td>
                                            @foreach ($report->details as $detail)
                                            <td></td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Jam Mulai</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ optional($detail->totalProcessTime)->start_time ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Jam Selesai</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ optional($detail->totalProcessTime)->end_time ?? '-' }}</td>
                                            @endforeach
                                        </tr>

                                        {{-- Posisi Thermocouple --}}
                                        <tr style="background-color: seashell;">
                                            <td>10. POSISI THERMOCOUPLE</td>
                                            @foreach ($report->details as $detail)
                                            <td>
                                                @foreach ($detail->thermocouplePositions as $pos)
                                                {{ $pos->position_info ?? '-' }}@if (!$loop->last), @endif
                                                @endforeach
                                            </td>
                                            @endforeach
                                        </tr>

                                        {{-- Sensorik --}}
                                        <tr style="background-color: seashell;">
                                            <td>11. SENSORI</td>
                                            @foreach ($report->details as $detail)
                                            <td></td>
                                            @endforeach
                                        </tr>
                                        @foreach(['ripeness'=>'Kematangan','aroma'=>'Rasa
                                        Aroma','texture'=>'Tekstur','color'=>'Warna'] as $field => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $value = optional($detail->sensoryCheck)->$field;
                                            @endphp
                                            <td>{{ $value === null ? '-' : ($value ? 'OK' : 'Tidak OK') }}</td>
                                            @endforeach
                                        </tr>
                                        @endforeach

                                        {{-- Bisa/Tidak Bisa Di Ulir --}}
                                        <tr style="background-color: seashell;">
                                            <td>12. Bisa/Tidak bisa Di Ulir (khusus sosis ayam okey)</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $detail->can_be_twisted === null ? '-' : ($detail->can_be_twisted ? 'Bisa' : 'Tidak Bisa') }}
                                            </td>
                                            @endforeach
                                        </tr>


                                        {{-- B. Showering & Cooling Down --}}
                                        <tr class="table-secondary">
                                            <td colspan="{{ 1 + $report->details->count() }}"
                                                class="text-start fw-semibold">
                                                B. Showering & Cooling Down
                                            </td>
                                        </tr>

                                        {{-- 1 SHOWERING --}}
                                        <tr style="background-color: seashell;">
                                            <td>1 SHOWERING</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ optional($detail->showeringCoolingDown)->showering_time ?? '-' }}
                                            </td>
                                            @endforeach
                                        </tr>

                                        {{-- 2 COOLING DOWN --}}
                                        <tr style="background-color: seashell;">
                                            <td>2 COOLING DOWN</td>
                                            @foreach ($report->details as $detail)
                                            <td></td> {{-- kosong karena judul --}}
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Suhu Ruangan /ST (°C)</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $scd = optional($detail->showeringCoolingDown);
                                            $v1 = $scd->room_temp_1 ?? '-';
                                            $v2 = $scd->room_temp_2 ?? '-';
                                            @endphp
                                            <td>{{ $v1 }} / {{ $v2 }}</td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Suhu Produk /CT (°C)</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $v1 = $scd->product_temp_1 ?? '-';
                                            $v2 = $scd->product_temp_2 ?? '-';
                                            @endphp
                                            <td>{{ $v1 }} / {{ $v2 }}</td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Waktu (menit)</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $v1 = $scd->time_minutes_1 ?? '-';
                                            $v2 = $scd->time_minutes_2 ?? '-';
                                            @endphp
                                            <td>{{ $v1 }} / {{ $v2 }}</td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Suhu pusat produk setelah keluar (°C)</td>
                                            @foreach ($report->details as $detail)
                                            @php
                                            $v1 = $scd->product_temp_after_exit_1 ?? '-';
                                            $v2 = $scd->product_temp_after_exit_2 ?? '-';
                                            $v3 = $scd->product_temp_after_exit_3 ?? '-';
                                            @endphp
                                            <td>{{ $v1 }} / {{ $v2 }} / {{ $v3 }}</td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Suhu rata-rata pusat produk setelah keluar (°C)</td>
                                            @foreach ($report->details as $detail)
                                            <td>{{ $scd->avg_product_temp_after_exit ?? '-' }}</td>
                                            @endforeach
                                        </tr>



                                        {{-- C. Cooking Loss --}}
                                        <tr class="table-secondary">
                                            <td colspan="{{ 1 + $report->details->count() }}"
                                                class="text-start fw-semibold">
                                                C. Cooking Loss
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Tiap Stick</td>
                                            @foreach ($report->details as $detail)
                                            <td></td> {{-- kolom kosong (judul) --}}
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Kode Batch</td>
                                            @foreach ($report->details as $detail)
                                            <td>
                                                @foreach ($detail->cookingLosses as $loss)
                                                {{ $loss->batch_code ?? '-' }}@if (!$loop->last), @endif
                                                @endforeach
                                            </td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Berat Mentah</td>
                                            @foreach ($report->details as $detail)
                                            <td>
                                                @foreach ($detail->cookingLosses as $loss)
                                                {{ $loss->raw_weight ?? '-' }}@if (!$loop->last), @endif
                                                @endforeach
                                            </td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Berat Matang</td>
                                            @foreach ($report->details as $detail)
                                            <td>
                                                @foreach ($detail->cookingLosses as $loss)
                                                {{ $loss->cooked_weight ?? '-' }}@if (!$loop->last), @endif
                                                @endforeach
                                            </td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>Lose (kg)</td>
                                            @foreach ($report->details as $detail)
                                            <td>
                                                @foreach ($detail->cookingLosses as $loss)
                                                {{ $loss->loss_kg ?? '-' }}@if (!$loop->last), @endif
                                                @endforeach
                                            </td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>%</td>
                                            @foreach ($report->details as $detail)
                                            <td>
                                                @foreach ($detail->cookingLosses as $loss)
                                                {{ $loss->loss_percent ?? '-' }}@if (!$loop->last), @endif
                                                @endforeach
                                            </td>
                                            @endforeach
                                        </tr>



                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="5">No reports found.</td>
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

function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    if (row) {
        row.classList.toggle('d-none');
    }
}
</script>
@endsection