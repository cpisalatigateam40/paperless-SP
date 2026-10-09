<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle text-center mb-0">
        {{-- Header informasi --}}
        <tr>
            <th class="text-left">Nama Produk</th>
            <td colspan="20" class="text-left">{{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="text-left">Formula</th>
            <td colspan="20" class="text-left">{{ $report->formula->formula_name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="text-left">Gramase</th>
            <td colspan="20" class="text-left">
                {{ !empty($report->gramase) ? $report->gramase : ($report->product->nett_weight ?? '-') }} g
            </td>
        </tr>
        <tr>
            <th class="text-left">Kode Produksi</th>
            <td colspan="20" class="text-left">{{ $report->production_code ?? '-' }}</td>
        </tr>
        <tr>
            <th class="text-left">Waktu (Start - Stop)</th>
            <td colspan="20" class="text-left">{{ $report->start_time ?? '-' }} - {{ $report->end_time ?? '-' }}</td>
        </tr>
        <tr>
            <th class="text-left">Nomor Mesin</th>
            <td colspan="20" class="text-left">{{ $details->pluck('no_mesin')->filter()->implode(', ') ?: '-' }}</td>
        </tr>
        <tr>
            <th class="text-left">Catatan &amp; Dokumentasi</th>
            <td colspan="20" class="text-left">{{ $report->documentation_notes ?? '-' }}</td>
        </tr>

        {{-- Header kolom --}}
        <tr>
            <th rowspan="2">Durasi Proses</th>
            <th colspan="5">Bahan Baku</th>
            <th colspan="7">Parameter Pemasakan</th>
            <th colspan="5">Produk Organoleptik</th>
            <th rowspan="2">Status Produk</th>
            <th rowspan="2">Tindakan Perbaikan</th>
            <th rowspan="2">Catatan</th>
        </tr>
        <tr>
            <th>Jenis Bahan</th>
            <th>Jumlah (Kg)</th>
            <th>Status</th>
            <th>Tindakan Koreksi</th>
            <th>Keterangan</th>

            <th>Mixing Paddle On</th>
            <th>Mixing Paddle Off</th>
            <th>Brix (%)</th>
            <th>Salinity (%)</th>
            <th>Pressure (Bar)</th>
            <th>Target Temp (°C)</th>
            <th>Actual Temp (°C)</th>

            <th>Kenampakan</th>
            <th>Warna</th>
            <th>Aroma</th>
            <th>Rasa</th>
            <th>Tekstur</th>
        </tr>

        {{-- Isi data --}}
        @forelse ($details as $d)
            <tr>
                <td>{{ $d->process_step }}</td>

                <td colspan="5" class="p-0">
                    <table class="table table-sm mb-0 table-borderless">
                        @foreach ($d->rawMaterials as $rm)
                            <tr>
                                <td class="text-left">
                                    @if ($rm->material_type === 'premix')
                                        {{ $rm->premix->name ?? '-' }} (Premix)
                                    @else
                                        {{ $rm->rawMaterial->material_name ?? '-' }}
                                    @endif
                                </td>
                                <td class="text-left">{{ $rm->amount }}</td>
                                <td class="text-left">{{ $rm->sensory }}</td>
                                <td class="text-left">{{ $rm->corrective_action ?? '-' }}</td>
                                <td class="text-left">{{ $rm->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>

                <td>{{ $d->mixing_paddle_on ? '✔' : '-' }}</td>
                <td>{{ $d->mixing_paddle_off ? '✔' : '-' }}</td>
                <td>{{ $d->brix }}</td>
                <td>{{ $d->salinity }}</td>
                <td>{{ $d->pressure }}</td>
                <td>{{ $d->target_temperature }}</td>
                <td>{{ $d->actual_temperature }}</td>

                <td>{{ $d->appearance }}</td>
                <td>{{ $d->color }}</td>
                <td>{{ $d->aroma }}</td>
                <td>{{ $d->taste }}</td>
                <td>{{ $d->texture }}</td>

                <td>{{ $d->product_status }}</td>
                <td>{{ $d->corrective_action }}</td>
                <td>{{ $d->notes }}</td>
            </tr>
        @empty
            <tr><td colspan="21">Tidak ada detail</td></tr>
        @endforelse
    </table>
</div>