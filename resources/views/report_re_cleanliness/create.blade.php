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
                        <div class="mb-3 border cleanliness-item">
                            {{-- Judul, diklik untuk toggle --}}
                            <h6 class="fw-bold m-3" style="cursor: pointer;" onclick="toggleSection(this)">
                                {{ $room->name }}
                            </h6>

                            {{-- Form yang disembunyikan --}}
                            <div class="form-wrapper" style="display: none; margin-top: 1rem; padding: 1rem;">
                                <input type="hidden" name="rooms[{{ $room->uuid }}][name]" value="{{ $room->name }}">
                                <div class="row">
                                    @foreach ($room->elements as $element)
                                    <div class="col-md-4 mb-4">
                                        <label>
                                            <input type="checkbox" onchange="toggleCleanlinessFields(this)"
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
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tab Equipment --}}
                    <div class="tab-pane fade" id="equipmentTab">
                        @foreach ($equipments as $equipment)
                        <div class="mb-3 border cleanliness-item">
                            <h6 class="fw-bold m-3" style="cursor: pointer;" onclick="toggleSection(this)">
                                {{ $equipment->name }}
                            </h6>

                            <div class="form-wrapper" style="display: none; margin-top: 1rem; padding: 1rem;">
                                <input type="hidden" name="equipments[{{ $equipment->uuid }}][name]"
                                    value="{{ $equipment->name }}">
                                <div class="row">
                                    @foreach ($equipment->parts as $part)
                                    <div class="col-md-4 mb-4">
                                        <label>
                                            <input type="checkbox" onchange="toggleCleanlinessFields(this)"
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
function toggleRoomElementFields(checkbox) {
    let container = checkbox.closest('.col-md-4');
    let notes = container.querySelector('.notes');
    let corrective = container.querySelector('.corrective_action');
    let verification = container.querySelector('.verification');

    if (checkbox.checked) {
        // Bersih
        notes.disabled = true;
        corrective.disabled = true;
        verification.value = "OK";
    } else {
        // Kotor
        notes.disabled = false;
        corrective.disabled = false;
        verification.value = "";
    }
}

function toggleSection(headerEl) {
    const wrapper = headerEl.nextElementSibling;
    if (wrapper.style.display === 'none') {
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
    }
}

function toggleCleanlinessFields(checkbox) {
    const container = checkbox.closest('.col-md-4');
    const fields = container.querySelector('.cleanliness-fields');
    const notes = fields.querySelector('.notes');
    const corrective = fields.querySelector('.corrective_action');
    const verification = fields.querySelector('.verification');

    fields.style.display = 'block';

    if (checkbox.checked) {
        notes.disabled = true;
        corrective.disabled = true;
        verification.value = 'OK';
    } else {
        notes.disabled = false;
        corrective.disabled = false;
        verification.value = '';
    }

    checkRoomCompletion(checkbox.closest('.cleanliness-item'));
}


document.addEventListener('input', function(e) {
    const roomWrapper = e.target.closest('.cleanliness-item');
    if (roomWrapper) {
        checkRoomCompletion(roomWrapper);
    }
});

function checkRoomCompletion(roomWrapper) {
    const elements = roomWrapper.querySelectorAll('.col-md-4');
    let allFilled = true;

    elements.forEach(el => {
        const checkbox = el.querySelector('input[type="checkbox"]');
        const fields = el.querySelectorAll('.cleanliness-fields input');

        if (!checkbox.checked) {
            allFilled = false;
            return;
        }

        const filled = Array.from(fields).every(input =>
            input.disabled || input.value.trim() !== ''
        );

        if (!filled) allFilled = false;
    });

    roomWrapper.style.backgroundColor = allFilled ? '#d4edda' : '#fff3cd';
}

// additional

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

// Saat verifikasi utama berubah
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('verification')) {
        const container = e.target.closest('.col-md-4');
        const wrapper = container.querySelector('.followup-wrapper');
        const baseName = e.target.name.replace('[verification]', '');

        wrapper.innerHTML = ''; // reset followups

        if (e.target.value.trim() !== 'OK' && e.target.value !== '') {
            addFollowupField(wrapper, baseName);
        }
    }

    // Tetap update warna room/equipment
    const roomWrapper = e.target.closest('.cleanliness-item');
    if (roomWrapper) {
        checkRoomCompletion(roomWrapper);
    }
});

// Saat verifikasi followup berubah
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('followup-verification')) {
        const currentGroup = e.target.closest('.followup-group');
        const wrapper = currentGroup.parentElement;
        const baseName = e.target.name.replace(/\[followups\]\[\d+\]\[verification\]/, '');

        const allGroups = wrapper.querySelectorAll('.followup-group');
        const currentIndex = Array.from(allGroups).indexOf(currentGroup);

        if (e.target.value === 'Tidak OK') {
            // Tambahkan followup berikutnya hanya kalau belum ada
            if (allGroups.length === currentIndex + 1) {
                addFollowupField(wrapper, baseName);
            }
        } else if (e.target.value === 'OK') {
            // Hapus followup setelah current
            for (let i = allGroups.length - 1; i > currentIndex; i--) {
                allGroups[i].remove();
            }
        }
    }
});
</script>
@endsection