@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Form Kondisi Ruang Penyimpanan Bahan Baku dan Penunjang</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('cleanliness.store') }}" method="POST">
                @csrf

                <!-- Header -->
                <div class="mb-4">
                    <div class="d-flex" style="gap: 1rem;">
                        <div class="col-md-5 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Tanggal:</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-5 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Shift:</label>
                            <input type="text" name="shift" class="form-control" required>
                        </div>
                    </div>

                    <label>Area (Room Name):</label>
                    <select name="room_name" class="form-control col-md-5 mb-5" required>
                        <option value="">-- Pilih Area --</option>
                        <option value="Seasoning">Seasoning</option>
                        <option value="Chillroom">Chillroom</option>
                    </select>
                </div>

                <!-- Detail -->
                <div id="inspection-details">
                    <h5 class="mb-3">Detail Inspeksi</h5>
                </div>

                <button type="button" id="add-inspection" class="btn btn-secondary mr-2">+ Tambah Detail
                    Inspeksi</button>
                <button type="submit" class="btn btn-primary">Simpan</button>

                <!-- Template -->
                <template id="inspection-template">
                    <div class="inspection-block border rounded p-3 mb-3 position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute remove-inspection"
                            style="z-index: 1; right: 0; top: 0; margin-top: .5rem; margin-right: .5rem;">x</button>

                        <label>Jam Inspeksi:</label>
                        <input type="time" name="details[__index__][inspection_hour]" class="form-control mb-3 col-md-5"
                            value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>

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
                                {{-- ITEM 1 --}}
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <input type="hidden" name="details[__index__][items][0][item]"
                                            value="Kondisi dan penempatan barang">
                                        Kondisi dan penempatan barang
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][0][condition]" class="form-control"
                                            required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Tertata rapi">Tertata rapi</option>
                                            <option value="Tidak tertata rapi">Tidak tertata rapi</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][0][notes]"
                                            class="form-control note-select" data-item="0" required>
                                            <option value="">-- Pilih Catatan --</option>
                                            <option value="Sesuai">Sesuai</option>
                                            <option value="Penataan bahan tidak rapi">Penataan bahan tidak rapi</option>
                                            <option value="Penempatan bahan tidak sesuai dengan labelnya">Penempatan
                                                bahan tidak sesuai dengan labelnya</option>
                                            <option value="Tidak ada label/tagging di tempat penyimpanan">Tidak ada
                                                label/tagging di tempat penyimpanan</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][0][corrective_action]"
                                            id="corrective-0" class="form-control corrective-field">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][0][verification]" class="form-control"
                                            required>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>

                                {{-- ITEM 2 --}}
                                <tr>
                                    <td>2</td>
                                    <td>
                                        <input type="hidden" name="details[__index__][items][1][item]"
                                            value="Pelabelan">
                                        Pelabelan
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][1][condition]" class="form-control"
                                            required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Sesuai tagging dan jenis alergen">Sesuai tagging dan jenis
                                                alergen</option>
                                            <option value="Penempatan tidak sesuai">Penempatan tidak sesuai</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][1][notes]"
                                            class="form-control note-select" data-item="1" required>
                                            <option value="">-- Pilih Catatan --</option>
                                            <option value="Sesuai">Sesuai</option>
                                            <option value="Penataan bahan tidak rapi">Penataan bahan tidak rapi</option>
                                            <option value="Penempatan bahan tidak sesuai dengan labelnya">Penempatan
                                                bahan tidak sesuai dengan labelnya</option>
                                            <option value="Tidak ada label/tagging di tempat penyimpanan">Tidak ada
                                                label/tagging di tempat penyimpanan</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][1][corrective_action]"
                                            id="corrective-1" class="form-control corrective-field">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][1][verification]" class="form-control"
                                            required>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>

                                {{-- ITEM 3 --}}
                                <tr>
                                    <td>3</td>
                                    <td>
                                        <input type="hidden" name="details[__index__][items][2][item]"
                                            value="Kebersihan Ruangan">
                                        Kebersihan Ruangan
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][2][condition]" class="form-control"
                                            required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Bersih dan bebas kontaminan">Bersih dan bebas kontaminan
                                            </option>
                                            <option value="Tidak bersih / ada kontaminan">Tidak bersih / ada kontaminan
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][2][notes]"
                                            class="form-control note-select" data-item="2" required>
                                            <option value="">-- Pilih Catatan --</option>
                                            <option value="Sesuai">Sesuai</option>
                                            <option value="Rak penyimpanan bahan kotor">Rak penyimpanan bahan kotor
                                            </option>
                                            <option value="Langit-langit kotor">Langit-langit kotor</option>
                                            <option value="Pintu kotor">Pintu kotor</option>
                                            <option value="Dinding kotor">Dinding kotor</option>
                                            <option value="Curving kotor">Curving kotor</option>
                                            <option value="Curtain kotor">Curtain kotor</option>
                                            <option value="Lantai kotor/basah">Lantai kotor/basah</option>
                                            <option value="Pallet kotor">Pallet kotor</option>
                                            <option value="Lampu + cover kotor">Lampu + cover kotor</option>
                                            <option value="Exhaust fan kotor">Exhaust fan kotor</option>
                                            <option value="Evaporator kotor">Evaporator kotor</option>
                                            <option value="Temuan pest di area produksi">Temuan pest di area produksi
                                            </option>
                                            <option value="Temuan pest di dalam bahan">Temuan pest di dalam bahan
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][2][corrective_action]"
                                            id="corrective-2" class="form-control corrective-field">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][2][verification]" class="form-control"
                                            required>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>


                                {{-- ITEM 4 --}}
                                <tr>
                                    <td>4</td>
                                    <td>
                                        <input type="hidden" name="details[__index__][items][3][item]"
                                            value="Suhu ruang (℃) / RH (%)">
                                        Suhu ruang (℃) / RH (%)
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1" style="gap: 1rem;">
                                            <input type="number" step="0.1"
                                                name="details[__index__][items][3][temperature]" placeholder="℃"
                                                class="form-control" required>
                                            <input type="number" step="0.1"
                                                name="details[__index__][items][3][humidity]" placeholder="RH%"
                                                class="form-control" required>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][3][notes]"
                                            class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][3][corrective_action]"
                                            class="form-control" required>
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][3][verification]" class="form-control"
                                            required>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>
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

// Trigger satu kali di awal
document.getElementById('add-inspection').click();

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-inspection')) {
        e.target.closest('.inspection-block').remove();
    }
});

const koreksiMap = {
    // Item 1 & 2
    'Sesuai': '-',
    'Penataan bahan tidak rapi': 'Penataan bahan dengan rapi',
    'Penempatan bahan tidak sesuai dengan labelnya': 'Penataan bahan sesuai dengan labelnya',
    'Tidak ada label/tagging di tempat penyimpanan': 'Labelling/tagging sesuai tempat penyimpanan',

    // Item 3
    'Rak penyimpanan bahan kotor': 'Cleaning area/peralatan yang kotor',
    'Langit-langit kotor': 'Cleaning area/peralatan yang kotor',
    'Pintu kotor': 'Cleaning area/peralatan yang kotor',
    'Dinding kotor': 'Cleaning area/peralatan yang kotor',
    'Curving kotor': 'Cleaning area/peralatan yang kotor',
    'Curtain kotor': 'Cleaning area/peralatan yang kotor',
    'Lantai kotor/basah': 'Cleaning area/peralatan yang kotor',
    'Pallet kotor': 'Cleaning area/peralatan yang kotor',
    'Lampu + cover kotor': 'Cleaning area/peralatan yang kotor',
    'Exhaust fan kotor': 'Cleaning area/peralatan yang kotor',
    'Evaporator kotor': 'Cleaning area/peralatan yang kotor',
    'Temuan pest di area produksi': 'Inspeksi pest',
    'Temuan pest di dalam bahan': 'Inspeksi pest'
};


document.addEventListener('change', function(e) {
    if (e.target.classList.contains('note-select')) {
        const selected = e.target.value;
        const itemIndex = e.target.dataset.item;
        const target = document.getElementById('corrective-' + itemIndex);
        target.value = koreksiMap[selected] || '';
    }
});

document.addEventListener('change', function(e) {
    const target = e.target;

    // ITEM 1: Kondisi dan penempatan barang
    if (target.name.includes('[0][condition]')) {
        const block = target.closest('.inspection-block');
        if (target.value === 'Tertata rapi') {
            block.querySelector('select[name$="[0][notes]"]').value = 'Sesuai';
            block.querySelector('input[name$="[0][corrective_action]"]').value = '-';
            block.querySelector('select[name$="[0][verification]"]').value = '1';
        } else {
            block.querySelector('select[name$="[0][notes]"]').value = '';
            block.querySelector('input[name$="[0][corrective_action]"]').value = '';
            block.querySelector('select[name$="[0][verification]"]').value = '0';
        }
    }

    // ITEM 2: Pelabelan
    if (target.name.includes('[1][condition]')) {
        const block = target.closest('.inspection-block');
        if (target.value === 'Sesuai tagging dan jenis alergen') {
            block.querySelector('select[name$="[1][notes]"]').value = 'Sesuai';
            block.querySelector('input[name$="[1][corrective_action]"]').value = '-';
            block.querySelector('select[name$="[1][verification]"]').value = '1';
        } else {
            block.querySelector('select[name$="[1][notes]"]').value = '';
            block.querySelector('input[name$="[1][corrective_action]"]').value = '';
            block.querySelector('select[name$="[1][verification]"]').value = '0';
        }
    }

    // ITEM 3: Kebersihan Ruangan
    if (target.name.includes('[2][condition]')) {
        const block = target.closest('.inspection-block');
        if (target.value === 'Bersih dan bebas kontaminan') {
            block.querySelector('select[name$="[2][notes]"]').value = 'Sesuai';
            block.querySelector('input[name$="[2][corrective_action]"]').value = '-';
            block.querySelector('select[name$="[2][verification]"]').value = '1';
        } else {
            block.querySelector('select[name$="[2][notes]"]').value = '';
            block.querySelector('input[name$="[2][corrective_action]"]').value = '';
            block.querySelector('select[name$="[2][verification]"]').value = '0';
        }
    }
});
</script>
@endsection