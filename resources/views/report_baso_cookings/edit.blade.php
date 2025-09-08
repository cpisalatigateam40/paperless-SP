@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_baso_cookings.update', $report->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header">
                <h5>Update Suhu Akhir - {{ $report->date }} (Shift {{ $report->shift }})</h5>
                <small class="text-muted">Hanya jam akhir & suhu baso akhir yang bisa diisi/disunting.</small>
            </div>

            <div class="card-body">
                @foreach($report->details as $detail)
                <div class="mb-4 border rounded p-3">
                    <h6 class="fw-bold">Kode Produksi: {{ $detail->production_code }}</h6>

                    @foreach($detail->temperatures as $temp)
                    @if($temp->time_type === 'akhir')
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-2">
                            <label class="small">Jam Akhir</label>
                            <input type="time"
                                name="details[{{ $detail->uuid }}][temperatures][{{ $temp->uuid }}][time_recorded]"
                                class="form-control" value="{{ $temp->time_recorded }}">
                        </div>

                        @for($i = 1; $i <= 5; $i++) <div class="col-md-1">
                            <label class="small">Suhu {{ $i }}</label>
                            <input type="number" step="0.01"
                                name="details[{{ $detail->uuid }}][temperatures][{{ $temp->uuid }}][baso_temp_{{ $i }}]"
                                class="form-control baso-temp-input" value="{{ $temp['baso_temp_'.$i] }}">
                    </div>
                    @endfor

                    <div class="col-md-2">
                        <label class="small">Rata-rata</label>
                        <input type="number" step="0.01"
                            name="details[{{ $detail->uuid }}][temperatures][{{ $temp->uuid }}][avg_baso_temp]"
                            class="form-control avg-baso-temp" value="{{ $temp->avg_baso_temp }}" readonly>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endforeach
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('report_baso_cookings.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        </div>
</div>
</form>
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function() {
    function calculateAverage(row) {
        let inputs = row.querySelectorAll(".baso-temp-input");
        let sum = 0,
            count = 0;

        inputs.forEach(input => {
            let val = parseFloat(input.value);
            if (!isNaN(val)) {
                sum += val;
                count++;
            }
        });

        let avgInput = row.querySelector(".avg-baso-temp");
        if (avgInput) {
            avgInput.value = count > 0 ? (sum / count).toFixed(1) : "";
        }
    }

    document.querySelectorAll(".row.mb-2").forEach(row => {
        row.querySelectorAll(".baso-temp-input").forEach(input => {
            input.addEventListener("input", () => calculateAverage(row));
        });
    });
});
</script>
@endsection