<div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
        <thead>
            <tr>
                <th class="align-middle">Jam</th>
                <th class="align-middle">Produk</th>
                <th class="align-middle">Gramase</th>
                <th class="align-middle">Kode Produksi</th>
                <th class="align-middle">Jenis Kontaminan</th>
                <th class="align-middle">Bukti</th>
                <th class="align-middle">Tahapan Analisis</th>
                <th class="align-middle">Asal Kontaminan</th>
                <th class="align-middle">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $detail)
                <tr>
                    <td>{{ $detail->time }}</td>
                    <td>{{ $detail->product->product_name ?? '-' }}</td>
                    <td>{{ !empty($detail->gramase) ? $detail->gramase : ($detail->product->nett_weight ?? '-') }} g</td>
                    <td>{{ $detail->production_code }}</td>
                    <td>{{ $detail->contaminant_type }}</td>
                    <td>
                        @if ($detail->evidence)
                            <a href="{{ asset('storage/' . $detail->evidence) }}" target="_blank">
                                <img src="{{ asset('storage/' . $detail->evidence) }}" alt="Bukti" width="60">
                            </a>
                        @endif
                    </td>
                    <td>{{ $detail->analysis_stage }}</td>
                    <td>{{ $detail->contaminant_origin }}</td>
                    <td>{{ $detail->notes }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">Tidak ada detail</td></tr>
            @endforelse
        </tbody>
    </table>
</div>