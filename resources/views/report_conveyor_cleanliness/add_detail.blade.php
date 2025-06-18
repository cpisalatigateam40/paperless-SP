@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Tambah Detail Inspeksi Conveyor</h4>

    <div class="mb-3">
        <p><strong>Tanggal:</strong> {{ $report->date }}</p>
        <p><strong>Shift:</strong> {{ $report->shift }}</p>
        <p><strong>Area:</strong> {{ $report->area->name ?? '-' }}</p>
        <p><strong>Section:</strong> {{ $report->section->section_name ?? '-' }}</p>
    </div>

    <form action="{{ route('report-conveyor-cleanliness.store-detail', $report->uuid) }}" method="POST">
        @csrf

        {{-- FORM MESIN --}}
        <div class="card mb-3">
            <div class="card-header">Form Detail Pemeriksaan</div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Pukul</th>
                            <th>Area Conveyor Mesin</th>
                            <th>Bersih</th>
                            <th>Kotor</th>
                            <th>Keterangan</th>
                            <th>Tindakan Koreksi</th>
                            <th>Verifikasi</th>
                            <th>QC</th>
                            <th>KR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mesins as $i => $name)
                        <tr>
                            <td>{{ $i === 0 ? 1 : '' }}</td>
                            <td>
                                @if ($i === 0)
                                    <input type="time" name="machines[{{ $i }}][time]" class="form-control">
                                @else
                                    <input type="hidden" name="machines[{{ $i }}][time]" value="">
                                @endif
                            </td>
                            <td class="text-start">
                                <p class="mb-0">{{ $name }}</p>
                                <input type="hidden" name="machines[{{ $i }}][machine_name]" value="{{ $name }}">
                            </td>
                            <td>
                                <input type="radio" name="machines[{{ $i }}][status]" value="bersih">
                            </td>
                            <td>
                                <input type="radio" name="machines[{{ $i }}][status]" value="kotor">
                            </td>
                            <td>
                                <input type="text" name="machines[{{ $i }}][notes]" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="machines[{{ $i }}][corrective_action]" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="machines[{{ $i }}][verification]" class="form-control">
                            </td>
                            <td>
                                <input type="checkbox" name="machines[{{ $i }}][qc_check]" value="1">
                            </td>
                            <td>
                                <input type="checkbox" name="machines[{{ $i }}][kr_check]" value="1">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Simpan Detail</button>
            <a href="{{ route('report-conveyor-cleanliness.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
