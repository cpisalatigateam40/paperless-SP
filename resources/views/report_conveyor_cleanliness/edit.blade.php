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
                <div class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ old('date', $report->date) }}">
                        </div>
                        <div class="col-md-4">
                            <label>Shift</label>
                            <input type="text" name="shift" class="form-control"
                                value="{{ old('shift', $report->shift) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label>Section</label>
                            <select name="section_uuid" class="form-select form-control">
                                <option value="">-- Pilih Section --</option>
                                @foreach ($sections as $section)
                                <option value="{{ $section->uuid }}"
                                    {{ $section->uuid == $report->section_uuid ? 'selected' : '' }}>
                                    {{ $section->section_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- DETAIL MESIN --}}
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
                                    <input type="time" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][time]"
                                        value="{{ old('machines.' . $groupIndex . '.' . $innerIndex . '.time', \Carbon\Carbon::parse($machine->time)->format('H:i')) }}"
                                        class="form-control">
                                </td>
                                <td class="text-start">
                                    <input type="hidden" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][uuid]"
                                        value="{{ $machine->uuid }}">
                                    <input type="hidden"
                                        name="machines[{{ $groupIndex }}][{{ $innerIndex }}][machine_name]"
                                        value="{{ $machine->machine_name }}">
                                    {{ $machine->machine_name }}
                                </td>
                                <td><input type="radio" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][status]"
                                        value="bersih" {{ $machine->status === 'bersih' ? 'checked' : '' }}></td>
                                <td><input type="radio" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][status]"
                                        value="kotor" {{ $machine->status === 'kotor' ? 'checked' : '' }}></td>
                                <td><input type="text" name="machines[{{ $groupIndex }}][{{ $innerIndex }}][notes]"
                                        value="{{ $machine->notes }}" class="form-control"></td>
                                <td><input type="text"
                                        name="machines[{{ $groupIndex }}][{{ $innerIndex }}][corrective_action]"
                                        value="{{ $machine->corrective_action }}" class="form-control"></td>
                                <td>
                                    <select name="machines[{{ $groupIndex }}][{{ $innerIndex }}][verification]"
                                        class="form-select form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" {{ $machine->verification == '1' ? 'selected' : '' }}>OK
                                        </option>
                                        <option value="0" {{ $machine->verification == '0' ? 'selected' : '' }}>Tidak OK
                                        </option>
                                    </select>
                                </td>
                                <td><input type="checkbox"
                                        name="machines[{{ $groupIndex }}][{{ $innerIndex }}][qc_check]" value="1"
                                        {{ $machine->qc_check ? 'checked' : '' }}></td>
                                <td><input type="checkbox"
                                        name="machines[{{ $groupIndex }}][{{ $innerIndex }}][kr_check]" value="1"
                                        {{ $machine->kr_check ? 'checked' : '' }}></td>
                            </tr>
                            <tr class="followup-row">
                                <td colspan="10">
                                    <div class="followup-wrapper">
                                        @foreach($machine->followups as $idx => $followup)
                                        <div class="border rounded p-2 mb-2">
                                            <label class="small mb-1">Koreksi Lanjutan #{{ $idx+1 }}</label>
                                            <input type="text"
                                                name="machines[{{ $groupIndex }}][{{ $innerIndex }}][followups][{{ $idx }}][notes]"
                                                value="{{ $followup->notes }}" class="form-control mb-1"
                                                placeholder="Catatan">
                                            <input type="text"
                                                name="machines[{{ $groupIndex }}][{{ $innerIndex }}][followups][{{ $idx }}][corrective_action]"
                                                value="{{ $followup->corrective_action }}" class="form-control mb-1"
                                                placeholder="Tindakan Koreksi">
                                            <select
                                                name="machines[{{ $groupIndex }}][{{ $innerIndex }}][followups][{{ $idx }}][verification]"
                                                class="form-select form-control">
                                                <option value="">-- Pilih --</option>
                                                <option value="1"
                                                    {{ $followup->verification == '1' ? 'selected' : '' }}>OK</option>
                                                <option value="0"
                                                    {{ $followup->verification == '0' ? 'selected' : '' }}>Tidak OK
                                                </option>
                                            </select>
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
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