@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Tambah Laporan Verifikasi Kebersihan Area Proses</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('process-area-cleanliness.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <div class="d-flex" style="gap: 1rem;">
                        <div class="col-md-5 mb-3">
                            <label>Tanggal:</label>
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}"
                                required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label>Shift:</label>
                            <input type="text" name="shift" class="form-control" required>
                        </div>
                    </div>

                    <label>Area:</label>
                    <select name="section_name" class="form-control col-md-5 mb-5" required>
                        <option value="">-- Pilih Area --</option>
                        <option value="MP">MP</option>
                        <option value="Cooking">Cooking</option>
                        <option value="Packing">Packing</option>
                        <option value="Cartoning">Cartoning</option>
                    </select>
                </div>

                <div id="inspection-details">
                    <h5 class="mb-3">Detail Inspeksi</h5>
                </div>

                <button type="button" id="add-inspection" class="btn btn-secondary mr-2 d-none">+ Tambah Detail
                    Inspeksi</button>
                <button type="submit" class="btn btn-primary">Simpan</button>

                <!-- Template -->
                <template id="inspection-template">
                    <div class="inspection-block border rounded p-3 mb-3 position-relative">
                        <!-- <button type="button" class="btn btn-sm btn-danger position-absolute remove-inspection"
                            style="right: .5rem; top: .5rem;">x</button> -->

                        <label>Jam Inspeksi:</label>
                        <input type="time" name="details[__index__][inspection_hour]" class="form-control mb-3 col-md-5"
                            value="{{ now()->format('H:i') }}" required>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Item</th>
                                    <th>Kondisi</th>
                                    <th>Catatan</th>
                                    <th>Tindakan Koreksi</th>
                                    <th>Verifikasi setelahdilakukan tindakan koreksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $items = [
                                'Kondisi Kebersihan Ruangan',
                                'Kondisi Kebersihan Peralatan',
                                'Kondisi
                                Kebersihan Karyawan',
                                'Suhu ruang (℃)'
                                ];
                                @endphp

                                @foreach($items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="details[0][items][{{ $i }}][item]"
                                            value="{{ $item }}">
                                        {{ $item }}
                                    </td>
                                    <td>
                                        @if(Str::contains($item, 'Suhu ruang'))
                                        <div class="row">
                                            <div class="col">
                                                <input type="number" step="0.1"
                                                    name="details[0][items][{{ $i }}][temperature_actual]"
                                                    class="form-control mb-1" placeholder="Actual" required>
                                            </div>
                                            <div class="col">
                                                <input type="number" step="0.1"
                                                    name="details[0][items][{{ $i }}][temperature_display]"
                                                    class="form-control mb-1" placeholder="Display" required>
                                            </div>
                                        </div>
                                        @else
                                        <select name="details[0][items][{{ $i }}][condition]"
                                            class="form-control condition-select" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Bersih">1. Bersih</option>
                                            <option value="Kotor">2. Kotor</option>
                                        </select>
                                        @endif

                                    </td>
                                    <td><input type="text" name="details[0][items][{{ $i }}][notes]"
                                            class="form-control notes-input"></td>
                                    <td><input type="text" name="details[0][items][{{ $i }}][corrective_action]"
                                            class="form-control action-input"></td>
                                    <td>
                                        <select name="details[0][items][{{ $i }}][verification]"
                                            class="form-control verification-select">
                                            <option value="">-- Pilih --</option>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-followup mt-1"
                                            style="display:none;">+ Tambah Koreksi Lanjutan</button>
                                    </td>
                                </tr>
                                <tr class="followup-row">
                                    <td colspan="6">
                                        <div class="followup-wrapper"></div>
                                    </td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </template>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let inspectionIndex = 0;

document.getElementById('add-inspection').addEventListener('click', function() {
    const template = document.getElementById('inspection-template').innerHTML;
    const rendered = template.replace(/__index__/g, inspectionIndex);
    document.getElementById('inspection-details').insertAdjacentHTML('beforeend', rendered);
    inspectionIndex++;
});

document.getElementById('add-inspection').click();

// Fungsi menambahkan koreksi lanjutan ke baris <tr>
function addFollowupField(row) {
    const wrapperRow = row.nextElementSibling;
    const wrapper = wrapperRow.querySelector('.followup-wrapper');
    const baseName = row.querySelector('.verification-select').name.replace('[verification]', '');
    const count = wrapper.querySelectorAll('.followup-group').length;

    const html = `
        <div class="followup-group border rounded p-2 mb-2">
            <label class="small mb-1">Koreksi Lanjutan #${count + 1}</label>
            <input type="text" name="${baseName}[followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan">
            <input type="text" name="${baseName}[followups][${count}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
            <select name="${baseName}[followups][${count}][verification]" class="form-control followup-verification">
                <option value="">-- Pilih --</option>
                <option value="0">Tidak OK</option>
                <option value="1">OK</option>
            </select>
        </div>
    `;

    wrapper.insertAdjacentHTML('beforeend', html);

    // Tambahkan event listener ke select yang baru dibuat
    const newSelect = wrapper.querySelectorAll('.followup-group')[count].querySelector('.followup-verification');
    newSelect.addEventListener('change', function() {
        const allFollowups = wrapper.querySelectorAll('.followup-group');
        const currentIndex = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

        if (this.value === '0') {
            // Tambah koreksi lanjutan berikutnya
            const nextIndex = currentIndex + 1;
            if (allFollowups.length === nextIndex) {
                addFollowupField(row);
            }
        } else {
            // Hapus semua followup setelah currentIndex
            for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                allFollowups[i].remove();
            }
        }
    });

}

// Klik tombol koreksi lanjutan manual
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-inspection')) {
        e.target.closest('.inspection-block').remove();
    }

    if (e.target.classList.contains('add-followup')) {
        const row = e.target.closest('tr');
        addFollowupField(row);
    }
});

// Reaksi terhadap perubahan kondisi dan verifikasi utama
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('condition-select')) {
        const row = e.target.closest('tr');
        const notes = row.querySelector('.notes-input');
        const action = row.querySelector('.action-input');
        const verification = row.querySelector('.verification-select');
        const addBtn = row.querySelector('.add-followup');
        const wrapperRow = row.nextElementSibling;
        const wrapper = wrapperRow.querySelector('.followup-wrapper');

        if (e.target.value === 'Bersih') {
            notes.value = '';
            action.value = '';
            verification.value = '1';
            notes.setAttribute('readonly', true);
            action.setAttribute('readonly', true);
            wrapper.innerHTML = '';
            addBtn.style.display = 'none';
        } else {
            notes.removeAttribute('readonly');
            action.removeAttribute('readonly');
            verification.value = '0';
            addBtn.style.display = 'none';
            wrapper.innerHTML = '';
            addFollowupField(row);
        }
    }

    if (e.target.classList.contains('verification-select')) {
        const row = e.target.closest('tr');
        const condition = row.querySelector('.condition-select')?.value;
        const addBtn = row.querySelector('.add-followup');
        const wrapperRow = row.nextElementSibling;
        const wrapper = wrapperRow.querySelector('.followup-wrapper');

        wrapper.innerHTML = '';
        if (condition === 'Kotor' && e.target.value === '0') {
            addFollowupField(row);
            addBtn.style.display = 'none';
        } else {
            addBtn.style.display = 'none';
        }
    }
});
</script>
@endsection