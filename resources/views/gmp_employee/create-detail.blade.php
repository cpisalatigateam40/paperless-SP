@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('gmp-employee.detail.store', $report->id) }}" method="POST">
                @csrf
                <input type="hidden" name="report_uuid" value="{{ $report->uuid }}">

                <div class="mb-3">
                    <label for="inspection_hour" class="form-label">Jam Inspeksi</label>
                    <input type="time" name="inspection_hour" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="section_name" class="form-label">Nama Area</label>
                    <input type="text" name="section_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="employee_name" class="form-label">Nama Karyawan</label>
                    <input type="text" name="employee_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <input type="text" name="notes" class="form-control" rows="2" required>
                </div>

                <div class="mb-3">
                    <label for="corrective_action" class="form-label">Tindakan Korektif</label>
                    <input type="text" name="corrective_action" class="form-control" rows="2" required>
                </div>

                <div class="mb-3">
                    {{-- <input type="checkbox" name="verification" class="form-check-input" value="1"> --}}
                    <label class="form-check-label">Verifikasi</label>
                    <select name="verification" class="form-control" required>
                        <option value="0">Tidak OK</option>
                        <option value="1">OK</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Simpan Detail</button>
                <a href="{{ route('gmp-employee.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection
