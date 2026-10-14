<div class="mt-1">
    @foreach ($details as $detail)
    <table class="table table-bordered table-sm mb-2">
        <tr><th colspan="2">NAMA PRODUK</th><td colspan="5">{{ $detail->product->product_name ?? '-' }}</td></tr>
        <tr><th colspan="2">GRAMASE</th><td colspan="5">{{ number_format($detail->gramase, 0) }} g</td></tr>
        <tr><th colspan="2">KODE PRODUKSI</th><td colspan="5">{{ $detail->production_code ?? '-' }}</td></tr>
        <tr><th colspan="2">NOMOR FORMULA</th><td colspan="5">{{ $detail->formula->formula_name ?? '-' }}</td></tr>
        <tr><th colspan="2">WAKTU MIXING</th><td colspan="5">{{ $detail->mixing_time ?? '-' }}</td></tr>
        <tr><th colspan="2">NAMA MESIN MIXER/CHOPPER</th><td colspan="5">{{ $detail->machine_name ?? '-' }}</td></tr>

        {{-- A. BAHAN BAKU --}}
        <tr class="table-secondary fw-bold"><td colspan="7">A. BAHAN BAKU</td></tr>
        <tr>
            <th>No</th><th>Bahan</th><th>Berat (kg)</th><th>Sensorik</th>
            <th>Kode Produksi</th><th>Suhu (℃)</th><th>Keterangan</th>
        </tr>
        @php $i = 1; @endphp
        @foreach ($detail->items->filter(fn($item) => $item->material_type
            ? $item->material_type === 'raw_material'
            : $item->formulation?->raw_material_uuid) as $item)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $item->material_name ?? $item->formulation?->rawMaterial?->material_name ?? '-' }}</td>
            <td>{{ $item->actual_weight }}</td>
            <td>{{ $item->sensory }}</td>
            <td>{{ $item->prod_code }}</td>
            <td>{{ $item->temperature }}</td>
            <td>{{ $item->keterangan }}</td>
        </tr>
        @endforeach

        {{-- B. PREMIX --}}
        <tr class="table-secondary fw-bold"><td colspan="7">B. PREMIX / BAHAN TAMBAHAN</td></tr>
        <tr>
            <th>No</th><th>Bahan</th><th>Berat (kg)</th><th>Sensorik</th>
            <th>Kode Produksi</th><th>Suhu (℃)</th><th>Keterangan</th>
        </tr>
        @php $j = 1; @endphp
        @foreach ($detail->items->filter(fn($item) => $item->material_type
            ? $item->material_type === 'premix'
            : ($item->formulation && !$item->formulation->raw_material_uuid)) as $item)
        <tr>
            <td>{{ $j++ }}</td>
            <td>{{ $item->material_name ?? $item->formulation?->premix?->name ?? '-' }}</td>
            <td>{{ $item->actual_weight }}</td>
            <td>{{ $item->sensory }}</td>
            <td>{{ $item->prod_code }}</td>
            <td>{{ $item->temperature }}</td>
            <td>{{ $item->keterangan }}</td>
        </tr>
        @endforeach

        <tr><th colspan="2">Hasil Penggilingan</th><td colspan="5">{{ $detail->hasil_penggilingan ?? '-' }}</td></tr>
        <tr><th colspan="2">Hasil Pencampuran</th><td colspan="5">{{ $detail->hasil_pencampuran ?? '-' }}</td></tr>
        <tr><th colspan="2">REWORK (kg/%)</th><td colspan="5">{{ $detail->rework_kg ?? '-' }} / {{ $detail->rework_percent ?? '-' }}</td></tr>
        <tr><th colspan="2">PRODUK REWORK</th><td colspan="5">{{ $detail->reworkProduct->product_name ?? '-' }}</td></tr>
        <tr><th colspan="2">TOTAL BAHAN (kg)</th><td colspan="5">{{ $detail->total_material ?? '-' }}</td></tr>
        <tr><th colspan="2">Catatan After Rework</th><td colspan="5">{{ $detail->notes ?? '-' }}</td></tr>

        {{-- C. EMULSIFYING --}}
        <tr class="table-secondary fw-bold"><td colspan="7">C. EMULSIFYING</td></tr>
        <tr><th colspan="2">Standar suhu adonan (℃)</th><td colspan="5">{{ $detail->emulsifying->standard_mixture_temp ?? '14 ± 2' }}</td></tr>
        <tr>
            <th colspan="2">Aktual suhu adonan (℃)</th>
            <td colspan="5">
                {{ $detail->emulsifying->actual_mixture_temp_1 ?? '-' }} /
                {{ $detail->emulsifying->actual_mixture_temp_2 ?? '-' }} /
                {{ $detail->emulsifying->actual_mixture_temp_3 ?? '-' }}
            </td>
        </tr>
        <tr><th colspan="2">Rata-rata suhu adonan (℃)</th><td colspan="5">{{ $detail->emulsifying->average_mixture_temp ?? '-' }}</td></tr>

        {{-- D. SENSORIK --}}
        <tr class="table-secondary fw-bold"><td colspan="7">D. SENSORIK</td></tr>
        <tr><th colspan="2">Homogenitas</th><td colspan="5">{{ $detail->sensoric->homogeneous ?? '-' }}</td></tr>
        <tr><th colspan="2">Kekentalan</th><td colspan="5">{{ $detail->sensoric->stiffness ?? '-' }}</td></tr>
        <tr><th colspan="2">Aroma</th><td colspan="5">{{ $detail->sensoric->aroma ?? '-' }}</td></tr>
        <tr><th colspan="2">Benda Asing</th><td colspan="5">{{ $detail->sensoric->foreign_object ?? '-' }}</td></tr>

        {{-- E. TUMBLING --}}
        <tr class="table-secondary fw-bold"><td colspan="7">E. TUMBLING</td></tr>
        <tr><th colspan="2">Proses Tumbling</th><td colspan="5">{{ $detail->tumbling->tumbling_process ?? '-' }}</td></tr>
        <tr><th colspan="2">Lama Proses (Menit)</th><td colspan="5">{{ $detail->tumbling->process_duration ?? '-' }}</td></tr>
        <tr><th colspan="2">Suhu Akhir Tumbling (°C)</th><td colspan="5">{{ $detail->tumbling->final_temperature ?? '-' }}</td></tr>

        {{-- F. AGING --}}
        <tr class="table-secondary fw-bold"><td colspan="7">F. AGING</td></tr>
        <tr><th colspan="2">Proses Aging</th><td colspan="5">{{ $detail->aging->aging_process ?? '-' }}</td></tr>
        <tr><th colspan="2">Hasil Stuffing</th><td colspan="5">{{ $detail->aging->stuffing_result ?? '-' }}</td></tr>
    </table>
    @endforeach

    <p class="mb-0">Catatan: {{ $report->notes ?? '-' }}</p>
</div>