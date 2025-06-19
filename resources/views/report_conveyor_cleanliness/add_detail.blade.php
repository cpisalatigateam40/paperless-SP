@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
             <h5>Tambah Detail Inspeksi Conveyor</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <p><strong>Tanggal:</strong> {{ $report->date }}</p>
                <p><strong>Shift:</strong> {{ $report->shift }}</p>
                <p><strong>Area:</strong> {{ $report->area->name ?? '-' }}</p>
                <p><strong>Section:</strong> {{ $report->section->section_name ?? '-' }}</p>
            </div>

            <form action="{{ route('report-conveyor-cleanliness.store-detail', $report->uuid) }}" method="POST">
                @csrf

                {{-- FORM MESIN --}}
                <div class="mb-3 mt-3">
                    <h5 class="mb-3">Form Detail Pemeriksaan</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead>
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
                                        <input type="time" name="machines[{{ $i }}][time]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}">
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

    </div>
</div>
@endsection
