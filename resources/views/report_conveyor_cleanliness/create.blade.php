@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Laporan Pemeriksaan Kebersihan Conveyor</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report-conveyor-cleanliness.store') }}" method="POST">
                @csrf

                {{-- HEADER --}}
                <div style="margin-bottom: 3rem;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label>Shift</label>
                            <input type="text" name="shift" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Area</label>
                            <select name="section_uuid" class="form-select form-control">
                                <option value="">-- Pilih Section --</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- FORM MESIN --}}
                <div class="mb-3">
                    <h5>Pemeriksaan Area Conveyor</h5>
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
                                @php
                                    $mesins = [
                                        'Thermoformer Collimatic',
                                        'Thermoformer CFS',
                                        'Packing Manual 1',
                                        'Packing Manual 2',
                                    ];
                                @endphp

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

                {{-- Submit --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
