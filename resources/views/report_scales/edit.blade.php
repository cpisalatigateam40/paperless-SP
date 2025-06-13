@extends('layouts.app')

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
@endsection
