@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Buat Laporan Verifikasi Pembuatan Larutan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('report-solvents.store') }}" method="POST">
                @csrf

                {{-- HEADER --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">No.</th>
                                <th rowspan="2" class="align-middle">Nama Bahan</th>
                                <th rowspan="2" class="align-middle">Kadar Yang Diinginkan</th>
                                <th colspan="2" class="align-middle">Verifikasi Formulasi</th>
                                <th rowspan="2" class="align-middle">Keterangan</th>
                                <th rowspan="2" class="align-middle">Hasil Verifikasi</th>
                                <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
                            </tr>
                            <tr>
                                <th class="align-middle">Volume Bahan (mL)</th>
                                <th class="align-middle">Volume Larutan (mL)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solventItems as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="text-start">
                                    {{ $item->name }}
                                    <input type="hidden" name="details[{{ $i }}][solvent_uuid]"
                                        value="{{ $item->uuid }}">
                                </td>
                                <td>{{ $item->concentration }}</td>
                                <td>{{ $item->volume_material }}</td>
                                <td>{{ $item->volume_solvent }}</td>
                                <td class="text-start">{{ $item->application_area }}</td>
                                <td>
                                    <select name="details[{{ $i }}][verification_result]"
                                        class="form-select form-control form-select-sm verification-initial"
                                        data-index="{{ $i }}">
                                        <option value="">-- Pilih --</option>
                                        <option value="1">OK</option>
                                        <option value="0">Tidak OK</option>
                                    </select>

                                </td>
                                <td>
                                    <input type="text" name="details[{{ $i }}][corrective_action]"
                                        class="form-control form-control-sm corrective-action" data-index="{{ $i }}">
                                </td>
                                <td>
                                    <select name="details[{{ $i }}][reverification_action]"
                                        class="form-select form-control form-select-sm reverification-select"
                                        data-index="{{ $i }}">
                                        <option value="">-- Pilih --</option>
                                        <option value="1">OK</option>
                                        <option value="0">Tidak OK</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="followup-row d-none">
                                <td colspan="9">
                                    <div class="followup-wrapper"></div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

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
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.verification-initial').forEach(select => {
        select.addEventListener('change', () => {
            const index = select.dataset.index;
            const correctiveInput = document.querySelector(
                `input[name="details[${index}][corrective_action]"]`);
            const reverificationSelect = document.querySelector(
                `select[name="details[${index}][reverification_action]"]`);
            const followupRow = correctiveInput.closest('tr').nextElementSibling;
            const wrapper = followupRow.querySelector('.followup-wrapper');

            wrapper.innerHTML = ''; // reset followups
            followupRow.classList.add('d-none');

            if (select.value === '1') {
                // Verifikasi awal OK: corrective disable, reverification OK & disable
                correctiveInput.value = '';
                correctiveInput.setAttribute('readonly', true);
                reverificationSelect.value = '1';
                reverificationSelect.setAttribute('readonly', true);
            } else {
                // Verifikasi awal Tidak OK: corrective aktif, reverification aktif (default Tidak OK)
                correctiveInput.removeAttribute('readonly');
                reverificationSelect.value = '0';
                reverificationSelect.removeAttribute('readonly');
                followupRow.classList.remove('d-none');
                addFollowupField(index, wrapper);
            }
        });
    });

    document.querySelectorAll('.reverification-select').forEach(select => {
        select.addEventListener('change', () => {
            const index = select.dataset.index;
            const followupRow = select.closest('tr').nextElementSibling;
            const wrapper = followupRow.querySelector('.followup-wrapper');

            wrapper.innerHTML = ''; // reset

            if (select.value === '0') {
                followupRow.classList.remove('d-none');
                addFollowupField(index, wrapper);
            } else {
                followupRow.classList.add('d-none');
            }
        });
    });
});

// Fungsi tambah followup
function addFollowupField(index, wrapper) {
    const count = wrapper.querySelectorAll('.followup-group').length;
    const html = `
                <div class="followup-group border rounded p-2 mb-2">
                    <label class="small mb-1">Koreksi Lanjutan #${count + 1}</label>
                    <input type="text" name="details[${index}][followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan">
                    <input type="text" name="details[${index}][followups][${count}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                    <select name="details[${index}][followups][${count}][verification]" class="form-select form-control followup-verification">
                        <option value="">-- Pilih --</option>
                        <option value="1">OK</option>
                        <option value="0">Tidak OK</option>
                    </select>
                </div>
            `;
    wrapper.insertAdjacentHTML('beforeend', html);

    // Tambahkan event listener untuk followup verification
    const newSelect = wrapper.querySelectorAll('.followup-group')[count].querySelector('.followup-verification');
    newSelect.addEventListener('change', () => {
        const allFollowups = wrapper.querySelectorAll('.followup-group');
        const currentIndex = Array.from(allFollowups).indexOf(newSelect.closest('.followup-group'));
        if (newSelect.value === '0') {
            if (allFollowups.length === currentIndex + 1) {
                addFollowupField(index, wrapper);
            }
        } else {
            for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                allFollowups[i].remove();
            }
        }
    });
}
</script>
@endsection