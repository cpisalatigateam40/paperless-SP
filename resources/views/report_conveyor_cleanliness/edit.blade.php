@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Update Laporan Pemeriksaan Kebersihan Conveyor</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report-conveyor-cleanliness.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div style="margin-bottom: 3rem;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', $report->date) }}">
                        </div>
                        <div class="col-md-4">
                            <label>Shift</label>
                            <input type="text" name="shift" class="form-control" value="{{ old('shift', $report->shift) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label>Section</label>
                            <select name="section_uuid" class="form-select form-control">
                                <option value="">-- Pilih Section --</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->uuid }}" {{ $section->uuid == $report->section_uuid ? 'selected' : '' }}>{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- EDIT DETAIL (semua grup mesin) --}}
                <h5 class="mt-4">Update Detail Inspeksi</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pukul</th>
                                <th>Nama Mesin</th>
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
                            @php $grouped = $report->machines->chunk(4); @endphp
                            @foreach ($grouped as $groupIndex => $group)
                                @php $innerIndex = 0; @endphp
                                @foreach ($group as $machine)
                                <tr>
                                    <td>{{ $innerIndex === 0 ? $groupIndex + 1 : '' }}</td>
                                    <td>
                                        <input type="time" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][time]" class="form-control" value="{{ old('machines.' . $groupIndex . '.' . $innerIndex . '.time', $machine->time ? \Carbon\Carbon::parse($machine->time)->format('H:i') : '') }}">
                                    </td>
                                    <td class="text-start">
                                        <input type="hidden" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][uuid]" value="{{ $machine->uuid }}">
                                        <input type="hidden" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][machine_name]" value="{{ $machine->machine_name }}">
                                        <p class="mb-0">{{ $machine->machine_name }}</p>
                                    </td>
                                    <td><input type="radio" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][status]" value="bersih" {{ $machine->status === 'bersih' ? 'checked' : '' }}></td>
                                    <td><input type="radio" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][status]" value="kotor" {{ $machine->status === 'kotor' ? 'checked' : '' }}></td>
                                    <td><input type="text" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][notes]" class="form-control" value="{{ $machine->notes }}"></td>
                                    <td><input type="text" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][corrective_action]" class="form-control" value="{{ $machine->corrective_action }}"></td>
                                    <td><input type="text" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][verification]" class="form-control" value="{{ $machine->verification }}"></td>
                                    <td><input type="checkbox" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][qc_check]" value="1" {{ $machine->qc_check ? 'checked' : '' }}></td>
                                    <td><input type="checkbox" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][kr_check]" value="1" {{ $machine->kr_check ? 'checked' : '' }}></td>
                                </tr>
                                @php $innerIndex++; @endphp
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- SUBMIT --}}
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">Update Laporan</button>
                    <a href="{{ route('report-conveyor-cleanliness.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection