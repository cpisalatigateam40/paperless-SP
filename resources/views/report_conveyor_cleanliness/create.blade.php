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
                <div class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ old('date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label>Shift</label>
                            <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
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

                {{-- DETAIL MESIN --}}
                <h5>Pemeriksaan Area Conveyor</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
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
                                <td>{{ $i+1 }}</td>
                                <td>
                                    <input type="time" name="machines[{{ $i }}][time]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </td>
                                <td class="text-start">
                                    <p class="mb-0">{{ $name }}</p>
                                    <input type="hidden" name="machines[{{ $i }}][machine_name]" value="{{ $name }}">
                                </td>
                                <td><input type="radio" name="machines[{{ $i }}][status]" value="bersih"></td>
                                <td><input type="radio" name="machines[{{ $i }}][status]" value="kotor"></td>
                                <td><input type="text" name="machines[{{ $i }}][notes]" class="form-control"></td>
                                <td><input type="text" name="machines[{{ $i }}][corrective_action]"
                                        class="form-control"></td>
                                <td>
                                    <select name="machines[{{ $i }}][verification]"
                                        class="form-select form-control verification-select">
                                        <option value="1">OK</option>
                                        <option value="0">Tidak OK</option>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-followup mt-1"
                                        style="display:none;">+ Tambah Koreksi Lanjutan</button>
                                </td>
                                <td><input type="checkbox" name="machines[{{ $i }}][qc_check]" value="1"></td>
                                <td><input type="checkbox" name="machines[{{ $i }}][kr_check]" value="1"></td>
                            </tr>
                            <tr class="followup-row">
                                <td colspan="10">
                                    <div class="followup-wrapper"></div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- SUBMIT --}}
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trigger bersih / kotor radio
    document.querySelectorAll('input[type=radio][value=bersih], input[type=radio][value=kotor]').forEach(
        radio => {
            radio.addEventListener('change', function() {
                const row = this.closest('tr');
                const notes = row.querySelector('input[name*="[notes]"]');
                const corrective = row.querySelector('input[name*="[corrective_action]"]');
                const verification = row.querySelector('select[name*="[verification]"]');

                if (this.value === 'bersih') {
                    notes.value = '';
                    corrective.value = '';
                    notes.setAttribute('readonly', true);
                    corrective.setAttribute('readonly', true);
                    verification.value = '1';
                    removeAllFollowups(row);
                } else if (this.value === 'kotor') {
                    notes.removeAttribute('readonly');
                    corrective.removeAttribute('readonly');
                    verification.value = '0';
                    createFollowup(row); // langsung tambah followup saat pertama kali kotor
                }
            });
        });

    // Trigger verifikasi select berubah
    document.querySelectorAll('select[name*="[verification]"]').forEach(select => {
        select.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.value === '0') {
                createFollowup(row);
            } else {
                removeAllFollowups(row);
            }
        });
    });

    function createFollowup(row) {
        const wrapperRow = row.nextElementSibling;
        if (!wrapperRow || !wrapperRow.querySelector('.followup-wrapper')) return;

        const wrapper = wrapperRow.querySelector('.followup-wrapper');
        // Tambah hanya jika belum ada followup pertama
        if (wrapper.children.length === 0) {
            const index = wrapper.children.length;
            const baseName = row.querySelector('select[name*="[verification]"]').name.replace('[verification]',
                '');
            const html = `
                <div class="followup-group border rounded p-2 mb-2">
                    <label class="small mb-1 text-start">Koreksi Lanjutan #${index+1}</label>
                    <input type="text" name="${baseName}[followups][${index}][notes]" class="form-control mb-1" placeholder="Catatan">
                    <input type="text" name="${baseName}[followups][${index}][corrective_action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                    <select name="${baseName}[followups][${index}][verification]" class="form-control followup-verification">
                        <option value="0">Tidak OK</option>
                        <option value="1">OK</option>
                    </select>
                </div>`;
            wrapper.insertAdjacentHTML('beforeend', html);

            // Tambahkan listener untuk followup verification baru
            const newSelect = wrapper.querySelector('.followup-verification');
            newSelect.addEventListener('change', function() {
                if (this.value === '0') {
                    addNextFollowup(wrapper, baseName);
                } else {
                    removeFollowupsAfter(wrapper, newSelect.closest('.followup-group'));
                }
            });
        }
    }

    function addNextFollowup(wrapper, baseName) {
        const index = wrapper.children.length;
        const html = `
            <div class="followup-group border rounded p-2 mb-2">
                <label class="small mb-1">Koreksi Lanjutan #${index+1}</label>
                <input type="text" name="${baseName}[followups][${index}][notes]" class="form-control mb-1" placeholder="Catatan">
                <input type="text" name="${baseName}[followups][${index}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                <select name="${baseName}[followups][${index}][verification]" class="form-control followup-verification">
                    <option value="0">Tidak OK</option>
                    <option value="1">OK</option>
                </select>
            </div>`;
        wrapper.insertAdjacentHTML('beforeend', html);

        const newSelect = wrapper.lastElementChild.querySelector('.followup-verification');
        newSelect.addEventListener('change', function() {
            if (this.value === '0') {
                addNextFollowup(wrapper, baseName);
            } else {
                removeFollowupsAfter(wrapper, newSelect.closest('.followup-group'));
            }
        });
    }

    function removeAllFollowups(row) {
        const wrapperRow = row.nextElementSibling;
        if (wrapperRow && wrapperRow.querySelector('.followup-wrapper')) {
            wrapperRow.querySelector('.followup-wrapper').innerHTML = '';
        }
    }

    function removeFollowupsAfter(wrapper, currentGroup) {
        const all = Array.from(wrapper.querySelectorAll('.followup-group'));
        const idx = all.indexOf(currentGroup);
        all.slice(idx + 1).forEach(el => el.remove());
    }
});
</script>
@endsection