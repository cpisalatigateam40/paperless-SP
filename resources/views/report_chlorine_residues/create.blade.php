@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Laporan Residu Klorin</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_chlorine_residues.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Section</label>
                    <select name="section_uuid" class="form-control" required>
                        <option value="">- Pilih Section -</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Bulan</label>
                    <input type="month" name="month" class="form-control" id="monthInput" required>
                </div>

                <div class="mb-3">
                    <label>Sampling Point</label>
                    <input type="text" name="sampling_point" class="form-control">
                </div>

                <h5>Detail Harian</h5>
                <div id="detailsTableContainer">
                    <p class="text-muted">Pilih bulan terlebih dahulu untuk menampilkan tabel detail.</p>
                </div>

                <button type="submit" class="btn btn-success mt-3">Simpan</button>
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
    const monthInput = document.getElementById('monthInput');
    const container = document.getElementById('detailsTableContainer');
    const userName = @json($userName);

    monthInput.addEventListener('change', function() {
        const [year, month] = this.value.split('-').map(Number);
        const daysInMonth = new Date(year, month, 0).getDate();

        let html = `
            <table class="table table-bordered table-sm">
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
        `;
        for (let day = 1; day <= daysInMonth; day++) {
            html += `
                <tr>
                    <td class="text-center">${day}</td>
                    <td class="text-center">0,1 - 5</td>
                    <td><input type="number" step="0.01" name="details[${day}][result_ppm]" class="form-control"></td>
                    <td>
                        <select name="details[${day}][remark]" class="form-control remark-select" data-day="${day}">
                            <option value="">- Pilih -</option>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </td>
                    <td><input type="text" name="details[${day}][corrective_action]" class="form-control corrective-action" data-day="${day}"></td>
                    <td>
                        <select name="details[${day}][verification]" class="form-control verification-select" data-day="${day}">
                            <option value="">- Pilih -</option>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input verify-checkbox" data-day="${day}">
                        <input type="hidden" name="details[${day}][verified_by]" id="verified_by_${day}">
                        <input type="hidden" name="details[${day}][verified_at]" id="verified_at_${day}">
                    </td>
                </tr>
                <tr id="followup-row-${day}" class="d-none">
                    <td colspan="7">
                        <div class="followup-wrapper" id="followup-wrapper-${day}"></div>
                    </td>
                </tr>
            `;
        }
        html += '</tbody></table>';
        container.innerHTML = html;

        // Checkbox verified
        document.querySelectorAll('.verify-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const day = this.dataset.day;
                document.getElementById('verified_by_' + day).value = this.checked ?
                    userName : '';
                document.getElementById('verified_at_' + day).value = this.checked ?
                    new Date().toISOString().slice(0, 10) : '';
            });
        });

        // Remark select
        document.querySelectorAll('.remark-select').forEach(select => {
            select.addEventListener('change', function() {
                const day = this.dataset.day;
                const corrective = document.querySelector(
                    `.corrective-action[data-day="${day}"]`);
                const verification = document.querySelector(
                    `.verification-select[data-day="${day}"]`);
                const followupRow = document.getElementById(`followup-row-${day}`);
                const wrapper = document.getElementById(`followup-wrapper-${day}`);
                wrapper.innerHTML = '';

                if (this.value === 'OK') {
                    corrective.value = '';
                    corrective.setAttribute('readonly', true);
                    verification.value = 'OK';
                    followupRow.classList.add('d-none');
                } else if (this.value === 'Tidak OK') {
                    corrective.removeAttribute('readonly');
                    verification.value = 'Tidak OK';
                    followupRow.classList.remove('d-none');
                    addFollowupField(day);
                } else {
                    corrective.value = '';
                    corrective.setAttribute('readonly', true);
                    verification.value = '';
                    followupRow.classList.add('d-none');
                }
            });
        });

        // Verification select utama
        document.querySelectorAll('.verification-select').forEach(select => {
            select.addEventListener('change', function() {
                const day = this.dataset.day;
                const followupRow = document.getElementById(`followup-row-${day}`);
                const wrapper = document.getElementById(`followup-wrapper-${day}`);
                wrapper.innerHTML = '';

                if (this.value === 'Tidak OK') {
                    followupRow.classList.remove('d-none');
                    addFollowupField(day);
                } else {
                    followupRow.classList.add('d-none');
                }
            });
        });

        // Add followup field
        function addFollowupField(day) {
            const wrapper = document.getElementById(`followup-wrapper-${day}`);
            const count = wrapper.querySelectorAll('.followup-group').length;

            const html = `
            <div class="followup-group border rounded p-1 mb-2">
                <strong>Koreksi Lanjutan #${count + 1}</strong>
                <input type="text" name="details[${day}][followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan Koreksi Lanjutan">
                <input type="text" name="details[${day}][followups][${count}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                <select name="details[${day}][followups][${count}][verification]" class="form-control followup-verification">
                    <option value="">- Pilih Verifikasi -</option>
                    <option value="OK">OK</option>
                    <option value="Tidak OK">Tidak OK</option>
                </select>
            </div>`;
            wrapper.insertAdjacentHTML('beforeend', html);

            const newSelect = wrapper.querySelectorAll('.followup-verification')[count];
            newSelect.addEventListener('change', function() {
                const allFollowups = wrapper.querySelectorAll('.followup-group');
                const currentIndex = Array.from(allFollowups).indexOf(this.closest(
                    '.followup-group'));

                if (this.value === 'Tidak OK') {
                    if (currentIndex === allFollowups.length - 1) {
                        addFollowupField(day);
                    }
                } else if (this.value === 'OK') {
                    // Hapus semua followup setelah currentIndex
                    for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                        allFollowups[i].remove();
                    }
                }
            });
        }

    });
});
</script>
@endsection