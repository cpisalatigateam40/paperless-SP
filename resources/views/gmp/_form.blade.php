@php
    $isEdit = isset($gmpHeader);
    $section = old('section', $gmpHeader->section ?? '');

    $existingWaktu = [];
    if ($isEdit) {
        $existingWaktu = $gmpHeader->waktuPemeriksaans->map(function ($w) use ($gmpHeader) {
            return [
                'jam_pemeriksaan' => $w->jam_pemeriksaan ? substr($w->jam_pemeriksaan, 0, 5) : null,
                'catatan' => $w->catatan,
                'employees' => $gmpHeader->section === 'gmp_karyawan'
                    ? $w->employeeChecks->map(fn ($e) => array_merge($e->toArray(), [
                        'section_uuid' => $e->section_uuid,
                    ]))->values()->all()
                    : [],
                'sanitations' => $gmpHeader->section === 'sanitasi_area'
                    ? $w->sanitationChecks->map(fn ($s) => array_merge($s->toArray(), [
                        'section_uuid' => $s->section_uuid,
                    ]))->values()->all()
                    : [],
            ];
        })->values()->all();
    }
@endphp

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="mb-0">{{ $isEdit ? 'Edit' : 'Tambah' }} Verifikasi Penerapan GMP Karyawan & Sanitasi Area</h5>
    </div>

    <div class="card-body">
        <form action="{{ $formAction }}" method="POST" id="gmp-form">
            @csrf
            @if ($formMethod === 'PUT') @method('PUT') @endif

            <div class="row mb-3">
                <div class="mb-3 col-md-4">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ old('date', $isEdit ? $gmpHeader->date->toDateString() : now()->toDateString()) }}" required>
                </div>

                <div class="mb-3 col-md-4">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control"
                        value="{{ old('shift', $gmpHeader->shift ?? (session('shift_number') . '-' . session('shift_group'))) }}">
                </div>

                <div class="mb-3 col-md-4">
                    <label>Verifikasi</label>
                    <select name="section" id="section-select" class="form-control" required {{ $isEdit ? 'disabled' : '' }}>
                        <option value="">-- Pilih --</option>
                        <option value="gmp_karyawan" {{ $section === 'gmp_karyawan' ? 'selected' : '' }}>GMP Karyawan</option>
                        <option value="sanitasi_area" {{ $section === 'sanitasi_area' ? 'selected' : '' }}>Sanitasi Area</option>
                    </select>
                    @if ($isEdit)
                        <input type="hidden" name="section" value="{{ $section }}">
                    @endif
                </div>
            </div>
            <hr>

            <p id="section-placeholder" class="text-muted {{ $section ? 'd-none' : '' }}">
                Pilih Verifikasi (GMP Karyawan / Sanitasi Area) dulu untuk menampilkan tabel isian.
            </p>

            <div id="waktu-container" class="{{ $section ? '' : 'd-none' }}"></div>

            <button type="button" id="add-waktu" class="btn btn-sm btn-info mb-3">+ Tambah Waktu Pemeriksaan</button>

            <div class="d-flex justify-content-between">
                <a href="{{ route('gmp.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= TEMPLATE: GMP KARYAWAN ================= --}}
<template id="tpl-waktu-karyawan">
    <div class="waktu-block border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="waktu-title mb-0">Waktu Pemeriksaan</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-waktu">Hapus Waktu</button>
        </div>

        <div class="row">
            <div class="mb-2 col-md-4">
                <label>Jam Pemeriksaan</label>
                <input type="time" name="__JAM__" class="form-control">
            </div>
        </div>

        <table class="table table-bordered table-sm align-middle mt-2">
            <thead>
                <tr>
                    <th style="min-width:150px">Area</th>
                    <th>Nama Karyawan</th>
                    <th>Seragam &amp; APD lengkap</th>
                    <th>Sarung tangan utuh</th>
                    <th>Sepatu boots bersih</th>
                    <th>Tidak pakai perhiasan &amp; jam tangan</th>
                    <th>Kuku &amp; tangan bersih, tanpa luka</th>
                    <th>Kuku tidak panjang &amp; tidak cat kuku</th>
                    <th>Perilaku &amp; kebiasaan kerja</th>
                    <th>Potensi cross contamination</th>
                    <th>Tindakan Koreksi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="employee-rows"></tbody>
        </table>

        

        <button type="button" class="btn btn-sm btn-outline-info add-employee-row mt-2">+ Tambah Karyawan</button>

        <div class="row mt-3">
            <div class="mb-2 col-md-12">
                <label>Catatan</label>
                <input type="text" name="__CATATAN__" class="form-control">
            </div>
        </div>
    </div>
</template>

<template id="tpl-employee-row">
    <tr>
        <td style="min-width:150px">
            <select name="__ROW__[section_uuid]" class="form-control form-control-sm">
                <option value="">-- Pilih --</option>
                @foreach ($sections as $sec)
                    <option value="{{ $sec->uuid }}">{{ $sec->section_name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="__ROW__[employee_name]" class="form-control form-control-sm"></td>
        @foreach (['seragam_apd_lengkap','sarung_tangan_utuh','sepatu_boots_bersih','tidak_pakai_perhiasan','kuku_tangan_bersih','kuku_tidak_panjang','perilaku_kerja','potensi_cross_contamination'] as $field)
        <td>
            <select name="__ROW__[{{ $field }}]" class="form-control form-control-sm">
                <option value="">-</option>
                <option value="1" selected>Ok</option>
                <option value="0">Tidak OK</option>
            </select>
        </td>
        @endforeach
        <td><input type="text" name="__ROW__[tindakan_koreksi]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
    </tr>
</template>

{{-- ================= TEMPLATE: SANITASI AREA ================= --}}
<template id="tpl-waktu-sanitasi">
    <div class="waktu-block border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="waktu-title mb-0">Waktu Pemeriksaan</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-waktu">Hapus Waktu</button>
        </div>

        <div class="row">
            <div class="mb-2 col-md-4">
                <label>Jam Pemeriksaan</label>
                <input type="time" name="__JAM__" class="form-control">
            </div>
        </div>

        <div class="d-flex align-items-center mb-2 mt-3" style="gap:.5rem">
            <select class="form-control form-control sanitation-area-select" style="max-width:260px">
                <option value="">-- Pilih Area --</option>
                @foreach ($sections as $sec)
                    <option value="{{ $sec->uuid }}" data-name="{{ $sec->section_name }}">{{ $sec->section_name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-info add-sanitation-area">+ Tambah Area</button>
        </div>

        <table class="table table-bordered table-sm align-middle mt-2">
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Item Verifikasi</th>
                    <th>Std. Klorin</th>
                    <th>Kadar Klorin (ppm)</th>
                    <th>Suhu (°C)</th>
                    <th>Tindakan Koreksi</th>
                    <th>Keterangan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="sanitation-rows"></tbody>
        </table>

        <div class="row mt-3">
            <div class="mb-2 col-md-12">
                <label>Catatan</label>
                <input type="text" name="__CATATAN__" class="form-control">
            </div>
        </div>
    </div>
</template>

<template id="tpl-sanitation-row">
    <tr>
        <td class="area-cell"></td>
        <td><input type="text" name="__ROW__[item_verifikasi]" class="form-control form-control-sm" readonly></td>
        <td><input type="number" name="__ROW__[standar_klorin]" class="form-control form-control-sm"></td>
        <td><input type="number" name="__ROW__[kadar_klorin]" class="form-control form-control-sm"></td>
        <td><input type="number" name="__ROW__[suhu]" class="form-control form-control-sm"></td>
        <td><input type="text" name="__ROW__[tindakan_koreksi]" class="form-control form-control-sm"></td>
        <td><input type="text" name="__ROW__[keterangan]" class="form-control form-control-sm"></td>
        <td class="remove-cell"></td>
    </tr>
</template>

@push('scripts')
<script>
const sections = @json($sections->map(fn ($s) => ['uuid' => $s->uuid, 'section_name' => $s->section_name])->values());
const sanitationItemList = @json($sanitationItemList); // [{item: 'Foot Basin', chlorine_std: 200}, {item: 'Hand Basin', chlorine_std: 50}]
const currentSection = @json($section);
const existingWaktu = @json($existingWaktu);

let waktuIndex = 0;

const container = document.getElementById('waktu-container');
const placeholder = document.getElementById('section-placeholder');
const sectionSelect = document.getElementById('section-select');

function addWaktuBlock(section, data = null) {
    const tpl = document.getElementById(section === 'gmp_karyawan' ? 'tpl-waktu-karyawan' : 'tpl-waktu-sanitasi');
    const node = tpl.content.cloneNode(true);
    const block = node.querySelector('.waktu-block');
    const idx = waktuIndex++;

    block.querySelector('.waktu-title').textContent = `Waktu Pemeriksaan ${idx + 1}`;
    block.querySelector('input[name="__JAM__"]').setAttribute('name', `waktu[${idx}][jam_pemeriksaan]`);
    if (data?.jam_pemeriksaan) block.querySelector(`[name="waktu[${idx}][jam_pemeriksaan]"]`).value = data.jam_pemeriksaan;

    block.querySelector('.remove-waktu').addEventListener('click', () => block.remove());

    block.querySelector('input[name="__CATATAN__"]').setAttribute('name', `waktu[${idx}][catatan]`);
    if (data?.catatan) block.querySelector(`[name="waktu[${idx}][catatan]"]`).value = data.catatan;

    if (section === 'gmp_karyawan') {
        const tbody = block.querySelector('.employee-rows');
        const addBtn = block.querySelector('.add-employee-row');
        let rowIndex = 0;

        function addEmployeeRow(rowData = null) {
            const rtpl = document.getElementById('tpl-employee-row');
            const rnode = rtpl.content.cloneNode(true);
            const prefix = `waktu[${idx}][employees][${rowIndex}]`;

            rnode.querySelectorAll('[name^="__ROW__"]').forEach(el => {
                el.setAttribute('name', el.getAttribute('name').replace('__ROW__', prefix));
            });

            if (rowData) {
                rnode.querySelectorAll('[name]').forEach(el => {
                    const key = el.getAttribute('name').split('[').pop().replace(']', '');
                    if (rowData[key] !== undefined && rowData[key] !== null) {
                        el.value = typeof rowData[key] === 'boolean' ? (rowData[key] ? '1' : '0') : rowData[key];
                    }
                });
            }

            rnode.querySelector('.remove-row').addEventListener('click', (e) => e.target.closest('tr').remove());
            tbody.appendChild(rnode);
            rowIndex++;
        }

        addBtn.addEventListener('click', () => addEmployeeRow());
        (data?.employees?.length ? data.employees : []).forEach(emp => addEmployeeRow(emp));
        if (!data?.employees?.length) addEmployeeRow();
    } else {

        const tbody = block.querySelector('.sanitation-rows');
        const areaSelect = block.querySelector('.sanitation-area-select');
        const addAreaBtn = block.querySelector('.add-sanitation-area');
        let rowIndex = 0;

        function addAreaGroup(sectionUuid, sectionName, existingRows = null) {
            sanitationItemList.forEach(item => {
                const rtpl = document.getElementById('tpl-sanitation-row');
                const rnode = rtpl.content.cloneNode(true);
                const tr = rnode.querySelector('tr');
                const prefix = `waktu[${idx}][sanitations][${rowIndex}]`;

                rnode.querySelectorAll('[name^="__ROW__"]').forEach(el => {
                    el.setAttribute('name', el.getAttribute('name').replace('__ROW__', prefix));
                });

                const sectionInput = document.createElement('input');
                sectionInput.type = 'hidden';
                sectionInput.name = `${prefix}[section_uuid]`;
                sectionInput.value = sectionUuid;
                tr.prepend(sectionInput);

                tr.dataset.sectionUuid = sectionUuid;
                tr.querySelector('.area-cell').textContent = sectionName;
                tr.querySelector('input[name$="[item_verifikasi]"]').value = item.item;
                tr.querySelector('input[name$="[standar_klorin]"]').value = item.chlorine_std ?? '';

                const existingRow = existingRows?.find(r => r.item_verifikasi === item.item);
                if (existingRow) {
                    ['kadar_klorin', 'suhu', 'tindakan_koreksi', 'keterangan'].forEach(key => {
                        const el = tr.querySelector(`[name$="[${key}]"]`);
                        if (el && existingRow[key] !== undefined && existingRow[key] !== null) el.value = existingRow[key];
                    });
                    if (existingRow.standar_klorin !== undefined && existingRow.standar_klorin !== null) {
                        tr.querySelector('input[name$="[standar_klorin]"]').value = existingRow.standar_klorin;
                    }
                }

                if (item === sanitationItemList[0]) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm btn-outline-danger remove-area-group';
                    btn.innerHTML = '&times;';
                    btn.addEventListener('click', () => {
                        tbody.querySelectorAll(`tr[data-section-uuid="${sectionUuid}"]`).forEach(r => r.remove());
                        const opt = areaSelect.querySelector(`option[value="${sectionUuid}"]`);
                        if (opt) opt.disabled = false;
                    });
                    tr.querySelector('.remove-cell').appendChild(btn);
                }

                tbody.appendChild(tr);
                rowIndex++;
            });

            const opt = areaSelect.querySelector(`option[value="${sectionUuid}"]`);
            if (opt) opt.disabled = true;
        }

        addAreaBtn.addEventListener('click', () => {
            const uuid = areaSelect.value;
            if (!uuid) return;
            const name = areaSelect.selectedOptions[0].dataset.name;
            addAreaGroup(uuid, name);
            areaSelect.value = '';
        });

        // isi ulang dari data existing (edit mode), dikelompokkan per section_uuid
        if (data?.sanitations?.length) {
            const grouped = {};
            data.sanitations.forEach(row => {
                if (!grouped[row.section_uuid]) grouped[row.section_uuid] = [];
                grouped[row.section_uuid].push(row);
            });
            Object.entries(grouped).forEach(([sectionUuid, rows]) => {
                const opt = areaSelect.querySelector(`option[value="${sectionUuid}"]`);
                const name = opt ? opt.dataset.name : '-';
                addAreaGroup(sectionUuid, name, rows);
            });
        }
    }

    container.appendChild(block);
}

function resetAndRender(section) {
    container.innerHTML = '';
    waktuIndex = 0;
    if (!section) {
        placeholder.classList.remove('d-none');
        container.classList.add('d-none');
        return;
    }
    placeholder.classList.add('d-none');
    container.classList.remove('d-none');

    if (existingWaktu.length && section === currentSection) {
        existingWaktu.forEach(w => addWaktuBlock(section, w));
    } else {
        addWaktuBlock(section);
    }
}

sectionSelect.addEventListener('change', (e) => resetAndRender(e.target.value));
document.getElementById('add-waktu').addEventListener('click', () => addWaktuBlock(sectionSelect.value));

if (currentSection) resetAndRender(currentSection);
</script>
@endpush