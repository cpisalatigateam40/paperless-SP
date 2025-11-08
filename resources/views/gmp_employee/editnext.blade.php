@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan Verifikasi GMP Karyawan & Kontrol Sanitasi (Jam Berikutnya)</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('gmp-employee.updatenext', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ $report->date ? \Carbon\Carbon::parse($report->date)->toDateString() : '' }}">
                </div>

                <div class="mb-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ $report->shift }}">
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="gmpTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail"
                            type="button" role="tab">GMP Karyawan</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="sanitasi-tab" data-bs-toggle="tab" data-bs-target="#sanitasi"
                            type="button" role="tab">Sanitasi Area</button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="gmpTabsContent">
                    {{-- Tab GMP Karyawan --}}
                    <div class="tab-pane fade show active" id="detail" role="tabpanel">
                        <div id="detail-container">
                            @foreach ($details as $i => $d)
                            <div class="detail-group border rounded p-3 mb-3" data-index="{{ $i }}">
                                <h6>Detail Inspeksi #{{ $i + 1 }}</h6>
                                <div class="mb-2">
                                    <label>Jam Inspeksi</label>
                                    <input type="time" name="details[{{ $i }}][inspection_hour]"
                                        class="form-control"
                                        value="{{ $d->inspection_hour ?? \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="mb-2">
                                    <label>Nama Bagian</label>
                                    <select name="details[{{ $i }}][section_name]" class="form-control">
                                        <option value="">-- Pilih Bagian --</option>
                                        <option value="MP" {{ $d->section_name == 'MP' ? 'selected' : '' }}>MP</option>
                                        <option value="Cooking" {{ $d->section_name == 'Cooking' ? 'selected' : '' }}>Cooking</option>
                                        <option value="Packing" {{ $d->section_name == 'Packing' ? 'selected' : '' }}>Packing</option>
                                        <option value="Cartoning" {{ $d->section_name == 'Cartoning' ? 'selected' : '' }}>Cartoning</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label>Nama Karyawan</label>
                                    <input type="text" name="details[{{ $i }}][employee_name]" class="form-control"
                                        value="{{ $d->employee_name }}">
                                </div>
                                <div class="mb-2">
                                    <label>Catatan</label>
                                    <input type="text" name="details[{{ $i }}][notes]" class="form-control"
                                        value="{{ $d->notes }}">
                                </div>
                                <div class="mb-2">
                                    <label>Tindakan Korektif</label>
                                    <input type="text" name="details[{{ $i }}][corrective_action]" class="form-control"
                                        value="{{ $d->corrective_action }}">
                                </div>
                                <div class="mb-2">
                                    <label>Verifikasi</label>
                                    <select name="details[{{ $i }}][verification]" class="form-control verification-select">
                                        <option value="">Pilih</option>
                                        <option value="1" {{ $d->verification == '1' ? 'selected' : '' }}>OK</option>
                                        <option value="0" {{ $d->verification == '0' ? 'selected' : '' }}>Tidak OK</option>
                                    </select>
                                </div>

                                {{-- Followups --}}
                                <div class="followup-wrapper">
                                    @if (!empty($d->followups))
                                        @foreach ($d->followups as $fi => $f)
                                        <div class="followup-group border rounded p-2 mb-2">
                                            <label class="small mb-1">Koreksi Lanjutan #{{ $fi + 1 }}</label>
                                            <input type="text" name="details[{{ $i }}][followups][{{ $fi }}][notes]"
                                                class="form-control mb-1" value="{{ $f->notes }}">
                                            <input type="text" name="details[{{ $i }}][followups][{{ $fi }}][action]"
                                                class="form-control mb-1" value="{{ $f->action }}">
                                            <select name="details[{{ $i }}][followups][{{ $fi }}][verification]"
                                                class="form-control followup-verification">
                                                <option value="">-- Pilih --</option>
                                                <option value="0" {{ $f->verification == '0' ? 'selected' : '' }}>Tidak OK</option>
                                                <option value="1" {{ $f->verification == '1' ? 'selected' : '' }}>OK</option>
                                            </select>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- <button type="button" id="add-detail" class="btn btn-sm btn-secondary mb-3">+ Tambah Detail
                            Inspeksi</button> -->
                    </div>

                    {{-- Tab Sanitasi --}}
                    <div class="tab-pane fade" id="sanitasi" role="tabpanel">
                        <div class="border rounded p-3">
                            <h6>Data Sanitasi</h6>
                            <div class="mb-2">
                                <label>Jam 1</label>
                                <input type="time" name="sanitation[hour_1]" class="form-control"
                                    value="{{ $sanitation->hour_1 ?? '' }}">
                            </div>
                            <div class="mb-2">
                                <label>Jam 2</label>
                                <input type="time" name="sanitation[hour_2]" class="form-control"
                                    value="{{ $sanitation->hour_2 ?? \Carbon\Carbon::now()->format('H:i') }}">
                            </div>

                            <hr>
                            <h6>Area Sanitasi</h6>

                            @foreach ($sanitationAreas as $index => $area)
                            <div class="border p-2 mb-3 sanitation-area-group">
                                <div class="mb-2">
                                    <label>Nama Area</label>
                                    <input type="text" name="sanitation_area[{{ $index }}][area_name]"
                                        class="form-control" value="{{ $area->area_name }}">
                                </div>
                                <div class="mb-2">
                                    <label>Standar Klorin</label>
                                    <input type="number" name="sanitation_area[{{ $index }}][chlorine_std]"
                                        class="form-control" value="{{ $area->chlorine_std }}">
                                </div>

                                <div class="d-flex mb-3" style="gap: 1rem">
                                    <div class="col-md-6">
                                        <p class="fw-bold mt-3">Jam 1</p>
                                        <label>Kadar Klorin</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][1][chlorine_level]"
                                            class="form-control mb-2"
                                            value="{{ $area->results_by_hour[1]->chlorine_level ?? '' }}">
                                        <label>Suhu</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][1][temperature]"
                                            class="form-control"
                                            value="{{ $area->results_by_hour[1]->temperature ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <p class="fw-bold mt-3">Jam 2</p>
                                        <label>Kadar Klorin</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][2][chlorine_level]"
                                            class="form-control mb-2"
                                            value="{{ $area->results_by_hour[2]->chlorine_level ?? '' }}">
                                        <label>Suhu</label>
                                        <input type="number"
                                            name="sanitation_area[{{ $index }}][result][2][temperature]"
                                            class="form-control"
                                            value="{{ $area->results_by_hour[2]->temperature ?? '' }}">
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label>Catatan</label>
                                    <input type="text" name="sanitation_area[{{ $index }}][notes]" class="form-control"
                                        value="{{ $area->notes }}">
                                </div>
                                <div class="mb-2">
                                    <label>Tindakan Korektif</label>
                                    <input type="text" name="sanitation_area[{{ $index }}][corrective_action]"
                                        class="form-control" value="{{ $area->corrective_action }}">
                                </div>
                                <div class="mb-2">
                                    <label>Verifikasi</label>
                                    <select name="sanitation_area[{{ $index }}][verification]"
                                        class="form-control sanitation-verification-select" data-index="{{ $index }}">
                                        <option value="">Pilih</option>
                                        <option value="1" {{ $area->verification == '1' ? 'selected' : '' }}>✔</option>
                                        <option value="0" {{ $area->verification == '0' ? 'selected' : '' }}>✘</option>
                                    </select>
                                </div>

                                {{-- Followups --}}
                                <div class="followup-wrapper" data-index="{{ $index }}">
                                    @if (!empty($area->followups))
                                        @foreach ($area->followups as $fi => $f)
                                        <div class="followup-group border rounded p-2 mb-2">
                                            <label class="small mb-1">Koreksi Lanjutan #{{ $fi + 1 }}</label>
                                            <input type="text" name="sanitation_area[{{ $index }}][followups][{{ $fi }}][notes]" class="form-control mb-1" value="{{ $f->notes }}">
                                            <input type="text" name="sanitation_area[{{ $index }}][followups][{{ $fi }}][action]" class="form-control mb-1" value="{{ $f->action }}">
                                            <select name="sanitation_area[{{ $index }}][followups][{{ $fi }}][verification]" class="form-control followup-verification">
                                                <option value="">-- Pilih --</option>
                                                <option value="0" {{ $f->verification == '0' ? 'selected' : '' }}>Tidak OK</option>
                                                <option value="1" {{ $f->verification == '1' ? 'selected' : '' }}>OK</option>
                                            </select>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Laporan</button>
            </form>
        </div>
    </div>
</div>

<script>
let detailIndex = 1; // kita sudah punya detail[0]

// Tombol tambah detail inspeksi
document.getElementById('add-detail').addEventListener('click', function() {
    const container = document.getElementById('detail-container');
    const firstGroup = container.querySelector('.detail-group');
    const clone = firstGroup.cloneNode(true);

    clone.setAttribute('data-index', detailIndex);

    // Update semua name & kosongkan value
    clone.querySelectorAll('input, select, textarea').forEach(input => {
        const oldName = input.getAttribute('name');
        if (oldName) {
            const newName = oldName.replace(/\[\d+\]/, `[${detailIndex}]`);
            input.setAttribute('name', newName);
        }

        if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        } else {
            input.value = '';
        }
    });

    // Kosongkan followup
    const wrapper = clone.querySelector('.followup-wrapper');
    if (wrapper) wrapper.innerHTML = '';

    container.appendChild(clone);
    detailIndex++;
});

// Fungsi tambah koreksi lanjutan
function addFollowupField(group) {
    const wrapper = group.querySelector('.followup-wrapper');
    const followupCount = wrapper.querySelectorAll('.followup-group').length;
    const detailIdx = group.getAttribute('data-index');

    const html = `
        <div class="followup-group border rounded p-2 mb-2">
            <label class="small mb-1">Koreksi Lanjutan #${followupCount + 1}</label>
            <input type="text" name="details[${detailIdx}][followups][${followupCount}][notes]" class="form-control mb-1" placeholder="Catatan">
            <input type="text" name="details[${detailIdx}][followups][${followupCount}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
            <select name="details[${detailIdx}][followups][${followupCount}][verification]" class="form-control followup-verification">
                <option value="">-- Pilih --</option>
                <option value="0">Tidak OK</option>
                <option value="1">OK</option>
            </select>
        </div>
    `;
    wrapper.insertAdjacentHTML('beforeend', html);

    const newSelect = wrapper.querySelectorAll('.followup-group')[followupCount].querySelector(
        '.followup-verification');
    newSelect.addEventListener('change', function() {
        const allFollowups = wrapper.querySelectorAll('.followup-group');
        const currentIndex = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

        if (this.value === '0') {
            // tambah lagi kalau ini yang terakhir
            if (allFollowups.length === currentIndex + 1) {
                addFollowupField(group);
            }
        } else {
            // kalau OK, hapus setelahnya
            for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                allFollowups[i].remove();
            }
        }
    });
}

// Event: ketika verifikasi utama berubah
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('verification-select')) {
        const group = e.target.closest('.detail-group');
        const wrapper = group.querySelector('.followup-wrapper');
        wrapper.innerHTML = '';

        if (e.target.value === '0') {
            addFollowupField(group);
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // untuk setiap select verifikasi
    const verificationSelects = document.querySelectorAll('.sanitation-verification-select');

    verificationSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            const index = this.dataset.index;
            const wrapper = document.querySelector('.followup-wrapper[data-index="' + index +
                '"]');
            wrapper.innerHTML = ''; // bersihkan dulu

            if (this.value === '0') {
                addFollowupField(wrapper, index);
            }
        });
    });

    function addFollowupField(wrapper, areaIndex) {
        const currentFollowups = wrapper.querySelectorAll('.followup-group');
        const nextIndex = currentFollowups.length;

        const html = `
            <div class="followup-group border rounded p-2 mb-2">
                <label class="small mb-1">Koreksi Lanjutan #${nextIndex + 1}</label>
                <input type="text" name="sanitation_area[${areaIndex}][followups][${nextIndex}][notes]" class="form-control mb-1" placeholder="Catatan">
                <input type="text" name="sanitation_area[${areaIndex}][followups][${nextIndex}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                <select name="sanitation_area[${areaIndex}][followups][${nextIndex}][verification]" class="form-control followup-verification">
                    <option value="">-- Pilih --</option>
                    <option value="1">✔</option>
                    <option value="0">✘</option>
                </select>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);

        const newSelect = wrapper.querySelectorAll('.followup-verification')[nextIndex];
        newSelect.addEventListener('change', function() {
            const allFollowups = wrapper.querySelectorAll('.followup-group');
            const currentIdx = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

            if (this.value === '0') {
                if (allFollowups.length === currentIdx + 1) {
                    addFollowupField(wrapper, areaIndex);
                }
            } else {
                for (let i = allFollowups.length - 1; i > currentIdx; i--) {
                    allFollowups[i].remove();
                }
            }
        });
    }
});
</script>
@endsection
