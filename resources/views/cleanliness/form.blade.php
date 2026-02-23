@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Buat Laporan Verifikasi Kebersihan Ruang Penyimpanan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('cleanliness.store') }}" method="POST">
                @csrf

                <!-- Header -->
                <div class="mb-4">
                    <div class="d-flex" style="gap: 1rem;">
                        <div class="col-md-6 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Tanggal:</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Shift:</label>
                            <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" required>
                        </div>
                    </div>

                    <label>Area (Room Name):</label>
                    <select id="roomSelect" name="room_name" class="form-control col-md-6 mb-5" required>
                        <option value="">-- Pilih Area --</option>
                        <option value="Seasoning">Seasoning</option>
                        <option value="Chillroom">Chillroom</option>
                    </select>
                </div>

                <!-- Detail -->
                <div id="inspection-details">
                    <h5 class="mb-3">Detail Inspeksi</h5>
                </div>

                <button type="button" id="add-inspection" class="btn btn-secondary mr-2 d-none">+ Tambah Detail
                    Inspeksi</button>
                <button type="submit" class="btn btn-primary">Simpan</button>

                <!-- Template -->
                <template id="inspection-template">
                    <div class="inspection-block border rounded p-3 mb-3 position-relative" data-index="__index__">

                        <label>Jam Inspeksi:</label>
                        <input type="time" name="details[__index__][inspection_hour]" class="form-control mb-3 col-md-6"
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
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][0][notes][]" value="Sesuai"
                                                data-item="0">
                                            <label class="form-check-label">Sesuai</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][0][notes][]"
                                                value="Penataan bahan tidak rapi" data-item="0">
                                            <label class="form-check-label">Penataan bahan tidak rapi</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][0][notes][]"
                                                value="Penempatan bahan tidak sesuai dengan labelnya" data-item="0">
                                            <label class="form-check-label">Penempatan bahan tidak sesuai dengan
                                                labelnya</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][0][notes][]"
                                                value="Tidak ada label/tagging di tempat penyimpanan" data-item="0">
                                            <label class="form-check-label">Tidak ada label/tagging di tempat
                                                penyimpanan</label>
                                        </div>
                                    </td>

                                    <td>
                                        <input type="text" name="details[__index__][items][0][corrective_action]"
                                            id="corrective-0" class="form-control corrective-field">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][0][verification]" data-item="0"
                                            class="form-control verification-select" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="followup-row">
                                    <td colspan="6">
                                        <div class="followup-wrapper"></div>
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
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][1][notes][]" value="Sesuai"
                                                data-item="1">
                                            <label class="form-check-label">Sesuai</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][1][notes][]"
                                                value="Penataan bahan tidak rapi" data-item="1">
                                            <label class="form-check-label">Penataan bahan tidak rapi</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][1][notes][]"
                                                value="Penempatan bahan tidak sesuai dengan labelnya" data-item="1">
                                            <label class="form-check-label">Penempatan bahan tidak sesuai dengan
                                                labelnya</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][1][notes][]"
                                                value="Tidak ada label/tagging di tempat penyimpanan" data-item="1">
                                            <label class="form-check-label">Tidak ada label/tagging di tempat
                                                penyimpanan</label>
                                        </div>
                                    </td>

                                    <td>
                                        <input type="text" name="details[__index__][items][1][corrective_action]"
                                            id="corrective-1" class="form-control corrective-field">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][1][verification]" data-item="1"
                                            class="form-control verification-select" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="followup-row">
                                    <td colspan="6">
                                        <div class="followup-wrapper"></div>
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
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Sesuai"
                                                data-item="2">
                                            <label class="form-check-label">Sesuai</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]"
                                                value="Rak penyimpanan bahan kotor" data-item="2">
                                            <label class="form-check-label">Rak penyimpanan bahan kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Langit-langit kotor"
                                                data-item="2">
                                            <label class="form-check-label">Langit-langit kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Pintu kotor"
                                                data-item="2">
                                            <label class="form-check-label">Pintu kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Dinding kotor"
                                                data-item="2">
                                            <label class="form-check-label">Dinding kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Curving kotor"
                                                data-item="2">
                                            <label class="form-check-label">Curving kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Curtain kotor"
                                                data-item="2">
                                            <label class="form-check-label">Curtain kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Lantai kotor/basah"
                                                data-item="2">
                                            <label class="form-check-label">Lantai kotor/basah</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Pallet kotor"
                                                data-item="2">
                                            <label class="form-check-label">Pallet kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Lampu + cover kotor"
                                                data-item="2">
                                            <label class="form-check-label">Lampu + cover kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Exhaust fan kotor"
                                                data-item="2">
                                            <label class="form-check-label">Exhaust fan kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]" value="Evaporator kotor"
                                                data-item="2">
                                            <label class="form-check-label">Evaporator kotor</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]"
                                                value="Temuan pest di area produksi" data-item="2">
                                            <label class="form-check-label">Temuan pest di area produksi</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[__index__][items][2][notes][]"
                                                value="Temuan pest di dalam bahan" data-item="2">
                                            <label class="form-check-label">Temuan pest di dalam bahan</label>
                                        </div>
                                    </td>

                                    <td>
                                        <input type="text" name="details[__index__][items][2][corrective_action]"
                                            id="corrective-2" class="form-control corrective-field">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][2][verification]"
                                            class="form-control verification-select" data-item="2" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="followup-row">
                                    <td colspan="6">
                                        <div class="followup-wrapper"></div>
                                    </td>
                                </tr>

                                {{-- ITEM 4 --}}
                                <tr class="item-4-row">
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
                                                class="form-control">
                                            <input type="number" step="0.1"
                                                name="details[__index__][items][3][humidity]" placeholder="RH%"
                                                class="form-control">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][3][notes]"
                                            class="form-control">
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][3][corrective_action]"
                                            class="form-control">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][3][verification]" class="form-control">
                                            <option value="">-- Pilih --</option>
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

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endsection

@section('script')
<script>
// document.getElementById('roomSelect').addEventListener('change', function () {
//     const selected = this.value;
//     const item4 = document.querySelector('.item-4-row');

//     if (selected === "Chillroom") {
//         item4.classList.add('d-none'); // sembunyikan
//     } else {
//         item4.classList.remove('d-none'); // tampilkan kembali
//     }
// });

let inspectionIndex = 0;

document.getElementById('add-inspection').addEventListener('click', function() {
    const template = document.getElementById('inspection-template').innerHTML;
    const rendered = template.replace(/__index__/g, inspectionIndex);
    const container = document.createElement('div');
    container.innerHTML = rendered;

    document.getElementById('inspection-details').appendChild(container);
    inspectionIndex++;
});

// Trigger pertama kali
document.getElementById('add-inspection').click();

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-inspection')) {
        e.target.closest('.inspection-block').remove();
    }
});

const koreksiMap = {
    'Sesuai': '-',
    'Penataan bahan tidak rapi': 'Penataan bahan dengan rapi',
    'Penempatan bahan tidak sesuai dengan labelnya': 'Penataan bahan sesuai dengan labelnya',
    'Tidak ada label/tagging di tempat penyimpanan': 'Labelling/tagging sesuai tempat penyimpanan',
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

function addFollowupField(block, itemIndex) {
    const wrapper = block.querySelectorAll('.followup-wrapper')[itemIndex];
    const count = wrapper.querySelectorAll('.followup-group').length;

    const html = `
        <div class="followup-group border rounded p-3 mb-3">
            <label class="small mb-2 d-block">Koreksi Lanjutan #${count + 1}</label>
            <div class="mb-2">
                <input type="text" name="details[${block.dataset.index}][items][${itemIndex}][followups][${count}][notes]" class="form-control" placeholder="Catatan">
            </div>
            <div class="mb-2">
                <input type="text" name="details[${block.dataset.index}][items][${itemIndex}][followups][${count}][corrective_action]" class="form-control" placeholder="Tindakan Koreksi">
            </div>
            <div>
                <select name="details[${block.dataset.index}][items][${itemIndex}][followups][${count}][verification]" class="form-control followup-verification">
                    <option value="">-- Pilih --</option>
                    <option value="0">Tidak OK</option>
                    <option value="1">OK</option>
                </select>
            </div>
        </div>
    `;
    wrapper.insertAdjacentHTML('beforeend', html);

    const newSelect = wrapper.querySelectorAll('.followup-group')[count].querySelector('.followup-verification');
    newSelect.addEventListener('change', function() {
        const allFollowups = wrapper.querySelectorAll('.followup-group');
        const currentIndex = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

        if (this.value === '0') {
            if (allFollowups.length === currentIndex + 1) {
                addFollowupField(block, itemIndex);
            }
        } else {
            for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                allFollowups[i].remove();
            }
        }
    });
}

// NEW: Handle multi checkbox note
document.addEventListener('change', function(e) {
    const target = e.target;

    if (target.classList.contains('note-checkbox')) {
        const itemIndex = target.dataset.item;
        const block = target.closest('.inspection-block');
        const corrective = block.querySelector('#corrective-' + itemIndex);

        // ambil semua checkbox tercentang di item ini
        const checked = block.querySelectorAll(`.note-checkbox[data-item="${itemIndex}"]:checked`);
        let correctiveText = '';

        if (checked.length === 1) {
            const selected = checked[0].value;
            correctiveText = koreksiMap[selected] || '';
        } else if (checked.length > 1) {
            const unique = new Set();
            checked.forEach(c => {
                const correction = koreksiMap[c.value];
                if (correction) unique.add(correction);
            });
            correctiveText = Array.from(unique).join(', ');
        } else {
            correctiveText = '';
        }
        corrective.value = correctiveText;
    }

    // verifikasi berubah -> trigger followup
    if (target.classList.contains('verification-select')) {
        const block = target.closest('.inspection-block');
        const itemIndex = parseInt(target.dataset.item);
        const wrapper = block.querySelectorAll('.followup-wrapper')[itemIndex];
        wrapper.innerHTML = '';

        if (target.value === '0') {
            addFollowupField(block, itemIndex);
        }
    }

    // Auto-fill based on kondisi
    const autoFill = (itemIdx, conditionMatch, correctiveValue, verificationValue) => {
        if (target.name.includes(`[${itemIdx}][condition]`)) {
            const block = target.closest('.inspection-block');
            const corrective = block.querySelector(`input[name$="[${itemIdx}][corrective_action]"]`);
            const verificationField = block.querySelector(`select[name$="[${itemIdx}][verification]"]`);
            const wrapper = block.querySelectorAll('.followup-wrapper')[itemIdx];

            // checkbox
            const checkboxes = block.querySelectorAll(`.note-checkbox[data-item="${itemIdx}"]`);
            if (target.value === conditionMatch) {
                checkboxes.forEach(c => c.checked = (c.value === 'Sesuai'));
                corrective.value = correctiveValue;
                verificationField.value = verificationValue;
                wrapper.innerHTML = '';
            } else {
                checkboxes.forEach(c => c.checked = false);
                corrective.value = '';
                verificationField.value = '0';
                wrapper.innerHTML = '';
                addFollowupField(block, itemIdx);
            }
        }
    }
    autoFill(0, 'Tertata rapi', '-', '1');
    autoFill(1, 'Sesuai tagging dan jenis alergen', '-', '1');
    autoFill(2, 'Bersih dan bebas kontaminan', '-', '1');
});

// Script sync sensor tetap seperti sebelumnya (biarkan)

document.getElementById('sync-sensor').addEventListener('click', function() {
    const button = this;
    const label = button.querySelector('.label');
    const icon = button.querySelector('.icon');
    const message = document.getElementById('sync-status-message');

    label.textContent = 'Menyinkronkan...';
    icon.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`;
    button.disabled = true;
    message.style.display = 'none';

    axios.get('http://10.68.1.220:3003/api/sensor/last')
        .then(response => {
            const mesin701 = response.data.data["701"];
            if (!mesin701 || !mesin701.DATA || mesin701.DATA.CH1 === undefined) {
                alert('Data sensor dari mesin 701 tidak tersedia');
                return;
            }

            const temperature = Number(mesin701.DATA.CH1).toFixed(1); // ensures 1 digit after dot
            const humidity = 0;

            const blocks = document.querySelectorAll('.inspection-block');
            if (blocks.length === 0) return;

            const lastBlock = blocks[blocks.length - 1];
            const tempInput = lastBlock.querySelector('input[name*="[3][temperature]"]');
            const humInput = lastBlock.querySelector('input[name*="[3][humidity]"]');

            if (tempInput) tempInput.value = temperature;
            if (humInput) humInput.value = humidity;

            // Feedback sukses
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-success');

            label.textContent = '✓ Berhasil disinkron!';
            icon.textContent = '';

            message.textContent = 'Data sensor berhasil disinkronisasi.';
            message.classList.remove('text-danger');
            message.classList.add('text-success');
            message.style.display = 'block';

            setTimeout(() => {
                label.textContent = 'Sync Data Sensor';
                icon.textContent = '🔄';
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-primary');
                button.disabled = false;
            }, 3000);
        })
        .catch(error => {
            alert('Gagal mengambil data dari sensor.');
            console.error(error);

            label.textContent = 'Sync Gagal';
            icon.textContent = '❌';

            message.textContent = 'Gagal menyinkronkan data sensor.';
            message.classList.remove('text-success');
            message.classList.add('text-danger');
            message.style.display = 'block';

            setTimeout(() => {
                label.textContent = 'Sync Data Sensor';
                icon.textContent = '🔄';
                button.disabled = false;
            }, 3000);
        });
});
</script>
@endsection