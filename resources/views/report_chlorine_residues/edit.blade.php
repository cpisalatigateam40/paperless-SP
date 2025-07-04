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
                    <input type="month" name="month" class="form-control"
                        value="{{ \Carbon\Carbon::parse($report->month)->format('Y-m') }}" readonly>
                </div>

                <div class="mb-3">
                    <label>Sampling Point</label>
                    <input type="text" name="sampling_point" class="form-control" value="{{ $report->sampling_point }}"
                        readonly>
                </div>

                <h5>Detail Harian</h5>
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
                                <select name="details[{{ $detail->id }}][remark]" class="form-control remark-select"
                                    data-id="{{ $detail->id }}">
                                    <option value="">- Pilih -</option>
                                    <option value="OK" {{ $detail->remark == 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="Tidak OK" {{ $detail->remark == 'Tidak OK' ? 'selected' : '' }}>Tidak
                                        OK</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[{{ $detail->id }}][corrective_action]"
                                    value="{{ $detail->corrective_action }}" class="form-control corrective-action"
                                    data-id="{{ $detail->id }}">
                            </td>
                            <td>
                                <select name="details[{{ $detail->id }}][verification]"
                                    class="form-control verification-select" data-id="{{ $detail->id }}">
                                    <option value="">- Pilih -</option>
                                    <option value="OK" {{ $detail->verification == 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="Tidak OK"
                                        {{ $detail->verification == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
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

                        {{-- Existing followups --}}
                        @foreach($detail->followups as $index => $followup)
                        <tr class="table-secondary">
                            <td></td>
                            <td colspan="2">↳ Koreksi Lanjutan #{{ $index+1 }}</td>
                            <td>
                                <input type="text" name="details[{{ $detail->id }}][followups][{{ $index }}][notes]"
                                    value="{{ $followup->notes }}" class="form-control">
                            </td>
                            <td>
                                <input type="text"
                                    name="details[{{ $detail->id }}][followups][{{ $index }}][corrective_action]"
                                    value="{{ $followup->corrective_action }}" class="form-control">
                            </td>
                            <td>
                                <select name="details[{{ $detail->id }}][followups][{{ $index }}][verification]"
                                    class="form-control followup-verification" data-id="{{ $detail->id }}">
                                    <option value="">- Pilih -</option>
                                    <option value="OK" {{ $followup->verification == 'OK' ? 'selected' : '' }}>OK
                                    </option>
                                    <option value="Tidak OK"
                                        {{ $followup->verification == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </td>
                            <td></td>
                        </tr>
                        @endforeach

                        {{-- Tempatkan followup baru --}}
                        <tr>
                            <td colspan="7">
                                <div id="followup-wrapper-{{ $detail->id }}"></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

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

    // Checkbox diverifikasi
    document.querySelectorAll('.verify-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.dataset.id;
            document.getElementById('verified_by_' + id).value = this.checked ? userName : '';
            document.getElementById('verified_at_' + id).value = this.checked ? new Date()
                .toISOString().slice(0, 10) : '';
        });
    });

    // Remark select: kalau OK → disable corrective, kalau Tidak OK → enable + tambahkan followup pertama
    document.querySelectorAll('.remark-select').forEach(select => {
        select.addEventListener('change', function() {
            const id = this.dataset.id;
            const corrective = document.querySelector(`.corrective-action[data-id="${id}"]`);
            const verification = document.querySelector(
            `.verification-select[data-id="${id}"]`);
            const wrapper = document.getElementById(`followup-wrapper-${id}`);
            wrapper.innerHTML = '';

            if (this.value === 'OK') {
                corrective.value = '';
                corrective.setAttribute('readonly', true);
                verification.value = 'OK';
            } else if (this.value === 'Tidak OK') {
                corrective.removeAttribute('readonly');
                verification.value = 'Tidak OK';
                addFollowupField(id, wrapper); // tambahkan followup pertama
            } else {
                corrective.setAttribute('readonly', true);
                verification.value = '';
            }
        });
    });

    // Verifikasi utama (bukan followup): saat dipilih "Tidak OK" → kalau belum ada followup, tambahkan followup pertama
    document.querySelectorAll('.verification-select').forEach(select => {
        select.addEventListener('change', function() {
            const id = this.dataset.id;
            const wrapper = document.getElementById(`followup-wrapper-${id}`);
            if (this.value === 'Tidak OK') {
                if (wrapper.querySelectorAll('.followup-group').length === 0) {
                    addFollowupField(id, wrapper);
                }
            } else if (this.value === 'OK') {
                wrapper.innerHTML = ''; // hapus semua followup
            }
        });
    });

    function addFollowupField(id, wrapper) {
        const count = wrapper.querySelectorAll('.followup-group').length;

        const html = `
        <div class="followup-group border rounded p-1 mb-2">
            <strong>Koreksi Lanjutan #${count+1}</strong>
            <input type="text" name="details[${id}][followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan Koreksi Lanjutan">
            <input type="text" name="details[${id}][followups][${count}][corrective_action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
            <select name="details[${id}][followups][${count}][verification]" class="form-control followup-verification" data-id="${id}">
                <option value="">- Pilih Verifikasi -</option>
                <option value="OK">OK</option>
                <option value="Tidak OK">Tidak OK</option>
            </select>
        </div>`;
        wrapper.insertAdjacentHTML('beforeend', html);

        // Listener untuk verifikasi followup
        const newSelect = wrapper.querySelectorAll('.followup-verification')[count];
        newSelect.addEventListener('change', function() {
            const allFollowups = wrapper.querySelectorAll('.followup-group');
            const currentIndex = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

            if (this.value === 'Tidak OK') {
                // Tambahkan followup baru hanya kalau ini adalah followup terakhir
                if (currentIndex === allFollowups.length - 1) {
                    addFollowupField(id, wrapper);
                }
            } else if (this.value === 'OK') {
                // Hapus followup setelahnya
                for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                    allFollowups[i].remove();
                }
            }
        });
    }
});
</script>
@endsection