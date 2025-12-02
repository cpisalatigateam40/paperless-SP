@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Buat Laporan Verifikasi Kebersihan Ruangan, Mesin, dan Peralatan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report-re-cleanliness.store') }}" method="POST">
                @csrf
                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control mb-5"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="cleanTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#roomTab">Ruangan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#equipmentTab">Mesin & Peralatan</a>
                    </li>
                </ul>

                <div class="tab-content p-3 border border-top-0">
                    {{-- Tab Ruangan --}}
                    <div class="tab-pane fade show active" id="roomTab">
                        @foreach ($rooms as $room)
                        <div class="mb-3 border cleanliness-item" data-uuid="{{ $room->uuid }}">
                            {{-- Judul, diklik untuk toggle --}}
                            <h6 class="fw-bold m-3" style="cursor: pointer;" onclick="toggleSection(this)">
                                {{ $room->name }}
                            </h6>

                            {{-- Check All untuk ruangan ini --}}
                            <div class="m-3">
                                <label>
                                    <input type="checkbox" class="checkall-room" data-room="{{ $room->uuid }}">
                                    Centang Semua
                                </label>
                            </div>

                            {{-- Form yang disembunyikan --}}
                            <div class="form-wrapper" style="display: none; margin-top: 1rem; padding: 1rem;">
                                <input type="hidden" name="rooms[{{ $room->uuid }}][name]" value="{{ $room->name }}">
                                <div class="row room-elements-{{ $room->uuid }}">
                                    @foreach ($room->elements as $element)
                                    <div class="col-md-4 mb-4">
                                        <label>
                                            <input type="hidden"
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][condition]"
                                                value="dirty">
                                            <input type="checkbox" class="element-checkbox"
                                                data-room="{{ $room->uuid }}" onchange="toggleCleanlinessFields(this)"
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][condition]"
                                                value="clean">
                                            {{ $element->element_name }}
                                        </label>
                                        <div class="cleanliness-fields mt-2" style="display: none;">
                                            <input
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][notes]"
                                                class="form-control mb-2 notes" placeholder="Catatan jika kotor">
                                            <input
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][corrective_action]"
                                                class="form-control mb-2 corrective_action"
                                                placeholder="Tindakan koreksi">
                                            <select
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][verification]"
                                                class="form-control verification">
                                                <option value="">-- Pilih Verifikasi --</option>
                                                <option value="OK">OK</option>
                                                <option value="Tidak OK">Tidak OK</option>
                                            </select>
                                            <div class="followup-wrapper mt-2"></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if($room->elements->isEmpty())
                                <div class="m-3">
                                    <label>
                                        <input type="checkbox" name="rooms[{{ $room->uuid }}][condition]" value="clean">
                                        Ruangan Bersih
                                    </label>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tab Equipment --}}
                    <div class="tab-pane fade" id="equipmentTab">
                        @foreach ($equipments as $equipment)
                        <div class="mb-3 border cleanliness-item" data-uuid="{{ $equipment->uuid }}">
                            <h6 class="fw-bold m-3" style="cursor: pointer;" onclick="toggleSection(this)">
                                {{ $equipment->name }}
                            </h6>

                            {{-- Check All untuk equipment ini --}}
                            <div class="m-3">
                                <label>
                                    <input type="checkbox" class="checkall-equipment"
                                        data-equipment="{{ $equipment->uuid }}">
                                    Centang Semua Part
                                </label>
                            </div>

                            <div class="form-wrapper" style="display: none; margin-top: 1rem; padding: 1rem;">
                                <input type="hidden" name="equipments[{{ $equipment->uuid }}][name]"
                                    value="{{ $equipment->name }}">
                                <div class="row equipment-parts-{{ $equipment->uuid }}">
                                    @foreach ($equipment->parts as $part)
                                    <div class="col-md-4 mb-4">
                                        <label>
                                            <input type="hidden"
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][condition]"
                                                value="dirty">

                                            <input type="checkbox" class="part-checkbox"
                                                data-equipment="{{ $equipment->uuid }}"
                                                onchange="toggleCleanlinessFields(this)"
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][condition]"
                                                value="clean">
                                            {{ $part->part_name }}
                                        </label>
                                        <div class="cleanliness-fields mt-2" style="display: none;">
                                            <input
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][notes]"
                                                class="form-control mb-2 notes" placeholder="Catatan jika kotor">
                                            <input
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][corrective_action]"
                                                class="form-control mb-2 corrective_action"
                                                placeholder="Tindakan koreksi">
                                            <select
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][verification]"
                                                class="form-control verification">
                                                <option value="">-- Pilih Verifikasi --</option>
                                                <option value="OK">OK</option>
                                                <option value="Tidak OK">Tidak OK</option>
                                            </select>
                                            <div class="followup-wrapper mt-2"></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if($equipment->parts->isEmpty())
                                <div class="m-3">
                                    <label>
                                        <input type="checkbox" name="equipments[{{ $equipment->uuid }}][condition]"
                                            value="clean">
                                        Equipment Bersih
                                    </label>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>

                <button type="submit" class="btn btn-success mt-3">Simpan Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function toggleSection(headerEl) {
    const wrapper = headerEl.nextElementSibling;
    // safer toggle
    if (!wrapper) return;
    wrapper.style.display = (wrapper.style.display === 'block') ? 'none' : 'block';
}

function toggleCleanlinessFields(checkbox) {
    const container = checkbox.closest('.col-md-4');
    if (!container) return;
    const fields = container.querySelector('.cleanliness-fields');
    if (fields) fields.style.display = 'block';

    const notes = container.querySelector('.notes');
    const corrective = container.querySelector('.corrective_action');
    const verification = container.querySelector('.verification');

    if (checkbox.checked) {
        checkbox.value = 'clean'; // 👈 FIX PALING PENTING

        if (notes) notes.disabled = true;
        if (corrective) corrective.disabled = true;
        if (verification) verification.value = 'OK';
    } else {
        checkbox.value = 'dirty'; // 👈 FIX PALING PENTING

        if (notes) notes.disabled = false;
        if (corrective) corrective.disabled = false;
        if (verification) verification.value = '';
    }

    const roomOrEq = checkbox.closest('.cleanliness-item');
    if (roomOrEq) checkRoomCompletion(roomOrEq);
}


function checkRoomCompletion(roomWrapper) {
    const elements = roomWrapper.querySelectorAll('.col-md-4');
    let allFilled = true;

    elements.forEach(el => {
        const checkbox = el.querySelector('input[type="checkbox"]');
        const fields = el.querySelectorAll('.cleanliness-fields input, .cleanliness-fields select');

        if (!checkbox || !checkbox.checked) {
            allFilled = false;
            return;
        }

        const filled = Array.from(fields).every(input =>
            input.disabled || (String(input.value || '').trim() !== '')
        );

        if (!filled) allFilled = false;
    });

    roomWrapper.style.backgroundColor = allFilled ? '#d4edda' : '#fff3cd';
}

// Followup helper (tetap seperti sebelumnya)
function addFollowupField(wrapper, baseName) {
    const count = wrapper.querySelectorAll('.followup-group').length;

    const html = `
        <div class="followup-group border rounded p-2 mb-2">
            <label class="small mb-1">Koreksi Lanjutan #${count + 1}</label>
            <input type="text" name="${baseName}[followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan">
            <input type="text" name="${baseName}[followups][${count}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
            <select name="${baseName}[followups][${count}][verification]" class="form-control followup-verification">
                <option value="">-- Pilih Verifikasi --</option>
                <option value="OK">OK</option>
                <option value="Tidak OK">Tidak OK</option>
            </select>
        </div>
    `;
    wrapper.insertAdjacentHTML('beforeend', html);
}

// Event: verifikasi utama berubah -> tampilkan followup bila perlu, dan update warna
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('verification')) {
        const container = e.target.closest('.col-md-4');
        if (!container) return;
        const wrapper = container.querySelector('.followup-wrapper');
        const baseName = e.target.name.replace('[verification]', '');

        if (wrapper) wrapper.innerHTML = '';

        if (e.target.value.trim() !== 'OK' && e.target.value !== '') {
            if (wrapper) addFollowupField(wrapper, baseName);
        }
    }

    // followup-verification handler
    if (e.target.classList.contains('followup-verification')) {
        const currentGroup = e.target.closest('.followup-group');
        if (!currentGroup) return;
        const wrapper = currentGroup.parentElement;
        const allGroups = wrapper.querySelectorAll('.followup-group');
        const currentIndex = Array.from(allGroups).indexOf(currentGroup);

        if (e.target.value === 'Tidak OK') {
            if (allGroups.length === currentIndex + 1) {
                const baseName = e.target.name.replace(/\[followups\]\[\d+\]\[verification\]/, '');
                addFollowupField(wrapper, baseName);
            }
        } else if (e.target.value === 'OK') {
            for (let i = allGroups.length - 1; i > currentIndex; i--) {
                allGroups[i].remove();
            }
        }
    }

    // update room/equipment completion color
    const roomWrapper = e.target.closest('.cleanliness-item');
    if (roomWrapper) {
        checkRoomCompletion(roomWrapper);
    }
});

// CHECKALL handlers
document.addEventListener('change', function(e) {
    // Check all for rooms
    if (e.target.classList.contains('checkall-room')) {
        const roomId = e.target.dataset.room;
        const cleanlinessItem = document.querySelector(`.cleanliness-item[data-uuid="${roomId}"]`);
        const wrapper = cleanlinessItem ? cleanlinessItem.querySelector('.form-wrapper') : null;
        if (wrapper) wrapper.style.display = 'block';

        const checkboxes = document.querySelectorAll(`.room-elements-${roomId} .element-checkbox`);

        if (checkboxes.length > 0) {
            // Room dengan Elements
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
                toggleCleanlinessFields(cb);
            });
        } else {
            // Room TANPA Elements → centang condition clean
            const roomInput = cleanlinessItem.querySelector('input[name^="rooms"][name$="[condition]"]');
            if (roomInput) {
                roomInput.checked = e.target.checked;
            }
        }
    }

    // Check all for equipments
    if (e.target.classList.contains('checkall-equipment')) {
        const eqId = e.target.dataset.equipment;
        const cleanlinessItem = document.querySelector(`.cleanliness-item[data-uuid="${eqId}"]`);
        const wrapper = cleanlinessItem ? cleanlinessItem.querySelector('.form-wrapper') : null;
        if (wrapper) wrapper.style.display = 'block';

        const checkboxes = document.querySelectorAll(`.equipment-parts-${eqId} .part-checkbox`);

        if (checkboxes.length > 0) {
            // Equipment dengan Parts
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
                toggleCleanlinessFields(cb);
            });
        } else {
            // Equipment TANPA Parts → centang condition clean
            const eqInput = cleanlinessItem.querySelector('input[name^="equipments"][name$="[condition]"]');
            if (eqInput) {
                eqInput.checked = e.target.checked;
            }
        }
    }


    // Auto-update checkall when manual cek/unccek
    if (e.target.classList.contains('element-checkbox')) {
        const roomId = e.target.dataset.room;
        const all = document.querySelectorAll(`.room-elements-${roomId} .element-checkbox`);
        const checked = document.querySelectorAll(`.room-elements-${roomId} .element-checkbox:checked`);
        const checkall = document.querySelector(`.checkall-room[data-room="${roomId}"]`);
        if (checkall) checkall.checked = (all.length > 0 && all.length === checked.length);
    }

    if (e.target.classList.contains('part-checkbox')) {
        const eqId = e.target.dataset.equipment;
        const all = document.querySelectorAll(`.equipment-parts-${eqId} .part-checkbox`);
        const checked = document.querySelectorAll(`.equipment-parts-${eqId} .part-checkbox:checked`);
        const checkall = document.querySelector(`.checkall-equipment[data-equipment="${eqId}"]`);
        if (checkall) checkall.checked = (all.length > 0 && all.length === checked.length);
    }
});
</script>
@endsection