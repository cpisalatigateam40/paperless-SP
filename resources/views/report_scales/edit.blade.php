{{-- @extends('layouts.app')

@section('content')
<div class="container">
    <h5>Edit Laporan Pemeriksaan Timbangan</h5>

    <form action="{{ route('report-scales.update', $report->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="date">Tanggal</label>
            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                   value="{{ old('date', $report->date?->format('Y-m-d')) }}" required>
            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="shift">Shift</label>
            <input type="text" name="shift" class="form-control @error('shift') is-invalid @enderror"
                   value="{{ old('shift', $report->shift) }}" required>
            @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button class="btn btn-primary">Perbarui</button>
        <a href="{{ route('report-scales.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan Pemeriksaan Timbangan & Thermometer</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('report-scales.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="date">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d', strtotime($report->date)) }}" required>
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
@endsection

