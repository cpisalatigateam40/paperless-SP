@php
    $resultLabel = ['detected' => 'Terdeteksi', 'not_detected' => 'Tidak Terdeteksi'];
    $causeLabels = \App\Models\ReportMdRejectDetail::CAUSES;
    $dispLabels  = \App\Models\ReportMdRejectDetail::DISPOSITIONS;
@endphp

<div class="p-2">
    {{-- A. Verifikasi Alat --}}
    <h6 class="font-weight-bold">A. Verifikasi Alat (Test Piece)</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered text-center mb-0" style="max-width: 600px;">
            <thead>
                <tr>
                    <th>Test Piece</th>
                    <th>Hasil</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\ReportMdReject::TEST_PIECES as $piece)
                    @php $v = $report->verifications->firstWhere('test_piece', $piece); @endphp
                    <tr>
                        <td>{{ $piece }}</td>
                        <td>{{ $resultLabel[$v->result ?? ''] ?? '-' }}</td>
                        <td>{{ $v->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- B. Data Produk --}}
    <h6 class="font-weight-bold">B. Data Produk</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered text-center mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jam</th>
                    <th>Nama Produk</th>
                    <th>Kode Produksi</th>
                    <th>Jumlah Reject (pcs/pack)</th>
                    <th>Hasil Pengecekan Ulang</th>
                    <th>Kontaminasi Ditemukan</th>
                    <th>Dugaan Penyebab</th>
                    <th>Tindakan Perbaikan / Korektif</th>
                    <th>Disposisi Produk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report->details as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ substr($detail->time, 0, 5) }}</td>
                    <td class="text-left">{{ optional($detail->product)->product_name ?? '-' }}</td>
                    <td>{{ $detail->production_code }}</td>
                    <td>{{ $detail->reject_qty }}</td>
                    <td>{{ $resultLabel[$detail->recheck_result] ?? '-' }}</td>
                    <td>{{ is_null($detail->contamination_found) ? '-' : ($detail->contamination_found ? 'Ada' : 'Tidak') }}</td>
                    <td class="text-left">
                        @forelse($detail->suspected_causes ?? [] as $key)
                            <div>
                                • {{ $causeLabels[$key] ?? $key }}@if($key === 'other' && $detail->suspected_cause_other): {{ $detail->suspected_cause_other }}@endif
                            </div>
                        @empty
                            -
                        @endforelse
                    </td>
                    <td class="text-left">{{ $detail->corrective_action ?: '-' }}</td>
                    <td>{{ $dispLabels[$detail->disposition] ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-muted">Tidak ada produk reject.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>