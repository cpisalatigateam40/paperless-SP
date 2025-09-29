@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_tofu_verifs.store') }}" method="POST">
        @csrf

        {{-- HEADER --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Tambah Laporan Verifikasi Produk Tofu</h5>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control">
                </div>
            </div>
        </div>

        {{-- PRODUK ACCORDION --}}
        <!-- <div class="accordion" id="produkAccordion">
            @for ($i = 0; $i < 4; $i++) <div class="accordion-item mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-primary text-white" type="button"
                        data-bs-toggle="collapse" data-bs-target="#produk{{ $i }}">
                        Produk #{{ $i + 1 }}
                    </button>
                </h2>
                <div id="produk{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#produkAccordion">

                </div>
        </div>
        @endfor -->

        <div class="accordion-body card shadow">
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label>Kode Produksi</label>
                    <input type="text" name="products[{{ $i }}][production_code]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Expired Date</label>
                    <input type="date" name="products[{{ $i }}][expired_date]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Jumlah Sampel (pcs)</label>
                    <input type="number" name="products[{{ $i }}][sample_amount]" class="form-control">
                </div>
            </div>

            <hr class="my-3">
            <h6 style="margin-left: 2rem;"><strong>Hasil Pemeriksaan Berat</strong></h6>
            @php
            $weights = ['under' => 'Under (< 11gr)', 'standard'=> 'Standard (11 - 13gr)', 'over' => 'Over (>
                13gr)'];
                @endphp
                @foreach ($weights as $key => $label)
                <div class="row mb-2 card-body">
                    <div class="col-md-4">
                        <label>{{ $label }} - Turus</label>
                        <input type="number" name="products[{{ $i }}][weight_verifs][{{ $key }}][turus]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - Jumlah</label>
                        <input type="number" name="products[{{ $i }}][weight_verifs][{{ $key }}][total]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - %</label>
                        <input type="number" step="0.01"
                            name="products[{{ $i }}][weight_verifs][{{ $key }}][percentage]" class="form-control">
                    </div>
                </div>
                @endforeach

                <hr class="my-3">
                <h6 style="margin-left: 2rem;"><strong>Hasil Pemeriksaan Defect</strong></h6>
                @php
                $defects = ['hole' => 'Berlubang', 'stain' => 'Noda', 'asymmetry' => 'Tidak Bulat Simetris',
                'other' => 'Lain-lain', 'good' => 'Produk Bagus', 'note' => 'Keterangan'];
                @endphp
                @foreach ($defects as $key => $label)
                <div class="row mb-2 card-body">
                    <div class="col-md-4">
                        <label>{{ $label }} - Turus</label>
                        <input type="number" name="products[{{ $i }}][defect_verifs][{{ $key }}][turus]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - Jumlah</label>
                        <input type="number" name="products[{{ $i }}][defect_verifs][{{ $key }}][total]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - %</label>
                        <input type="number" step="0.01"
                            name="products[{{ $i }}][defect_verifs][{{ $key }}][percentage]" class="form-control">
                    </div>
                </div>
                @endforeach
        </div>
</div>

<div class="mt-4 text-end">
    <button type="submit" class="btn btn-success">Simpan Laporan</button>
</div>
</form>
</div>
@endsection