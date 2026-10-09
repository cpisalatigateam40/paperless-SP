@php
    $specimens = ['fe_1_5mm', 'non_fe_2mm', 'sus_2_5mm'];
    $positions = ['d', 't', 'b'];
@endphp

<div class="table-responsive">
    <table class="table table-sm table-bordered mb-0 text-center">
        <thead>
            <tr>
                <th rowspan="2" class="align-middle">No</th>
                <th rowspan="2" class="align-middle">Waktu Verifikasi</th>
                <th rowspan="2" class="align-middle">Nama Produk</th>
                <th rowspan="2" class="align-middle">Gramase (gr)</th>
                <th rowspan="2" class="align-middle">Kode Produksi</th>
                <th rowspan="2" class="align-middle">No Program</th>
                <th colspan="3" class="align-middle">Fe 1.5 mm</th>
                <th colspan="3" class="align-middle">Non-Fe 2.0 mm</th>
                <th colspan="3" class="align-middle">SUS 2.5 mm</th>
                <th rowspan="2" class="align-middle">Status (OK/NG)</th>
                <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                <th rowspan="2" class="align-middle">Keterangan</th>
            </tr>
            <tr>
                @for ($g = 0; $g < 3; $g++)
                    <th>D</th><th>T</th><th>B</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $detail->time ? \Carbon\Carbon::parse($detail->time)->format('H:i') : '-' }}</td>
                    <td>{{ $detail->product->product_name ?? '-' }}</td>
                    <td>{{ $detail->gramase ?? '-' }}</td>
                    <td>{{ $detail->production_code ?? '-' }}</td>
                    <td>{{ $detail->program_number ?? '-' }}</td>

                    @foreach ($specimens as $specimen)
                        @foreach ($positions as $pos)
                            @php
                                $posDetail = $detail->positions
                                    ->where('specimen', $specimen)
                                    ->where('position', $pos)
                                    ->first();
                            @endphp
                            <td>{{ $posDetail ? ($posDetail->status ? '✓' : '×') : '-' }}</td>
                        @endforeach
                    @endforeach

                    <td>
                        <span class="badge {{ $detail->status ? 'badge-success' : 'badge-danger' }}">
                            {{ $detail->status ? 'OK' : 'NG' }}
                        </span>
                    </td>
                    <td>{{ $detail->corrective_action ?: '-' }}</td>
                    <td>{{ $detail->verification ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="18" class="text-center">Tidak ada detail</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="mt-2 mb-0">Catatan: {{ $report->notes ?: '-' }}</p>
</div>