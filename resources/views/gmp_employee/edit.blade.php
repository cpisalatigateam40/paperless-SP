@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan GMP Karyawan & Kontrol Sanitasi</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('gmp-employee.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', $report->date) }}"
                        required>
                </div>

                <div class="mb-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ old('shift', $report->shift) }}"
                        required>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#detail" type="button">GMP
                            Karyawan</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sanitasi" type="button">Sanitasi
                            Area</button>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- Tab GMP Karyawan --}}
                    <div class="tab-pane fade show active" id="detail">
                        @foreach($details as $index => $detail)
                        <div class="border rounded p-3 mb-3">
                            <h6>Detail Inspeksi</h6>
                            <div class="mb-2">
                                <label>Jam Inspeksi</label>
                                <input type="time" name="details[{{ $index }}][inspection_hour]" class="form-control"
                                    value="{{ $detail->inspection_hour }}">
                            </div>
                            <div class="mb-2">
                                <label>Nama Bagian</label>
                                <input type="text" name="details[{{ $index }}][section_name]" class="form-control"
                                    value="{{ $detail->section_name }}">
                            </div>
                            <div class="mb-2">
                                <label>Nama Karyawan</label>
                                <input type="text" name="details[{{ $index }}][employee_name]" class="form-control"
                                    value="{{ $detail->employee_name }}">
                            </div>
                            <div class="mb-2">
                                <label>Catatan</label>
                                <input type="text" name="details[{{ $index }}][notes]" class="form-control"
                                    value="{{ $detail->notes }}">
                            </div>
                            <div class="mb-2">
                                <label>Tindakan Korektif</label>
                                <input type="text" name="details[{{ $index }}][corrective_action]" class="form-control"
                                    value="{{ $detail->corrective_action }}">
                            </div>
                            <div class="mb-2">
                                <label>Verifikasi</label>
                                <select name="details[{{ $index }}][verification]"
                                    class="form-control verification-select" data-index="{{ $index }}" data-type="gmp">
                                    <option value="">Pilih</option>
                                    <option value="1" {{ $detail->verification == 1 ? 'selected' : '' }}>OK</option>
                                    <option value="0" {{ $detail->verification === 0 ? 'selected' : '' }}>Tidak OK
                                    </option>
                                </select>
                            </div>

                            <div class="followup-container mt-2" id="detail-followups-{{ $index }}"
                                style="display: {{ ($detail->verification === 0) ? 'block' : 'none' }}">
                                <h6>Koreksi Lanjutan</h6>
                                @foreach($detail->followups as $fIndex => $followup)
                                <div class="border rounded p-2 mb-2">
                                    <div class="mb-2">
                                        <label>Catatan</label>
                                        <input type="text" name="details[{{ $index }}][followups][{{ $fIndex }}][notes]"
                                            class="form-control" value="{{ $followup->notes }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Tindakan Korektif</label>
                                        <input type="text"
                                            name="details[{{ $index }}][followups][{{ $fIndex }}][action]"
                                            class="form-control" value="{{ $followup->action }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Verifikasi</label>
                                        <select name="details[{{ $index }}][followups][{{ $fIndex }}][verification]"
                                            class="form-control">
                                            <option value="">Pilih</option>
                                            <option value="1" {{ $followup->verification == 1 ? 'selected' : '' }}>✔
                                            </option>
                                            <option value="0" {{ $followup->verification == 0 ? 'selected' : '' }}>✘
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tab Sanitasi Area --}}
                    <div class="tab-pane fade" id="sanitasi">
                        @foreach($sanitationAreas as $index => $area)
                        <div class="border rounded p-3 mb-3">
                            <h6>Area: {{ $area->area_name }}</h6>
                            <div class="mb-2">
                                <label>Jam 1</label>
                                <input type="time" name="sanitation[hour_1]" class="form-control"
                                    value="{{ optional($sanitation)->hour_1 }}" {{ $isEdit ? 'disabled' : '' }}>
                            </div>
                            <div class="mb-2">
                                <label>Jam 2</label>
                                <input type="time" name="sanitation[hour_2]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}" {{ !$isEdit ? 'disabled' : '' }}>
                            </div>
                            <div class="mb-2">
                                <label>Nama Area</label>
                                <input type="text" name="sanitation_area[{{ $index }}][area_name]" class="form-control"
                                    value="{{ $area->area_name }}" readonly>
                            </div>
                            <div class="mb-2">
                                <label>Standar Klorin</label>
                                <input type="number" name="sanitation_area[{{ $index }}][chlorine_std]"
                                    class="form-control" value="{{ $area->chlorine_std }}">
                            </div>

                            <div class="d-flex" style="gap: 1rem">
                                <div class="col-md-6">
                                    <p class="fw-bold mt-3">Hasil Pengecekan Jam 1</p>
                                    <div class="mb-2">
                                        <label>Kadar Klorin</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][1][chlorine_level]"
                                            class="form-control"
                                            value="{{ $area->results_by_hour[1]->chlorine_level ?? '' }}" readonly>
                                    </div>
                                    <div class="mb-2">
                                        <label>Suhu</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][1][temperature]"
                                            class="form-control"
                                            value="{{ $area->results_by_hour[1]->temperature ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <p class="fw-bold mt-3">Hasil Pengecekan Jam 2</p>
                                    <div class="mb-2">
                                        <label>Kadar Klorin</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][2][chlorine_level]"
                                            class="form-control"
                                            value="{{ $area->results_by_hour[2]->chlorine_level ?? '' }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Suhu</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][2][temperature]"
                                            class="form-control"
                                            value="{{ $area->results_by_hour[2]->temperature ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label>Catatan</label>
                                <input type="text" name="sanitation_area[{{ $index }}][notes]" class="form-control"
                                    value="{{ $area->notes }}">
                            </div>
                            <div class="mb-2">
                                <label>Tindakan Korektif</label>
                                <input type="text" name="sanitation_area[{{ $index }}][corrective_action]"
                                    class="form-control" value="{{ $area->corrective_action }}">
                            </div>
                            <div class="mb-2">
                                <label>Verifikasi</label>
                                <select name="sanitation_area[{{ $index }}][verification]"
                                    class="form-control verification-select" data-index="{{ $index }}"
                                    data-type="sanitasi">
                                    <option value="">Pilih</option>
                                    <option value="1" {{ $area->verification == 1 ? 'selected' : '' }}>✔</option>
                                    <option value="0" {{ $area->verification == 0 ? 'selected' : '' }}>✘</option>
                                </select>
                            </div>

                            <div class="followup-container mt-2" id="sanitasi-followups-{{ $index }}"
                                style="display: {{ ($area->verification === 0) ? 'block' : 'none' }}">
                                <h6>Koreksi Lanjutan</h6>
                                @foreach($area->followups as $fIndex => $followup)
                                <div class="border rounded p-2 mb-2">
                                    <div class="mb-2">
                                        <label>Catatan</label>
                                        <input type="text"
                                            name="sanitation_area[{{ $index }}][followups][{{ $fIndex }}][notes]"
                                            class="form-control" value="{{ $followup->notes }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Tindakan Korektif</label>
                                        <input type="text"
                                            name="sanitation_area[{{ $index }}][followups][{{ $fIndex }}][action]"
                                            class="form-control" value="{{ $followup->action }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Verifikasi</label>
                                        <select
                                            name="sanitation_area[{{ $index }}][followups][{{ $fIndex }}][verification]"
                                            class="form-control">
                                            <option value="">Pilih</option>
                                            <option value="1" {{ $followup->verification == 1 ? 'selected' : '' }}>✔
                                            </option>
                                            <option value="0" {{ $followup->verification == 0 ? 'selected' : '' }}>✘
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.verification-select').change(function() {
        var value = $(this).val();
        var index = $(this).data('index');
        var type = $(this).data('type');
        var containerId = (type === 'gmp') ? '#detail-followups-' + index : '#sanitasi-followups-' +
            index;

        if (value === '0') {
            $(containerId).show();
        } else {
            $(containerId).hide();
        }
    });
});
</script>
@endsection