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

                <button type="button" id="add-inspection" class="btn btn-secondary mb-3 d-none">+ Tambah Detail
                    Inspeksi</button>
                <button type="submit" class="btn btn-primary">Simpan</button>

                <template id="inspection-template">
                    <div class="inspection-block border rounded p-3 mb-3 position-relative" data-index="__index__">
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
                                        <div class="d-flex gap-1">
                                            <input type="number" step="0.1"
                                                name="details[__index__][items][3][temperature]" placeholder="℃"
                                                class="form-control">
                                            <input type="number" step="0.1"
                                                name="details[__index__][items][3][humidity]" placeholder="RH%"
                                                class="form-control">
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
                                            class="form-control">
                                        @else
                                        @php
                                        $notesOptions = $i < 2 ? ['Sesuai','Penataan bahan tidak rapi','Penempatan bahan
                                            tidak sesuai dengan labelnya','Tidak ada label/tagging di tempat
                                            penyimpanan'] : ['Sesuai','Rak penyimpanan bahan kotor','Langit-langit
                                            kotor','Pintu kotor','Dinding kotor','Curving kotor','Curtain kotor','Lantai
                                            kotor/basah','Pallet kotor','Lampu + cover kotor','Exhaust fan
                                            kotor','Evaporator kotor','Temuan pest di area produksi','Temuan pest di
                                            dalam bahan']; @endphp <div>
                                            @foreach($notesOptions as $note)
                                            <div class="form-check">
                                                <input class="form-check-input note-checkbox" type="checkbox"
                                                    value="{{ $note }}" data-item="{{ $i }}">
                                                <label class="form-check-label small">{{ $note }}</label>
                                            </div>
                                            @endforeach
                                            <input type="hidden" name="details[__index__][items][{{ $i }}][notes]"
                                                id="notes-hidden-{{ $i }}">
                    </div>
                    @endif
                    </td>
                    <td>
                        <input type="text" name="details[__index__][items][{{ $i }}][corrective_action]"
                            id="corrective-{{ $i }}" class="form-control">
                    </td>
                    <td>
                        <select name="details[__index__][items][{{ $i }}][verification]"
                            class="form-control verification-select" data-item="{{ $i }}">
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

<input type="hidden" id="section-room" value="{{ $report->room_name }}">
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
                <option value="">-- Pilih --</option><option value="0">Tidak OK</option><option value="1">OK</option>
            </select>
        </div>
    </div>`;

    wrapper.insertAdjacentHTML('beforeend', html);
    const newSelect = wrapper.querySelectorAll('.followup-group')[count].querySelector('.followup-verification');
    newSelect.addEventListener('change', function() {
        const all = wrapper.querySelectorAll('.followup-group');
        const idx = Array.from(all).indexOf(this.closest('.followup-group'));
        if (this.value === '0' && all.length === idx + 1) addFollowupField(block, itemIndex);
        else if (this.value === '1')
            for (let i = all.length - 1; i > idx; i--) all[i].remove();
    });
}

document.getElementById('add-inspection').addEventListener('click', function() {
    const tpl = document.getElementById('inspection-template').innerHTML;
    const html = tpl.replace(/__index__/g, inspectionIndex);
    const container = document.createElement('div');
    container.innerHTML = html;
    container.dataset.index = inspectionIndex;

    // Bind checkbox
    container.querySelectorAll('.note-checkbox').forEach(chk => {
        chk.addEventListener('change', function() {
            const itemIndex = this.dataset.item;
            const block = this.closest('.inspection-block');
            const chks = block.querySelectorAll(`.note-checkbox[data-item="${itemIndex}"]`);
            const selected = Array.from(chks).filter(c => c.checked).map(c => c.value);
            const hidden = block.querySelector(`#notes-hidden-${itemIndex}`);
            hidden.value = JSON.stringify(selected);
            const corrective = block.querySelector(`#corrective-${itemIndex}`);
            corrective.value = selected.length > 0 ? (koreksiMap[selected[0]] || '') : '';
        });
    });

    // const room = document.getElementById('section-room').value.toLowerCase();
    // if (room.includes('chill')) {
    //     const rows = container.querySelectorAll('tbody tr');

    //     // Sembunyikan item Suhu & RH
    //     if (rows[6]) rows[6].style.display = 'none';

    //     // Tidak ada followup-row setelah item ke-4 → skip
    //     container.querySelectorAll('[name*="[3][temperature]"]').forEach(el => el.removeAttribute('required'));
    //     container.querySelectorAll('[name*="[3][humidity]"]').forEach(el => el.removeAttribute('required'));
    //     const notesField = container.querySelector('[name*="[3][notes]"]');
    //     if (notesField) notesField.removeAttribute('required');
    // }




    document.getElementById('inspection-details').appendChild(container);
    inspectionIndex++;
});
document.getElementById('add-inspection').click();

document.addEventListener('change', function(e) {
    const target = e.target;

    if (target.classList.contains('verification-select')) {
        const block = target.closest('.inspection-block');
        const itemIndex = parseInt(target.dataset.item);
        const wrapper = block.querySelectorAll('.followup-wrapper')[itemIndex];
        wrapper.innerHTML = '';
        if (target.value === '0') addFollowupField(block, itemIndex);
    }

    const autoFill = (itemIdx, cond, note, ca, ver) => {
        if (target.name.includes(`[${itemIdx}][condition]`)) {
            const block = target.closest('.inspection-block');
            const hidden = block.querySelector(`#notes-hidden-${itemIdx}`);
            const action = block.querySelector(`#corrective-${itemIdx}`);
            const veri = block.querySelector(`select[name$="[${itemIdx}][verification]"]`);
            const wrapper = block.querySelectorAll('.followup-wrapper')[itemIdx];
            if (target.value === cond) {
                hidden.value = JSON.stringify([note]);
                action.value = ca;
                veri.value = ver;
                wrapper.innerHTML = '';
                // centang checkbox sesuai note
                block.querySelectorAll(`.note-checkbox[data-item="${itemIdx}"]`).forEach(c => c.checked = (c
                    .value === note));
            } else {
                hidden.value = '[]';
                action.value = '';
                veri.value = '0';
                wrapper.innerHTML = '';
                addFollowupField(block, itemIdx);
                block.querySelectorAll(`.note-checkbox[data-item="${itemIdx}"]`).forEach(c => c.checked =
                    false);
            }
        }
    }
    autoFill(0, 'Tertata rapi', 'Sesuai', '-', '1');
    autoFill(1, 'Sesuai tagging dan jenis alergen', 'Sesuai', '-', '1');
    autoFill(2, 'Bersih dan bebas kontaminan', 'Sesuai', '-', '1');
});
</script>
@endsection