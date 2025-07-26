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
                            <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
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
                    <div class="inspection-block border rounded p-3 mb-3 position-relative" data-index="__index__">
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
                                        <select name="details[__index__][items][1][verification]" data-item="1"
                                            class="form-control verification-select" required>
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
                                        <select name="details[__index__][items][2][verification]"
                                            class="form-control verification-select" data-item="2" required>
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

                                        <button type="button" id="sync-sensor"
                                            class="btn btn-outline-primary mb-3 mt-3 btn-sm w-100 d-flex justify-content-center align-items-center"
                                            style="gap: .5rem;">
                                            <span class="icon">🔄</span>
                                            <span class="label">Sync Data Sensor</span>
                                        </button>
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

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endsection

@section('script')
<script>
let inspectionIndex = 0;

document.getElementById('add-inspection').addEventListener('click', function() {
    const template = document.getElementById('inspection-template').innerHTML;
    const rendered = template.replace(/__index__/g, inspectionIndex);
    const container = document.createElement('div');
    container.innerHTML = rendered;

    // Bind ulang untuk setiap note-select di template baru
    container.querySelectorAll('.note-select').forEach(select => {
        select.addEventListener('change', function(e) {
            const selected = e.target.value;
            const itemIndex = e.target.dataset.item;
            const corrective = container.querySelector('#corrective-' + itemIndex);
            if (corrective) {
                corrective.value = koreksiMap[selected] || '';
            }
        });
    });

    document.getElementById('inspection-details').appendChild(container);
    inspectionIndex++;
});


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

document.addEventListener('change', function(e) {
    const target = e.target;

    // auto koreksi dari note
    if (target.classList.contains('note-select')) {
        const selected = target.value;
        const itemIndex = target.dataset.item;
        const corrective = document.getElementById('corrective-' + itemIndex);
        corrective.value = koreksiMap[selected] || '';
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
    const autoFill = (itemIdx, conditionMatch, noteValue, correctiveValue, verificationValue) => {
        if (target.name.includes(`[${itemIdx}][condition]`)) {
            const block = target.closest('.inspection-block');
            const noteField = block.querySelector(`select[name$="[${itemIdx}][notes]"]`);
            const actionField = block.querySelector(`input[name$="[${itemIdx}][corrective_action]"]`);
            const verificationField = block.querySelector(`select[name$="[${itemIdx}][verification]"]`);
            const wrapper = block.querySelectorAll('.followup-wrapper')[itemIdx];

            if (target.value === conditionMatch) {
                noteField.value = noteValue;
                actionField.value = correctiveValue;
                verificationField.value = verificationValue;
                wrapper.innerHTML = '';
            } else {
                noteField.value = '';
                actionField.value = '';
                verificationField.value = '0';
                wrapper.innerHTML = '';
                addFollowupField(block, itemIdx);
            }
        }
    }

    autoFill(0, 'Tertata rapi', 'Sesuai', '-', '1');
    autoFill(1, 'Sesuai tagging dan jenis alergen', 'Sesuai', '-', '1');
    autoFill(2, 'Bersih dan bebas kontaminan', 'Sesuai', '-', '1');
});

document.getElementById('sync-sensor').addEventListener('click', function() {
    const button = this;
    const label = button.querySelector('.label');
    const icon = button.querySelector('.icon');

    // Ganti jadi loading
    label.textContent = 'Menyinkronkan...';
    icon.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`;
    button.disabled = true;

    axios.get('http://10.68.1.220:3003/api/sensor/last')
        .then(response => {
            const mesin301 = response.data.data["301"];
            if (!mesin301 || !mesin301.DATA || mesin301.DATA.TEMPERATURE === undefined) {
                alert('Data suhu dari sensor 301 tidak tersedia');
                return;
            }

            const temperature = mesin301.DATA.TEMPERATURE;
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

            setTimeout(() => {
                label.textContent = 'Sync Data Sensor';
                icon.textContent = '🔄';
                button.disabled = false;
            }, 3000);
        });
});
</script>
@endsection