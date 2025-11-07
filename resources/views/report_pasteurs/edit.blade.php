@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Edit Laporan Verifikasi Pasteurisasi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_pasteurs.update', $report->uuid) }}" method="POST" id="pasteur-form"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" value="{{ $report->date }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" value="{{ $report->shift }}" class="form-control">
                    </div>
                </div>

                <h5 class="mt-4">Detail Produk</h5>
                <div id="detail-wrapper">
                    @foreach($report->details as $dIndex => $detail)
                    <div class="border p-3 mb-3 detail-item">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Produk</label>
                                <select name="details[{{ $dIndex }}][product_uuid]"
                                    class="form-control select2-product">
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        {{ $product->uuid == $detail->product_uuid ? 'selected' : '' }}>
                                        {{ $product->product_name }} - {{ $product->nett_weight }} g
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Nomor Program</label>
                                <input type="text" name="details[{{ $dIndex }}][program_number]" class="form-control"
                                    value="{{ $detail->program_number }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Kode Produk</label>
                                <input type="text" name="details[{{ $dIndex }}][product_code]" class="form-control"
                                    value="{{ $detail->product_code }}">
                            </div>
                            <div class="col-md-6">
                                <label>Kemasan (gr)</label>
                                <input type="number" name="details[{{ $dIndex }}][for_packaging_gr]"
                                    class="form-control" value="{{ $detail->for_packaging_gr }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Jumlah Troly</label>
                                <input type="number" name="details[{{ $dIndex }}][trolley_count]" class="form-control"
                                    value="{{ $detail->trolley_count }}">
                            </div>
                            <div class="col-md-6">
                                <label>Suhu Produk (°C)</label>
                                <input type="number" step="0.1" name="details[{{ $dIndex }}][product_temp]"
                                    class="form-control" value="{{ $detail->product_temp }}">
                            </div>
                        </div>

                        {{-- ===================== STEPS ===================== --}}
                        <h6 class="mt-4">Steps</h6>
                        @foreach($detail->steps as $sIndex => $step)
                        <div class="p-2 border mb-4">
                            <strong>{{ $step->step_order }}. {{ $step->step_name }}</strong>

                            <input type="hidden" name="details[{{ $dIndex }}][steps][{{ $sIndex }}][step_name]"
                                value="{{ $step->step_name }}">
                            <input type="hidden" name="details[{{ $dIndex }}][steps][{{ $sIndex }}][step_order]"
                                value="{{ $step->step_order }}">
                            <input type="hidden" name="details[{{ $dIndex }}][steps][{{ $sIndex }}][step_type]"
                                value="{{ $step->step_type }}">

                            @if($step->step_type === 'standard')
                            <div class="row mb-4 mt-2">
                                <div class="col-md-3">
                                    <label>Jam Mulai</label>
                                    <input type="time"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][start_time]"
                                        class="form-control" value="{{ $step->standardStep->start_time ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Jam Selesai</label>
                                    <input type="time"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][end_time]"
                                        class="form-control" value="{{ $step->standardStep->end_time ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Suhu Air (°C)</label>
                                    <input type="number" step="0.1"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][water_temp]"
                                        class="form-control" value="{{ $step->standardStep->water_temp ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Tekanan (Bar)</label>
                                    <input type="number" step="0.01"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][pressure]"
                                        class="form-control" value="{{ $step->standardStep->pressure ?? '' }}">
                                </div>
                            </div>
                            @elseif($step->step_type === 'drainage')
                            <div class="row mb-4 mt-2">
                                <div class="col-md-6">
                                    <label>Jam Mulai</label>
                                    <input type="time"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][start_time]"
                                        class="form-control" value="{{ $step->drainageStep->start_time ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label>Jam Selesai</label>
                                    <input type="time"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][end_time]"
                                        class="form-control" value="{{ $step->drainageStep->end_time ?? '' }}">
                                </div>
                            </div>
                            @elseif($step->step_type === 'finish')
                            <div class="row mb-4 mt-2">
                                <div class="col-md-6">
                                    <label>Suhu Inti Produk (°C)</label>
                                    <input type="number" step="0.1"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][product_core_temp]"
                                        class="form-control" value="{{ $step->finishStep->product_core_temp ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label>Sortasi</label>
                                    <input type="text"
                                        name="details[{{ $dIndex }}][steps][{{ $sIndex }}][data][sortation]"
                                        class="form-control" value="{{ $step->finishStep->sortation ?? '' }}">
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach

                        {{-- Paraf --}}
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>Paraf QC</label>
                                @if($detail->qc_paraf)
                                <p><img src="{{ asset('storage/'.$detail->qc_paraf) }}" height="100"></p>
                                @endif
                                <canvas id="qc-canvas-{{ $dIndex }}" class="border" width="300" height="150"
                                    data-input="qc_paraf_input_{{ $dIndex }}"></canvas>
                                <input type="hidden" name="details[{{ $dIndex }}][qc_paraf]"
                                    id="qc_paraf_input_{{ $dIndex }}">
                            </div>
                            <div class="col-md-6">
                                <label>Paraf Produksi</label>
                                @if($detail->production_paraf)
                                <p><img src="{{ asset('storage/'.$detail->production_paraf) }}" height="100"></p>
                                @endif
                                <canvas id="prod-canvas-{{ $dIndex }}" class="border" width="300" height="150"
                                    data-input="production_paraf_input_{{ $dIndex }}"></canvas>
                                <input type="hidden" name="details[{{ $dIndex }}][production_paraf]"
                                    id="production_paraf_input_{{ $dIndex }}">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <label>Problem</label>
                        <textarea name="problem" class="form-control" rows="2">{{ $report->problem }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label>Corrective Action</label>
                        <textarea name="corrective_action" class="form-control"
                            rows="2">{{ $report->corrective_action }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-4">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const signaturePads = [];
    document.querySelectorAll("canvas").forEach((canvas) => {
        const pad = new SignaturePad(canvas, {
            backgroundColor: "rgba(255,255,255,0)",
            penColor: "rgb(0,0,0)"
        });
        signaturePads.push({
            pad,
            canvas
        });
    });

    document.querySelector("form#pasteur-form").addEventListener("submit", function() {
        signaturePads.forEach(({
            pad,
            canvas
        }) => {
            const inputId = canvas.dataset.input;
            const input = document.getElementById(inputId);
            if (pad && !pad.isEmpty()) {
                input.value = pad.toDataURL("image/png");
            }
        });
    });
});
</script>
@endsection