@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Tambah Detail ke Report Tanggal {{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}
            </h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_md_products.store-detail', $report->uuid) }}">
                @csrf

                {{-- DETAIL --}}
                <div class="mb-3">
                    <label>Waktu Pengecekan</label>
                    <input type="time" name="details[0][time]" class="form-control"
                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                </div>
                <div class=" mb-3">
                    <label>Nama Produk</label>
                    <select name="details[0][product_uuid]" class="form-control" onchange="updateBestBefore(this, 0)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}"
                            data-created-at="{{ $product->created_at }}">
                            {{ $product->product_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="details[0][production_code]" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Best Before</label>
                    <input type="date" name="details[0][best_before]" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label>Nomor Program</label>
                    <input type="text" name="details[0][program_number]" class="form-control">
                </div>

                <h6 class="mt-4">Hasil Pemeriksaan Verifikasi Specimen</h6>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Specimen</th>
                            <th>Depan (D)</th>
                            <th>Tengah (T)</th>
                            <th>Belakang (B)</th>
                            <th>Dalam Tumpukan (DL)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $specimens = ['fe_1_5mm' => 'Fe 1.5 mm', 'non_fe_2mm' => 'Non Fe 2 mm', 'sus_2_5mm' => 'SUS 2.5
                        mm'];
                        $positions = ['d', 't', 'b', 'dl'];
                        $posIdx = 0;
                        @endphp
                        @foreach ($specimens as $specimenKey => $specimenName)
                        <tr>
                            <td>{{ $specimenName }}</td>
                            @foreach ($positions as $posKey)
                            <td>
                                <input type="hidden" name="details[0][positions][{{ $posIdx }}][specimen]"
                                    value="{{ $specimenKey }}">
                                <input type="hidden" name="details[0][positions][{{ $posIdx }}][position]"
                                    value="{{ $posKey }}">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                        name="details[0][positions][{{ $posIdx }}][status]" value="1" checked>
                                    <label class="form-check-label">OK</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                        name="details[0][positions][{{ $posIdx }}][status]" value="0">
                                    <label class="form-check-label">Tidak OK</label>
                                </div>
                            </td>
                            @php $posIdx++; @endphp
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row">
                    <div class="mb-3 mt-3 col-md-6">
                        <label>Tindakan Perbaikan</label>
                        <input type="text" name="details[0][corrective_action]" class="form-control">
                    </div>
                    <div class="mb-3 mt-3 col-md-6">
                        <label>Verifikasi Setelah Perbaikan</label>
                        <select name="details[0][verification]" class="form-control">
                            <option value="">-- Pilih Verifikasi --</option>
                            <option value="0">Tidak</option>
                            <option value="1">Ya</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Simpan Detail</button>
                <a href="{{ route('report_md_products.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function updateBestBefore(select, index) {
    let option = select.options[select.selectedIndex];
    let shelfLife = option.getAttribute('data-shelf-life');
    let createdAt = option.getAttribute('data-created-at');
    if (shelfLife && createdAt) {
        let createdDate = new Date(createdAt);
        createdDate.setMonth(createdDate.getMonth() + parseInt(shelfLife));

        let year = createdDate.getFullYear();
        let month = String(createdDate.getMonth() + 1).padStart(2, '0');
        let day = String(createdDate.getDate()).padStart(2, '0');

        let bestBeforeStr = `${year}-${month}-${day}`;
        document.querySelector(`input[name="details[${index}][best_before]"]`).value = bestBeforeStr;
    }
}
</script>
@endsection