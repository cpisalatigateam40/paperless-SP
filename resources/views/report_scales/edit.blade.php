@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan Verifikasi Timbangan & Thermometer</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('report-scales.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="date">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ date('Y-m-d', strtotime($report->date)) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="shift">Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#scale" role="tab">Timbangan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#thermometer" role="tab">Thermometer</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- Timbangan Tab --}}
                    <div class="tab-pane fade show active" id="scale">
                        @include('report_scales.partials.edit_scale', [
                        'details' => $report->details,
                        'scales' => $scales
                        ])
                    </div>

                    {{-- Thermometer Tab --}}
                    <div class="tab-pane fade" id="thermometer">
                        @include('report_scales.partials.edit_thermometer', [
                        'details' => $report->thermometerDetails,
                        'thermometers' => $thermometers
                        ])
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Update Laporan</button>
                    <a href="{{ route('report-scales.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// TIMBANGAN
document.getElementById('scale-time1')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('input[name^="data"][name$="[time_1]"]').forEach(el => el.value = val);
});

document.getElementById('scale-time2')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('input[name^="data"][name$="[time_2]"]').forEach(el => el.value = val);
});

// THERMOMETER
document.getElementById('thermo-time1')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('input[name^="thermo_data"][name$="[time_1]"]').forEach(el => el.value = val);
});

document.getElementById('thermo-time2')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('input[name^="thermo_data"][name$="[time_2]"]').forEach(el => el.value = val);
});

// Jalankan awal untuk set semua value waktu
document.getElementById('scale-time1')?.dispatchEvent(new Event('change'));
document.getElementById('scale-time2')?.dispatchEvent(new Event('change'));
document.getElementById('thermo-time1')?.dispatchEvent(new Event('change'));
document.getElementById('thermo-time2')?.dispatchEvent(new Event('change'));
</script>

@endsection