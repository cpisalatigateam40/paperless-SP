@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('gmp-employee.detail.store', $report->id) }}" method="POST">
                @csrf
                <input type="hidden" name="report_uuid" value="{{ $report->uuid }}">

                <div class="mb-3">
                    <label for="inspection_hour" class="form-label">Jam Inspeksi</label>
                    <input type="time" name="inspection_hour" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="section_name" class="form-label">Nama Area</label>
                    <input type="text" name="section_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="employee_name" class="form-label">Nama Karyawan</label>
                    <input type="text" name="employee_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <input type="text" name="notes" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="corrective_action" class="form-label">Tindakan Korektif</label>
                    <input type="text" name="corrective_action" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Verifikasi</label>
                    <select name="verification" id="main-verification" class="form-control verification-select"
                        required>
                        <option value="">Pilih</option>
                        <option value="0">Tidak OK</option>
                        <option value="1">OK</option>
                    </select>
                </div>

                {{-- Tempat koreksi lanjutan --}}
                <div id="followup-wrapper"></div>

                <button type="submit" class="btn btn-success">Simpan Detail</button>
                <a href="{{ route('gmp-employee.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const verificationSelect = document.querySelector('.verification-select');
    const wrapper = document.getElementById('followup-wrapper');

    function addFollowupField() {
        const currentFollowups = wrapper.querySelectorAll('.followup-group');
        const nextIndex = currentFollowups.length; // selalu hitung ulang

        const html = `
                <div class="followup-group border rounded p-3 mb-2">
                    <label class="small mb-1">Koreksi Lanjutan #${nextIndex + 1}</label>
                    <input type="text" name="followups[${nextIndex}][notes]" class="form-control mb-1" placeholder="Catatan">
                    <input type="text" name="followups[${nextIndex}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                    <select name="followups[${nextIndex}][verification]" class="form-control followup-verification">
                        <option value="0">Tidak OK</option>
                        <option value="1">OK</option>
                    </select>
                </div>
            `;
        wrapper.insertAdjacentHTML('beforeend', html);

        const newSelect = wrapper.querySelectorAll('.followup-verification')[nextIndex];
        newSelect.addEventListener('change', function() {
            const allFollowups = wrapper.querySelectorAll('.followup-group');
            const currentIdx = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

            if (this.value === '0') {
                if (allFollowups.length === currentIdx + 1) {
                    addFollowupField(); // tambah lagi
                }
            } else {
                // hapus semua setelahnya
                for (let i = allFollowups.length - 1; i > currentIdx; i--) {
                    allFollowups[i].remove();
                }
            }
        });
    }

    // saat verifikasi utama berubah
    verificationSelect.addEventListener('change', function() {
        wrapper.innerHTML = ''; // bersihkan dulu
        if (this.value === '0') {
            addFollowupField();
        }
    });
});
</script>


@endsection