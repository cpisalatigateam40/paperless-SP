@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Tambah Detail Inspeksi</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('cleanliness.detail.store', $report->id) }}" method="POST">
                @csrf

                <div id="inspection-details"></div>

                <button type="button" id="add-inspection" class="btn btn-secondary mb-3">+ Tambah Detail
                    Inspeksi</button>
                <button type="submit" class="btn btn-primary">Simpan</button>

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
                                @for ($i = 0; $i < 4; $i++) <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="details[__index__][items][{{ $i }}][item]"
                                            value="{{ $i == 0 ? 'Kondisi dan penempatan barang' : ($i == 1 ? 'Pelabelan' : ($i == 2 ? 'Kebersihan Ruangan' : 'Suhu ruang (℃) / RH (%)')) }}">
                                        {{ $i == 0 ? 'Kondisi dan penempatan barang' : ($i == 1 ? 'Pelabelan' : ($i == 2 ? 'Kebersihan Ruangan' : 'Suhu ruang (℃) / RH (%)')) }}
                                    </td>
                                    <td>
                                        @if ($i == 3)
                                        <div class="d-flex gap-1" style="gap: 1rem;">
                                            <input type="number" step="0.1"
                                                name="details[__index__][items][3][temperature]" placeholder="℃"
                                                class="form-control" required>
                                            <input type="number" step="0.1"
                                                name="details[__index__][items][3][humidity]" placeholder="RH%"
                                                class="form-control" required>
                                        </div>
                                        @else
                                        <select name="details[__index__][items][{{ $i }}][condition]"
                                            class="form-control" required>
                                            <option value="">-- Pilih --</option>
                                            @if ($i == 0)
                                            <option value="Tertata rapi">Tertata rapi</option>
                                            <option value="Tidak tertata rapi">Tidak tertata rapi</option>
                                            @elseif ($i == 1)
                                            <option value="Sesuai tagging dan jenis alergen">Sesuai tagging dan jenis
                                                alergen</option>
                                            <option value="Penempatan tidak sesuai">Penempatan tidak sesuai</option>
                                            @elseif ($i == 2)
                                            <option value="Bersih dan bebas kontaminan">Bersih dan bebas kontaminan
                                            </option>
                                            <option value="Tidak bersih / ada kontaminan">Tidak bersih / ada kontaminan
                                            </option>
                                            @endif
                                        </select>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($i == 3)
                                        <input type="text" name="details[__index__][items][{{ $i }}][notes]"
                                            class="form-control" required>
                                        @else
                                        <select name="details[__index__][items][{{ $i }}][notes]"
                                            class="form-control note-select" data-item="{{ $i }}" required>
                                            <option value="">-- Pilih Catatan --</option>
                                            @if ($i < 2) <option value="Sesuai">Sesuai</option>
                                                <option value="Penataan bahan tidak rapi">Penataan bahan tidak rapi
                                                </option>
                                                <option value="Penempatan bahan tidak sesuai dengan labelnya">Penempatan
                                                    bahan tidak sesuai dengan labelnya</option>
                                                <option value="Tidak ada label/tagging di tempat penyimpanan">Tidak ada
                                                    label/tagging di tempat penyimpanan</option>
                                                @else
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
                                                <option value="Temuan pest di area produksi">Temuan pest di area
                                                    produksi</option>
                                                <option value="Temuan pest di dalam bahan">Temuan pest di dalam bahan
                                                </option>
                                                @endif
                                        </select>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" name="details[__index__][items][{{ $i }}][corrective_action]"
                                            id="corrective-{{ $i }}" class="form-control">
                                    </td>
                                    <td>
                                        <select name="details[__index__][items][{{ $i }}][verification]"
                                            class="form-control verification-select" data-item="{{ $i }}" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                    </tr>
                                    @if($i < 3) <tr class="followup-row">
                                        <td colspan="6">
                                            <div class="followup-wrapper"></div>
                                        </td>
                                        </tr>
                                        @endif
                                        @endfor
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

document.addEventListener('change', function(e) {
    const target = e.target;

    if (target.classList.contains('note-select')) {
        const selected = target.value;
        const itemIndex = target.dataset.item;
        const corrective = document.getElementById('corrective-' + itemIndex);
        if (corrective) corrective.value = koreksiMap[selected] || '';
    }

    if (target.classList.contains('verification-select')) {
        const block = target.closest('.inspection-block');
        const itemIndex = parseInt(target.dataset.item);
        const wrapper = block.querySelectorAll('.followup-wrapper')[itemIndex];
        wrapper.innerHTML = '';
        if (target.value === '0') {
            addFollowupField(block, itemIndex);
        }
    }

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
</script>
@endsection