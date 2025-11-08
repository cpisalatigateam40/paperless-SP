@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Kebersihan Ruangan, Mesin, dan Peralatan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report-re-cleanliness.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control mb-5"
                            value="{{ $report->date }}" required>
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
                        @php
                            $roomDetails = $report->roomDetails->where('room_uuid', $room->uuid);
                        @endphp
                        <div class="mb-3 border cleanliness-item" data-uuid="{{ $room->uuid }}">
                            <h6 class="fw-bold m-3" style="cursor: pointer;" onclick="toggleSection(this)">
                                {{ $room->name }}
                            </h6>

                            <div class="m-3">
                                <label>
                                    <input type="checkbox" class="checkall-room" data-room="{{ $room->uuid }}">
                                    Centang Semua
                                </label>
                            </div>

                            <div class="form-wrapper" style="display:block; margin-top: 1rem; padding: 1rem;">
                                <div class="row room-elements-{{ $room->uuid }}">
                                    @foreach ($room->elements as $element)
                                    @php
                                        $existing = $roomDetails->where('room_element_uuid', $element->uuid)->first();
                                    @endphp
                                    <div class="col-md-4 mb-4">
                                        <label>
                                            <input type="checkbox"
                                                class="element-checkbox"
                                                data-room="{{ $room->uuid }}"
                                                onchange="toggleCleanlinessFields(this)"
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][condition]"
                                                value="clean"
                                                {{ $existing && $existing->condition == 'clean' ? 'checked' : '' }}>
                                            {{ $element->element_name }}
                                        </label>
                                        <div class="cleanliness-fields mt-2" style="display:block;">
                                            <input name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][notes]"
                                                class="form-control mb-2 notes"
                                                value="{{ $existing->notes ?? '' }}"
                                                placeholder="Catatan jika kotor">

                                            <input
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][corrective_action]"
                                                class="form-control mb-2 corrective_action"
                                                value="{{ $existing->corrective_action ?? '' }}"
                                                placeholder="Tindakan koreksi">

                                            <select
                                                name="rooms[{{ $room->uuid }}][elements][{{ $element->uuid }}][verification]"
                                                class="form-control verification">
                                                <option value="">-- Pilih Verifikasi --</option>
                                                <option value="OK" {{ $existing && $existing->verification == 'OK' ? 'selected' : '' }}>OK</option>
                                                <option value="Tidak OK" {{ $existing && $existing->verification == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                            </select>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tab Equipment --}}
                    <div class="tab-pane fade" id="equipmentTab">
                        @foreach ($equipments as $equipment)
                        @php
                            $equipmentDetails = $report->equipmentDetails->where('equipment_uuid', $equipment->uuid);
                        @endphp
                        <div class="mb-3 border cleanliness-item" data-uuid="{{ $equipment->uuid }}">
                            <h6 class="fw-bold m-3" style="cursor: pointer;" onclick="toggleSection(this)">
                                {{ $equipment->name }}
                            </h6>

                            <div class="m-3">
                                <label>
                                    <input type="checkbox" class="checkall-equipment" data-equipment="{{ $equipment->uuid }}">
                                    Centang Semua
                                </label>
                            </div>

                            <div class="form-wrapper" style="display:block; margin-top: 1rem; padding: 1rem;">
                                <div class="row equipment-parts-{{ $equipment->uuid }}">
                                    @foreach ($equipment->parts as $part)
                                    @php
                                        $exist = $equipmentDetails->where('equipment_part_uuid', $part->uuid)->first();
                                    @endphp
                                    <div class="col-md-4 mb-4">
                                        <label>
                                            <input type="checkbox"
                                                class="part-checkbox"
                                                data-equipment="{{ $equipment->uuid }}"
                                                onchange="toggleCleanlinessFields(this)"
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][condition]"
                                                value="clean"
                                                {{ $exist && $exist->condition == 'clean' ? 'checked' : '' }}>
                                            {{ $part->part_name }}
                                        </label>
                                        <div class="cleanliness-fields mt-2" style="display:block;">
                                            <input
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][notes]"
                                                class="form-control mb-2 notes"
                                                value="{{ $exist->notes ?? '' }}"
                                                placeholder="Catatan jika kotor">

                                            <input
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][corrective_action]"
                                                class="form-control mb-2 corrective_action"
                                                value="{{ $exist->corrective_action ?? '' }}"
                                                placeholder="Tindakan koreksi">

                                            <select
                                                name="equipments[{{ $equipment->uuid }}][parts][{{ $part->uuid }}][verification]"
                                                class="form-control verification">
                                                <option value="">-- Pilih Verifikasi --</option>
                                                <option value="OK" {{ $exist && $exist->verification == 'OK' ? 'selected' : '' }}>OK</option>
                                                <option value="Tidak OK" {{ $exist && $exist->verification == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                            </select>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>

                <button type="submit" class="btn btn-primary mt-3">Update Laporan</button>
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
        if (notes) notes.disabled = true;
        if (corrective) corrective.disabled = true;
        if (verification) verification.value = 'OK';
    } else {
        if (notes) {
            notes.disabled = false;
            notes.value = notes.value ?? notes.value;
        }
        if (corrective) {
            corrective.disabled = false;
        }
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
        const checkboxes = document.querySelectorAll(`.room-elements-${roomId} .element-checkbox`);
        const cleanlinessItem = document.querySelector(`.cleanliness-item[data-uuid="${roomId}"]`);
        const wrapper = cleanlinessItem ? cleanlinessItem.querySelector('.form-wrapper') : null;
        if (wrapper) wrapper.style.display = 'block'; // buka jika tersembunyi

        checkboxes.forEach(cb => {
            if (cb.checked !== e.target.checked) {
                cb.checked = e.target.checked;
                toggleCleanlinessFields(cb);
            } else {
                // tetap panggil untuk memastikan UI konsisten
                toggleCleanlinessFields(cb);
            }
        });
    }

    // Check all for equipments
    if (e.target.classList.contains('checkall-equipment')) {
        const eqId = e.target.dataset.equipment;
        const checkboxes = document.querySelectorAll(`.equipment-parts-${eqId} .part-checkbox`);
        const cleanlinessItem = document.querySelector(`.cleanliness-item[data-uuid="${eqId}"]`);
        const wrapper = cleanlinessItem ? cleanlinessItem.querySelector('.form-wrapper') : null;
        if (wrapper) wrapper.style.display = 'block';

        checkboxes.forEach(cb => {
            if (cb.checked !== e.target.checked) {
                cb.checked = e.target.checked;
                toggleCleanlinessFields(cb);
            } else {
                toggleCleanlinessFields(cb);
            }
        });
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
