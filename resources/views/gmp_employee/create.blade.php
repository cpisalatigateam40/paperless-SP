@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Buat Laporan GMP Karyawan & Kontrol Sanitasi</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('gmp-employee.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>

                <div class="mb-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" required>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="gmpTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail"
                            type="button" role="tab">
                            GMP Karyawan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="sanitasi-tab" data-bs-toggle="tab" data-bs-target="#sanitasi"
                            type="button" role="tab">
                            Sanitasi Area
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="gmpTabsContent">
                    {{-- Tab Detail Inspeksi --}}
                    <div class="tab-pane fade show active" id="detail" role="tabpanel">
                        <div id="detail-container">
                            <div class="detail-group border rounded p-3 mb-3" data-index="0">
                                <h6>Detail Inspeksi</h6>
                                <div class="mb-2">
                                    <label>Jam Inspeksi</label>
                                    <input type="time" name="details[0][inspection_hour]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="mb-2">
                                    <label>Nama Bagian</label>
                                    <select name="details[0][section_name]" class="form-control">
                                        <option value="">-- Pilih Bagian --</option>
                                        <option value="MP">MP</option>
                                        <option value="Cooking">Cooking</option>
                                        <option value="Packing">Packing</option>
                                        <option value="Cartoning">Cartoning</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label>Nama Karyawan</label>
                                    <input type="text" name="details[0][employee_name]" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Catatan</label>
                                    <input type="text" name="details[0][notes]" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Tindakan Korektif</label>
                                    <input type="text" name="details[0][corrective_action]" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Verifikasi</label>
                                    <select name="details[0][verification]" class="form-control verification-select">
                                        <option value="">Pilih</option>
                                        <option value="1">OK</option>
                                        <option value="0">Tidak OK</option>
                                    </select>
                                </div>
                                <div class="followup-wrapper"></div>
                            </div>
                        </div>
                        <button type="button" id="add-detail" class="btn btn-sm btn-secondary mb-3">+ Tambah Detail
                            Inspeksi</button>
                    </div>

                    {{-- Tab Sanitasi Area --}}
                    <div class="tab-pane fade" id="sanitasi" role="tabpanel">
                        <div class="border rounded p-3">
                            <h6>Data Sanitasi</h6>
                            <div class="mb-2">
                                <label>Jam 1</label>
                                <input type="time" name="sanitation[hour_1]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}" {{ $isEdit ? 'disabled' : '' }}>
                            </div>
                            <div class="mb-2">
                                <label>Jam 2</label>
                                <input type="time" name="sanitation[hour_2]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}" {{ !$isEdit ? 'disabled' : '' }}>
                            </div>

                            <hr>
                            @php
                            $areaList = [
                            ['name' => 'Foot Basin', 'chlorine_std' => 200],
                            ['name' => 'Hand Basin', 'chlorine_std' => 50],
                            ['name' => 'Air Cuci Tangan', 'chlorine_std' => null],
                            ['name' => 'Air Cleaning', 'chlorine_std' => null],
                            ];
                            @endphp
                            <h6>Area Sanitasi</h6>

                            @foreach ($areaList as $index => $area)
                            <div class="border p-2 mb-3 sanitation-area-group">
                                <div class="mb-2">
                                    <label>Nama Area</label>
                                    <input type="text" name="sanitation_area[{{ $index }}][area_name]"
                                        class="form-control" value="{{ $area['name'] }}" readonly>
                                </div>
                                <div class="mb-2">
                                    <label>Standar Klorin</label>
                                    <input type="number" name="sanitation_area[{{ $index }}][chlorine_std]"
                                        class="form-control" value="{{ $area['chlorine_std'] }}">
                                </div>

                                <div class="d-flex" style="gap: 1rem">
                                    <div class="col-md-6">
                                        <p style="margin-top: 2rem; font-weight: bold;">Hasil pengecekan awal shift</p>
                                        <div class="mb-2">
                                            <label>Kadar Klorin</label>
                                            <input type="number"
                                                name="sanitation_area[{{ $index }}][result][1][chlorine_level]"
                                                class="form-control" {{ $isEdit ? 'disabled' : '' }}>
                                        </div>
                                        <div class="mb-2">
                                            <label>Suhu</label>
                                            <input type="number"
                                                name="sanitation_area[{{ $index }}][result][1][temperature]"
                                                class="form-control" {{ $isEdit ? 'disabled' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <p style="margin-top: 2rem; font-weight: bold;">Hasil pengecekan setelah
                                            istirahat</p>
                                        <div class="mb-2">
                                            <label>Kadar Klorin</label>
                                            <input type="number"
                                                name="sanitation_area[{{ $index }}][result][2][chlorine_level]"
                                                class="form-control" {{ !$isEdit ? 'disabled' : '' }}>
                                        </div>
                                        <div class="mb-2">
                                            <label>Suhu</label>
                                            <input type="number"
                                                name="sanitation_area[{{ $index }}][result][2][temperature]"
                                                class="form-control" {{ !$isEdit ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label>Catatan</label>
                                    <input type="text" name="sanitation_area[{{ $index }}][notes]" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Tindakan Korektif</label>
                                    <input type="text" name="sanitation_area[{{ $index }}][corrective_action]"
                                        class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Verifikasi</label>
                                    <select name="sanitation_area[{{ $index }}][verification]"
                                        class="form-control sanitation-verification-select" data-index="{{ $index }}">
                                        <option value="">Pilih</option>
                                        <option value="1">✔</option>
                                        <option value="0">✘</option>
                                    </select>
                                </div>

                                {{-- Tempat koreksi lanjutan --}}
                                <div class="followup-wrapper" data-index="{{ $index }}"></div>
                            </div>
                            @endforeach

                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
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