@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Tambah Detail Pemeriksaan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('process-area-cleanliness.detail.store', $report->id) }}" method="POST">
                @csrf

                <div class="inspection-block border rounded p-3 mb-3 position-relative">
                    <label>Jam Inspeksi:</label>
                    <input type="time" name="details[0][inspection_hour]" class="form-control mb-3 col-md-5" required>

                    <table class="table">
                        <thead>
                            <tr>
                                <th class="align-middle">No</th>
                                <th class="align-middle">Item</th>
                                <th class="align-middle">Kondisi</th>
                                <th class="align-middle">Catatan</th>
                                <th class="align-middle">Tindakan Koreksi</th>
                                <th class="align-middle">Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $items = [
                            'Kondisi dan penempatan barang',
                            'Pelabelan',
                            'Kebersihan Ruangan',
                            'Suhu ruang (℃)'
                            ];
                            @endphp

                            @foreach($items as $i => $item)
                            <tr>
                                <td class="align-middle">{{ $i + 1 }}</td>
                                <td class="align-middle">
                                    <input type="hidden" name="details[0][items][{{ $i }}][item]" value="{{ $item }}">
                                    {{ $item }}
                                </td>
                                <td class="align-middle">
                                    @if($item === 'Suhu ruang (℃)')
                                    <input type="number" step="0.1" name="details[0][items][{{ $i }}][temperature]"
                                        placeholder="℃" class="form-control" required>
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

                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Klik tombol tambah manual
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-followup')) {
            const row = e.target.closest('tr');
            addFollowupField(row);
        }
    });

    // Perubahan kondisi atau verifikasi
    document.addEventListener('change', function(e) {
        const row = e.target.closest('tr');
        if (!row) return;

        const wrapperRow = row.nextElementSibling;
        if (!wrapperRow) return;

        const wrapper = wrapperRow.querySelector('.followup-wrapper');
        const notes = row.querySelector('.notes-input');
        const action = row.querySelector('.action-input');
        const verification = row.querySelector('.verification-select');
        const addBtn = row.querySelector('.add-followup');
        const condition = row.querySelector('.condition-select')?.value;

        // Saat kondisi diubah
        if (e.target.classList.contains('condition-select')) {
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
                wrapper.innerHTML = '';
                addBtn.style.display = 'none';
                addFollowupField(row);
            }
        }

        // Saat verifikasi utama diubah
        if (e.target.classList.contains('verification-select')) {
            if (condition === 'Kotor') {
                if (e.target.value === '0') {
                    wrapper.innerHTML = '';
                    addFollowupField(row);
                } else {
                    wrapper.innerHTML = '';
                }
                addBtn.style.display = 'none';
            }
        }
    });

    function addFollowupField(row) {
        const wrapperRow = row.nextElementSibling;
        if (!wrapperRow) return;
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

        const newSelect = wrapper.querySelectorAll('.followup-verification')[count];
        newSelect.addEventListener('change', function() {
            const allFollowups = wrapper.querySelectorAll('.followup-group');
            const currentIndex = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

            if (this.value === '0') {
                if (allFollowups.length === currentIndex + 1) {
                    addFollowupField(row);
                }
            } else {
                for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                    allFollowups[i].remove();
                }
            }
        });
    }
});
</script>
@endsection