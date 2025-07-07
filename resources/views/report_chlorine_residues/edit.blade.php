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
                        @php
                        $foundEditable = false;
                        $userName = Auth::user()->name;
                        $today = now()->format('Y-m-d');
                        @endphp
                        @foreach($report->details as $detail)
                        @php
                        if(!$foundEditable && $detail->result_ppm == null){
                        // ini hari pertama yang bisa diisi
                        $readonly = '';
                        $disabled = '';
                        $foundEditable = true;
                        } else {
                        $readonly = 'readonly';
                        $disabled = 'disabled';
                        }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $detail->day }}</td>
                            <td class="text-center">0,1 - 5</td>
                            <td>
                                <input type="number" step="0.01" name="details[{{ $detail->id }}][result_ppm]"
                                    value="{{ $detail->result_ppm }}" class="form-control" {{ $readonly }}>
                            </td>
                            <td>
                                <select name="details[{{ $detail->id }}][remark]" class="form-control remark-select"
                                    data-id="{{ $detail->id }}" {{ $disabled }}>
                                    <option value="">- Pilih -</option>
                                    <option value="OK" {{ $detail->remark == 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="Tidak OK" {{ $detail->remark == 'Tidak OK' ? 'selected' : '' }}>Tidak
                                        OK</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[{{ $detail->id }}][corrective_action]"
                                    value="{{ $detail->corrective_action }}" class="form-control corrective-action"
                                    data-id="{{ $detail->id }}" {{ $readonly }}>
                            </td>
                            <td>
                                <select name="details[{{ $detail->id }}][verification]"
                                    class="form-control verification-select" data-id="{{ $detail->id }}"
                                    {{ $disabled }}>
                                    <option value="">- Pilih -</option>
                                    <option value="OK" {{ $detail->verification == 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="Tidak OK"
                                        {{ $detail->verification == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </td>
                            <td class="text-center">
                                @if(!$readonly)
                                {{-- hari yang diedit: langsung set verified_by & verified_at --}}
                                <input type="hidden" name="details[{{ $detail->id }}][verified_by]"
                                    value="{{ $userName }}">
                                <input type="hidden" name="details[{{ $detail->id }}][verified_at]"
                                    value="{{ $today }}">
                                <small class="text-muted">by {{ $userName }}</small>
                                @else
                                <small>{{ $detail->verified_by }}</small>
                                @if($detail->verified_at)
                                <br><small>{{ \Carbon\Carbon::parse($detail->verified_at)->format('d-m-Y') }}</small>
                                @endif
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
                                    value="{{ $followup->notes }}" class="form-control" {{ $readonly }}>
                            </td>
                            <td>
                                <input type="text"
                                    name="details[{{ $detail->id }}][followups][{{ $index }}][corrective_action]"
                                    value="{{ $followup->corrective_action }}" class="form-control" {{ $readonly }}>
                            </td>
                            <td>
                                <select name="details[{{ $detail->id }}][followups][{{ $index }}][verification]"
                                    class="form-control followup-verification" data-id="{{ $detail->id }}"
                                    {{ $disabled }}>
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

                        {{-- Tempat followup baru hanya di hari editable --}}
                        @if(!$readonly)
                        <tr>
                            <td colspan="7">
                                <div id="followup-wrapper-{{ $detail->id }}"></div>
                            </td>
                        </tr>
                        @endif

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
@php $userName = Auth::user()->name; @endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    // remark-select & verification-select logic sama seperti sebelumnya
    document.querySelectorAll('.remark-select').forEach(select => {
        select.addEventListener('change', function() {
            const id = this.dataset.id;
            const verification = document.querySelector(
                `.verification-select[data-id="${id}"]`);
            const corrective = document.querySelector(`.corrective-action[data-id="${id}"]`);
            const wrapper = document.getElementById(`followup-wrapper-${id}`);
            wrapper.innerHTML = '';
            if (this.value === 'OK') {
                verification.value = 'OK';
                corrective.value = '';
                corrective.setAttribute('readonly', true);
            } else if (this.value === 'Tidak OK') {
                verification.value = 'Tidak OK';
                corrective.removeAttribute('readonly');
                addFollowupField(id, wrapper);
            } else {
                verification.value = '';
                corrective.setAttribute('readonly', true);
            }
        });
    });

    document.querySelectorAll('.verification-select').forEach(select => {
        select.addEventListener('change', function() {
            const id = this.dataset.id;
            const wrapper = document.getElementById(`followup-wrapper-${id}`);
            if (this.value === 'Tidak OK') {
                if (wrapper.querySelectorAll('.followup-group').length === 0) {
                    addFollowupField(id, wrapper);
                }
            } else if (this.value === 'OK') {
                wrapper.innerHTML = '';
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

        const newSelect = wrapper.querySelectorAll('.followup-verification')[count];
        newSelect.addEventListener('change', function() {
            const all = wrapper.querySelectorAll('.followup-group');
            const current = Array.from(all).indexOf(this.closest('.followup-group'));
            if (this.value === 'Tidak OK') {
                if (current === all.length - 1) addFollowupField(id, wrapper);
            } else if (this.value === 'OK') {
                for (let i = all.length - 1; i > current; i--) all[i].remove();
            }
        });
    }

    // Auto set remark & verification saat user isi result_ppm
    document.querySelectorAll('input[name^="details"][name$="[result_ppm]"]').forEach(input => {
        // Hanya aktifkan untuk field yang boleh diedit (tidak readonly)
        if (!input.hasAttribute('readonly')) {
            input.addEventListener('input', function() {
                const id = this.name.match(/\[(\d+)\]/)[1];
                const value = parseFloat(this.value);
                const remarkSelect = document.querySelector(
                    `select[name="details[${id}][remark]"]`);
                const verificationSelect = document.querySelector(
                    `select[name="details[${id}][verification]"]`);
                const corrective = document.querySelector(
                `.corrective-action[data-id="${id}"]`);
                const wrapper = document.getElementById(`followup-wrapper-${id}`);
                wrapper.innerHTML = '';

                if (!isNaN(value)) {
                    if (value >= 0.1 && value <= 5) {
                        remarkSelect.value = 'OK';
                        verificationSelect.value = 'OK';
                        corrective.value = '';
                        corrective.setAttribute('readonly', true);
                    } else {
                        remarkSelect.value = 'Tidak OK';
                        verificationSelect.value = 'Tidak OK';
                        corrective.removeAttribute('readonly');
                        addFollowupField(id, wrapper);
                    }
                } else {
                    remarkSelect.value = '';
                    verificationSelect.value = '';
                    corrective.value = '';
                    corrective.setAttribute('readonly', true);
                }
            });
        }
    });

});
</script>
@endsection