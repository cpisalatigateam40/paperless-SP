@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('process-area-cleanliness.detail.store', $report->id) }}" method="POST">
                @csrf

                <div class="border rounded p-3 mb-3 position-relative">
                    <label>Jam Inspeksi:</label>
                    <input type="time" name="details[0][inspection_hour]" class="form-control mb-3 col-md-5" required>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Kondisi</th>
                                <th>Catatan</th>
                                <th>Tindakan Koreksi</th>
                                <th>Verifikasi</th>
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
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" name="details[0][items][{{ $i }}][item]" value="{{ $item }}">
                                    {{ $item }}
                                </td>
                                <td>
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
                                        <option value="0">Tidak OK</option>
                                        <option value="1">OK</option>
                                    </select>

                                    <div class="followup-wrapper mt-2"></div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-followup mt-1"
                                        style="display:none;">+ Tambah Koreksi Lanjutan</button>
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
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-followup')) {
        const row = e.target.closest('tr');
        const wrapper = row.querySelector('.followup-wrapper');
        const baseName = row.querySelector('.verification-select').name.replace('[verification]', '');
        const count = wrapper.querySelectorAll('.followup-group').length;

        const html = `
                <div class="followup-group border rounded p-2 mb-2">
                    <label class="small mb-1">Koreksi Lanjutan #${count + 1}</label>
                    <input type="text" name="${baseName}[followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan">
                    <input type="text" name="${baseName}[followups][${count}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                    <select name="${baseName}[followups][${count}][verification]" class="form-control">
                        <option value="0">Tidak OK</option>
                        <option value="1">OK</option>
                    </select>
                </div>
            `;
        wrapper.insertAdjacentHTML('beforeend', html);
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('condition-select')) {
        const row = e.target.closest('tr');
        const notes = row.querySelector('.notes-input');
        const action = row.querySelector('.action-input');
        const verification = row.querySelector('.verification-select');
        const followupWrapper = row.querySelector('.followup-wrapper');
        const addBtn = row.querySelector('.add-followup');

        if (e.target.value === 'Bersih') {
            notes.value = '';
            action.value = '';
            verification.value = '1';
            notes.setAttribute('readonly', true);
            action.setAttribute('readonly', true);
            followupWrapper.innerHTML = '';
            addBtn.style.display = 'none';
        } else {
            notes.removeAttribute('readonly');
            action.removeAttribute('readonly');
            verification.value = '0';
            addBtn.style.display = 'block';
        }
    }

    if (e.target.classList.contains('verification-select')) {
        const row = e.target.closest('tr');
        const condition = row.querySelector('.condition-select')?.value;
        const addBtn = row.querySelector('.add-followup');
        if (condition === 'Kotor') {
            addBtn.style.display = e.target.value === '0' ? 'block' : 'none';
        }
    }
});
</script>
@endsection