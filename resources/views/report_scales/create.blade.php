@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Buat Laporan Verifikasi Timbangan & Thermometer</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('report-scales.store') }}" method="POST">
                @csrf

                {{-- Data Header --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="date">Tanggal</label>
                        <input type="date" name="date" value="{{ \Carbon\Carbon::today()->toDateString() }}"
                            class="form-control @error('date') is-invalid @enderror" value="{{ old('date') }}" required>
                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="shift">Shift</label>
                        <input type="text" name="shift" class="form-control @error('shift') is-invalid @enderror" value="{{ session('shift_number') }}-{{ session('shift_group') }}"
                            required>
                        @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="scale-tab" data-bs-toggle="tab" href="#scale"
                            role="tab">Timbangan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="thermometer-tab" data-bs-toggle="tab" href="#thermometer"
                            role="tab">Thermometer</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="reportTabsContent">
                    <div class="tab-pane fade show active" id="scale" role="tabpanel">
                        @include('report_scales.partials.scale', ['scales' => $scales])
                    </div>
                    <div class="tab-pane fade" id="thermometer" role="tabpanel">
                        @include('report_scales.partials.thermometer', ['thermometers' => $thermometers])
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-success">Simpan Laporan</button>
                    <a href="{{ route('report-scales.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@section('script')
<script>
let rowCount = 1;

// Tambah Baris Baru
document.getElementById('add-row').addEventListener('click', function() {
    const tbody = document.getElementById('detail-body');
    const time1 = document.getElementById('time1').value;
    const time2 = document.getElementById('time2').value;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center">${rowCount + 1}</td>
        <td>
            <select name="data[${rowCount}][scale_uuid]" class="form-select form-control" required>
                <option value="">-- Pilih Timbangan --</option>
                @foreach($scales as $scale)
                    <option value="{{ $scale->uuid }}">{{ $scale->type }} - {{ $scale->code }}</option>
                @endforeach
            </select>
            <input type="hidden" name="data[${rowCount}][time_1]" class="time1-input" value="${time1}">
            <input type="hidden" name="data[${rowCount}][time_2]" class="time2-input" value="${time2}">
        </td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p1_1000]" class="form-control" required></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p1_5000]" class="form-control" required></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p1_10000]" class="form-control" required></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p2_1000]" class="form-control" required></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p2_5000]" class="form-control" required></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p2_10000]" class="form-control" required></td>
        <td>
            <input type="text" name="data[${rowCount}][status]" class="form-control" required>
        </td>

    `;

    tbody.appendChild(row);
    rowCount++;
});

// Sync waktu ke seluruh baris
document.getElementById('time1').addEventListener('input', function() {
    document.querySelectorAll('.time1-input').forEach(input => input.value = this.value);
});

document.getElementById('time2').addEventListener('input', function() {
    document.querySelectorAll('.time2-input').forEach(input => input.value = this.value);
});
</script>

@endsection