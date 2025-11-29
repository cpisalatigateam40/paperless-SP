@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan Verifikasi Kebersihan Ruang Penyimpanan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('cleanliness.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Header -->
                <div class="mb-4">
                    <div class="d-flex" style="gap: 1rem;">
                        <div class="col-md-5 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Tanggal:</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ \Carbon\Carbon::parse($report->date)->toDateString() }}" required>
                        </div>
                        <div class="col-md-5 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Shift:</label>
                            <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                        </div>
                    </div>

                    <label>Area (Room Name):</label>
                    <select name="room_name" id="roomSelect" class="form-control col-md-5 mb-5" required>
                        <option value="">-- Pilih Area --</option>
                        <option value="Seasoning" {{ $report->room_name == 'Seasoning' ? 'selected' : '' }}>Seasoning
                        </option>
                        <option value="Chillroom" {{ $report->room_name == 'Chillroom' ? 'selected' : '' }}>Chillroom
                        </option>
                    </select>
                </div>

                <!-- Detail -->
                <div id="inspection-details">
                    <h5 class="mb-3">Detail Inspeksi</h5>

                    @foreach($report->details as $i => $detail)
                    <div class="inspection-block border rounded p-3 mb-3 position-relative" data-index="{{ $i }}">

                        <label>Jam Inspeksi:</label>
                        <input type="time" name="details[{{ $i }}][inspection_hour]" class="form-control mb-3 col-md-5"
                            value="{{ $detail->inspection_hour }}" required>

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
                                @foreach($detail->items as $j => $item)
                                @if($item->item === 'Suhu ruang (℃) / RH (%)')
                                {{-- ITEM 4 KHUSUS SUHU --}}
                                <tr class="item-4-row">
                                    <td>{{ $j + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="details[{{ $i }}][items][{{ $j }}][item]"
                                            value="Suhu ruang (℃) / RH (%)">
                                        Suhu ruang (℃) / RH (%)
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1" style="gap: 1rem;">
                                            <input type="number" step="0.1"
                                                name="details[{{ $i }}][items][{{ $j }}][temperature]"
                                                value="{{ $item->temperature ?? '' }}" placeholder="℃"
                                                class="form-control">
                                            <input type="number" step="0.1"
                                                name="details[{{ $i }}][items][{{ $j }}][humidity]"
                                                value="{{ $item->humidity ?? '' }}" placeholder="RH%"
                                                class="form-control">
                                        </div>

                                        <!-- Optional: tombol sync sensor -->
                                        <!--
                    <button type="button" id="sync-sensor"
                        class="btn btn-outline-primary mb-3 mt-3 btn-sm w-100 d-flex justify-content-center align-items-center"
                        style="gap: .5rem;">
                        <span class="icon">🔄</span>
                        <span class="label">Sync Data Sensor</span>
                    </button>
                    -->

                                        <p id="sync-status-message" class="text-success text-center"
                                            style="display: none; margin-top: -.5rem;"></p>
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $i }}][items][{{ $j }}][notes]"
                                            value="{{ $item->notes ?? '' }}" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $i }}][items][{{ $j }}][corrective_action]"
                                            value="{{ $item->corrective_action ?? '' }}" class="form-control" required>
                                    </td>
                                    <td>
                                        <select name="details[{{ $i }}][items][{{ $j }}][verification]"
                                            class="form-control" required>
                                            <option value="0" {{ $item->verification == '0' ? 'selected' : '' }}>Tidak
                                                OK</option>
                                            <option value="1" {{ $item->verification == '1' ? 'selected' : '' }}>OK
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                @else
                                {{-- ITEM NORMAL --}}
                                <tr>
                                    <td>{{ $j + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="details[{{ $i }}][items][{{ $j }}][item]"
                                            value="{{ $item->item }}">
                                        {{ $item->item }}
                                    </td>
                                    <td>
                                        <select name="details[{{ $i }}][items][{{ $j }}][condition]"
                                            class="form-control" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Tertata rapi"
                                                {{ $item->condition == 'Tertata rapi' ? 'selected' : '' }}>Tertata rapi
                                            </option>
                                            <option value="Tidak tertata rapi"
                                                {{ $item->condition == 'Tidak tertata rapi' ? 'selected' : '' }}>Tidak
                                                tertata rapi</option>
                                            <option value="Sesuai tagging dan jenis alergen"
                                                {{ $item->condition == 'Sesuai tagging dan jenis alergen' ? 'selected' : '' }}>
                                                Sesuai tagging dan jenis alergen</option>
                                            <option value="Penempatan tidak sesuai"
                                                {{ $item->condition == 'Penempatan tidak sesuai' ? 'selected' : '' }}>
                                                Penempatan tidak sesuai</option>
                                            <option value="Bersih dan bebas kontaminan"
                                                {{ $item->condition == 'Bersih dan bebas kontaminan' ? 'selected' : '' }}>
                                                Bersih dan bebas kontaminan</option>
                                            <option value="Tidak bersih / ada kontaminan"
                                                {{ $item->condition == 'Tidak bersih / ada kontaminan' ? 'selected' : '' }}>
                                                Tidak bersih / ada kontaminan</option>
                                        </select>
                                    </td>
                                    <td>
                                        @php
                                        $selectedNotes = is_array($item->notes) ? $item->notes :
                                        json_decode($item->notes, true) ?? [];
                                        @endphp
                                        @foreach([
                                        'Sesuai',
                                        'Penataan bahan tidak rapi',
                                        'Penempatan bahan tidak sesuai dengan labelnya',
                                        'Tidak ada label/tagging di tempat penyimpanan',
                                        'Rak penyimpanan bahan kotor',
                                        'Langit-langit kotor',
                                        'Pintu kotor',
                                        'Dinding kotor',
                                        'Curving kotor',
                                        'Curtain kotor',
                                        'Lantai kotor/basah',
                                        'Pallet kotor',
                                        'Lampu + cover kotor',
                                        'Exhaust fan kotor',
                                        'Evaporator kotor',
                                        'Temuan pest di area produksi',
                                        'Temuan pest di dalam bahan'
                                        ] as $note)
                                        <div class="form-check">
                                            <input class="form-check-input note-checkbox" type="checkbox"
                                                name="details[{{ $i }}][items][{{ $j }}][notes][]" value="{{ $note }}"
                                                {{ in_array($note, $selectedNotes) ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $note }}</label>
                                        </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $i }}][items][{{ $j }}][corrective_action]"
                                            value="{{ $item->corrective_action }}" class="form-control">
                                    </td>
                                    <td>
                                        <select name="details[{{ $i }}][items][{{ $j }}][verification]"
                                            class="form-control" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="0" {{ $item->verification == '0' ? 'selected' : '' }}>Tidak
                                                OK</option>
                                            <option value="1" {{ $item->verification == '1' ? 'selected' : '' }}>OK
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                    @endforeach
                </div>

                <button type="button" id="add-inspection" class="btn btn-secondary mr-2 d-none">+ Tambah Detail
                    Inspeksi</button>
                <button type="submit" class="btn btn-primary">Perbarui</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
// document.addEventListener("DOMContentLoaded", function () {
//     const roomSelect = document.getElementById('roomSelect');
//     const item4Rows = document.querySelectorAll('.item-4-row');

//     function toggleItem4() {
//         const selected = roomSelect.value;
//         item4Rows.forEach(row => {
//             if (selected === "Chillroom") {
//                 row.classList.add('d-none');
//                 row.querySelectorAll('input,select').forEach(el => el.removeAttribute('required'));
//             } else {
//                 row.classList.remove('d-none');
//                 row.querySelectorAll('input,select').forEach(el => el.setAttribute('required', true));
//             }
//         });
//     }

//     // Jalankan saat halaman edit pertama kali dimuat
//     toggleItem4();

//     // Jalankan jika user mengganti area
//     roomSelect.addEventListener('change', toggleItem4);
// });


document.addEventListener('DOMContentLoaded', function() {
    // Pemetaan tindakan koreksi otomatis
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

    // Tambahkan wrapper followup ke tiap item agar script addFollowupField tetap berfungsi
    document.querySelectorAll('.inspection-block tbody tr').forEach((tr, idx) => {
        if (!tr.nextElementSibling || !tr.nextElementSibling.classList.contains('followup-row')) {
            const row = document.createElement('tr');
            row.classList.add('followup-row');
            row.innerHTML = `<td colspan="6"><div class="followup-wrapper"></div></td>`;
            tr.insertAdjacentElement('afterend', row);
        }
    });

    // Tambah data-item pada semua checkbox agar koreksi otomatis bisa jalan
    document.querySelectorAll('.inspection-block').forEach((block, i) => {
        block.querySelectorAll('tbody tr').forEach((tr, j) => {
            tr.querySelectorAll('.note-checkbox').forEach(chk => {
                chk.dataset.item = j;
            });
            const corrective = tr.querySelector('input[name*="[corrective_action]"]');
            if (corrective) corrective.id = 'corrective-' + j;
        });
    });

    // Fungsi menambahkan field tindak lanjut
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

        const newSelect = wrapper.querySelectorAll('.followup-group')[count].querySelector(
            '.followup-verification');
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

    // Checkbox => koreksi otomatis
    document.addEventListener('change', function(e) {
        const target = e.target;

        if (target.classList.contains('note-checkbox')) {
            const block = target.closest('.inspection-block');
            const itemIndex = target.dataset.item;
            const corrective = block.querySelector('#corrective-' + itemIndex);
            const checked = block.querySelectorAll(`.note-checkbox[data-item="${itemIndex}"]:checked`);
            const unique = new Set();
            checked.forEach(c => {
                const text = koreksiMap[c.value];
                if (text) unique.add(text);
            });
            corrective.value = Array.from(unique).join(', ');
        }

        // verifikasi → tambah followup
        if (target.classList.contains('verification-select')) {
            const block = target.closest('.inspection-block');
            const itemIndex = parseInt(target.closest('tr').rowIndex) - 1;
            const wrapper = block.querySelectorAll('.followup-wrapper')[itemIndex];
            wrapper.innerHTML = '';
            if (target.value === '0') addFollowupField(block, itemIndex);
        }

        // kondisi → auto isi koreksi
        const autoFill = (itemIdx, conditionMatch, correctiveValue, verificationValue) => {
            if (target.name.includes(`[${itemIdx}][condition]`)) {
                const block = target.closest('.inspection-block');
                const corrective = block.querySelector(
                    `input[name$="[${itemIdx}][corrective_action]"]`);
                const verification = block.querySelector(
                    `select[name$="[${itemIdx}][verification]"]`);
                const wrapper = block.querySelectorAll('.followup-wrapper')[itemIdx];

                if (target.value === conditionMatch) {
                    corrective.value = correctiveValue;
                    verification.value = verificationValue;
                    wrapper.innerHTML = '';
                } else {
                    corrective.value = '';
                    verification.value = '0';
                    wrapper.innerHTML = '';
                    addFollowupField(block, itemIdx);
                }
            }
        };
        autoFill(0, 'Tertata rapi', '-', '1');
        autoFill(1, 'Sesuai tagging dan jenis alergen', '-', '1');
        autoFill(2, 'Bersih dan bebas kontaminan', '-', '1');
    });
});
</script>
@endsection