<div class="table-responsive">
    <table class="table table-bordered table-sm text-center align-middle mb-0">
        {{-- Header informasi --}}
        <tr>
            <th class="text-left">Nama Produk</th>
            <td colspan="16" class="text-left">{{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="text-left">Gramase</th>
            <td colspan="16" class="text-left">
                {{ !empty($report->gramase) ? $report->gramase : ($report->product->nett_weight ?? '-') }} g
            </td>
        </tr>
        <tr>
            <th class="text-left">Kode Produksi</th>
            <td colspan="16" class="text-left">{{ $report->production_code ?? '-' }}</td>
        </tr>
        <tr>
            <th class="text-left">Waktu (Start - Stop)</th>
            <td colspan="16" class="text-left">{{ $report->start_time ?? '-' }} - {{ $report->end_time ?? '-' }}</td>
        </tr>

        {{-- Header kolom --}}
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

        {{-- Isi data --}}
        @forelse ($details as $d)
            <tr>
                <td>{{ $d->time }}</td>
                <td>{{ $d->process_step }}</td>

                <td colspan="3" class="p-0">
                    <table class="table table-sm mb-0 table-borderless">
                        @foreach ($d->rawMaterials as $rm)
                            <tr>
                                <td class="text-left">{{ $rm->rawMaterial->material_name ?? '-' }}</td>
                                <td class="text-left">{{ $rm->amount }}</td>
                                <td class="text-left">{{ $rm->sensory }}</td>
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
        @empty
            <tr><td colspan="17">Tidak ada detail</td></tr>
        @endforelse
    </table>
</div>