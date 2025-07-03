@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Laporan Residu Klorin</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_chlorine_residues.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Section</label>
                    <select name="section_uuid" class="form-control" readonly>
                        <option value="">- Pilih Section -</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->uuid }}"
                            {{ $report->section_uuid == $section->uuid ? 'selected' : '' }}>
                            {{ $section->section_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Bulan</label>
                    <input type="month" name="month" class="form-control" id="monthInput"
                        value="{{ \Carbon\Carbon::parse($report->month)->format('Y-m') }}" readonly>
                </div>

                <div class="mb-3">
                    <label>Sampling Point</label>
                    <input type="text" name="sampling_point" class="form-control" value="{{ $report->sampling_point }}"
                        readonly>
                </div>

                <h5>Detail Harian</h5>
                <div id="detailsTableContainer">
                    <table class="table table-bordered">
                        <thead class="text-center">
                            <tr>
                                <th>Tanggal</th>
                                <th>Standar (PPM)</th>
                                <th>Hasil Pemeriksaan (PPM)</th>
                                <th>Keterangan</th>
                                <th>Tindakan Koreksi</th>
                                <th>Verifikasi</th>
                                <th>Diverifikasi Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report->details as $detail)
                            <tr>
                                <td class="text-center">{{ $detail->day }}</td>
                                <td class="text-center">0,1 - 5</td>
                                <td>
                                    <input type="number" step="0.01" name="details[{{ $detail->id }}][result_ppm]"
                                        value="{{ $detail->result_ppm }}" class="form-control">
                                </td>
                                <td>
                                    <select name="details[{{ $detail->id }}][remark]" class="form-control">
                                        <option value="">- Pilih -</option>
                                        <option value="OK" {{ $detail->remark == 'OK' ? 'selected' : '' }}>OK</option>
                                        <option value="Tidak OK" {{ $detail->remark == 'Tidak OK' ? 'selected' : '' }}>
                                            Tidak OK</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $detail->id }}][corrective_action]"
                                        value="{{ $detail->corrective_action }}" class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $detail->id }}][verification]"
                                        value="{{ $detail->verification }}" class="form-control">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input verify-checkbox"
                                        data-id="{{ $detail->id }}" {{ $detail->verified_by ? 'checked' : '' }}>
                                    <input type="hidden" name="details[{{ $detail->id }}][verified_by]"
                                        id="verified_by_{{ $detail->id }}" value="{{ $detail->verified_by }}">
                                    <input type="hidden" name="details[{{ $detail->id }}][verified_at]"
                                        id="verified_at_{{ $detail->id }}" value="{{ $detail->verified_at }}">
                                    @if($detail->verified_at)
                                    <small
                                        class="d-block">{{ \Carbon\Carbon::parse($detail->verified_at)->format('d-m-Y') }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-success mt-3">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
@php
$userName = Auth::user()->name;
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userName = @json($userName);

    // Tambahkan listener untuk semua checkbox
    document.querySelectorAll('.verify-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.dataset.id;
            const verifiedByInput = document.getElementById('verified_by_' + id);
            const verifiedAtInput = document.getElementById('verified_at_' + id);

            if (this.checked) {
                verifiedByInput.value = userName;
                verifiedAtInput.value = new Date().toISOString().slice(0, 10);
            } else {
                verifiedByInput.value = '';
                verifiedAtInput.value = '';
            }
        });
    });
});
</script>
@endsection