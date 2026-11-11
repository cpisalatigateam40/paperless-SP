@php
    $report = $report ?? null;
    $method = $method ?? 'POST';

    $masterRoom = $rooms->map(function ($r) {
        return [
            'uuid'  => $r->uuid,
            'name'  => $r->name,
            'items' => $r->elements->map(function ($e) {
                return ['uuid' => $e->uuid, 'name' => $e->element_name];
            })->values(),
        ];
    })->values();

    $masterEquipment = $equipments->map(function ($q) {
        return [
            'uuid'      => $q->uuid,
            'name'      => $q->name,
            'room_uuid' => $q->room_uuid,
            'room'      => optional($q->room)->name,
            'items'     => $q->parts->map(function ($p) {
                return ['uuid' => $p->uuid, 'name' => $p->part_name];
            })->values(),
        ];
    })->values();

    // Data awal (mode edit)
    $mapDetail = function ($d) {
        return [
            'condition'         => $d->condition,
            'notes'             => $d->notes,
            'corrective_action' => $d->corrective_action,
            'verification'      => $d->verification,
            'followups'         => $d->followups->map(function ($f) {
                return [
                    'notes'        => $f->notes,
                    'action'       => $f->corrective_action,
                    'verification' => $f->verification,
                ];
            })->values(),
        ];
    };

    $initial = ['room' => [], 'equipment' => []];
    $timeValue = now()->format('H:i');

    $sessionTime = $sessionTime ?? null;
    $inSession = function ($d) use ($sessionTime) {
        return $sessionTime
            ? substr((string) $d->verification_time, 0, 5) === $sessionTime
            : empty($d->verification_time);
    };

    if ($report) {
        foreach ($report->roomDetails->filter($inSession) as $d) {
            $initial['room'][$d->room_uuid][$d->room_element_uuid ?: '_'] = $mapDetail($d);
        }
        foreach ($report->equipmentDetails->filter($inSession) as $d) {
            $initial['equipment'][$d->equipment_uuid][$d->equipment_part_uuid ?: '_'] = $mapDetail($d);
        }

        $timeValue = $sessionTime ?: ($report->created_at ? $report->created_at->format('H:i') : $timeValue);
    }

    $dateValue = $report
        ? \Carbon\Carbon::parse($report->date)->toDateString()
        : \Carbon\Carbon::today()->toDateString();
@endphp

<style>
    .cl-block { border: 1px solid #dee2e6; border-left: 5px solid #adb5bd; border-radius: .6rem; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .cl-block.is-partial { border-left-color: #ffc107; }
    .cl-block.is-done { border-left-color: #198754; }
    .cl-block-head { background: #f8f9fa; border-radius: .6rem .6rem 0 0; }
    .cl-item { border: 1px solid #e9ecef; border-radius: .5rem; padding: .75rem .9rem; background: #fff; transition: background .15s, border-color .15s; }
    .cl-item.is-clean { border-color: #198754; background: #f0fbf4; }
    .cl-item.is-dirty { border-color: #ffc107; background: #fffbea; }
    .cl-item.is-fixed { border-color: #0dcaf0; background: #effcff; }
    .cl-item.is-missing { border-color: #dc3545; box-shadow: 0 0 0 .2rem rgba(220,53,69,.2); }
    .cl-legend span { display: inline-flex; align-items: center; gap: .35rem; margin-right: 1rem; font-size: .8rem; color: #6c757d; }
    .cl-legend i { width: .8rem; height: .8rem; border-radius: 50%; display: inline-block; border: 2px solid; }
    .cl-tab-count { font-size: .7rem; }
    .followup-group { background: #fff; }

    .cl-item .state-badge { font-size: .7rem; }
    .cl-dirty-card { background: #fff; border: 1px solid #ffe08a; border-left: 4px solid #ffc107; border-radius: .5rem; padding: .75rem; }
    .cl-dirty-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #b58100; margin-bottom: .5rem; }
    .cl-dirty-card label.cl-lbl { font-size: .75rem; font-weight: 600; color: #6c757d; margin-bottom: .15rem; }
    .cl-item.is-fixed .cl-dirty-card { border-color: #9eeaf9; border-left-color: #0dcaf0; }
    .cl-item.is-fixed .cl-dirty-title { color: #087990; }
    .followup-group { border: 1px solid #dee2e6; border-left: 4px solid #dc3545; border-radius: .5rem; background: #fff; }

    .followup-group .cl-dirty-title { margin-bottom: .5rem; }
    .followup-wrapper:empty { display: none; }

    .cl-info { background: #eef6ff; border: 1px solid #b6d4fe; border-left: 4px solid #0d6efd; border-radius: .5rem; color: #084298; }
    .cl-info-icon { font-size: 1.4rem; margin-right: .75rem; color: #0d6efd; line-height: 1.2; }
    .cl-info ul { padding-left: 1.1rem; }
    .cl-info li { margin-bottom: .2rem; }
</style>

<form action="{{ $action }}" method="POST" id="cleanlinessForm">
    @csrf
    @if ($report)
        <input type="hidden" name="original_time" value="{{ $sessionTime }}">
    @endif
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Header --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal</label>
            <input type="date" name="date" class="form-control" value="{{ old('date', $dateValue) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Jam Verifikasi</label>
            <input type="time" name="verification_time" class="form-control"
                value="{{ old('verification_time', $timeValue) }}" required>
        </div>
    </div>

    {{-- Legend --}}
    <div class="cl-legend mb-3">
        <span><i style="border-color:#adb5bd"></i> Belum diperiksa</span>
        <span><i style="border-color:#198754;background:#f0fbf4"></i> Bersih</span>
        <span><i style="border-color:#ffc107;background:#fffbea"></i> Kotor, perlu diisi</span>
        <span><i style="border-color:#0dcaf0;background:#effcff"></i> Kotor, sudah ditindak</span>
    </div>

    <div class="alert cl-info alert-dismissible fade show mb-3" role="alert" id="clInfo">
        <div class="d-flex">
            <div class="cl-info-icon"><i class="fas fa-info-circle"></i></div>
            <div>
                <div class="fw-bold mb-1">Cara mengisi pemeriksaan</div>
                <ul class="mb-0 pl-3 ps-3 small" style="padding: 0px !important;">
                    <li>Semua item otomatis berstatus <span class="badge bg-success">Bersih</span>. Jika kondisinya sudah sesuai, tidak ada yang perlu diubah.</li>
                    <li>Jika ada yang <strong>kotor</strong>, <strong>matikan (uncheck)</strong> item tersebut. Kolom <em>Catatan</em>, <em>Tindakan Koreksi</em>, dan <em>Verifikasi</em> akan muncul untuk diisi.</li>
                    <li>Pilih verifikasi <strong>Tidak OK</strong> jika setelah koreksi masih belum bersih. Kolom <em>Koreksi Lanjutan</em> akan muncul.</li>
                    <li>Pilih ruangan untuk menampilkan mesin dan peralatannya secara otomatis. Item yang tidak diperiksa bisa dihapus dengan tombol <i class="fas fa-trash text-danger"></i>.</li>
                </ul>
            </div>
        </div>
        <button type="button" class="close btn-close" id="clInfoClose" aria-label="Tutup"></button>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" id="cleanTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#roomTab">
                Ruangan <span class="badge bg-secondary cl-tab-count" id="roomCount">0</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#equipmentTab">
                Mesin & Peralatan <span class="badge bg-secondary cl-tab-count" id="equipmentCount">0</span>
            </a>
        </li>
    </ul>

    <div class="tab-content p-3 border border-top-0 bg-white">
        <div class="tab-pane fade show active" id="roomTab">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-outline-secondary btn-sm btn-toggle-all" data-type="room" title="Lipat semua">
                    <i class="fas fa-angle-double-up"></i>
                </button>
            </div>
            <div id="roomBlocks"></div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addRoom">+ Tambah Ruangan</button>
        </div>

        <div class="tab-pane fade" id="equipmentTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">
                    Mesin dari ruangan yang dipilih di tab Ruangan akan muncul otomatis di sini.
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm btn-toggle-all" data-type="equipment">Lipat Semua</button>
            </div>
            <div id="equipmentBlocks"></div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addEquipment">+ Tambah Mesin / Peralatan</button>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
        <button type="submit" class="btn btn-success px-4">Simpan</button>
    </div>
</form>

<script>
/* ================= DATA ================= */
const MASTER = {
    room: @json($masterRoom),
    equipment: @json($masterEquipment),
};
const INITIAL = @json($initial);

const CFG = {
    room: {
        prefix: 'rooms', key: 'elements', container: 'roomBlocks', count: 'roomCount',
        label: 'Ruangan', clean: 'Ruangan Bersih', all: 'Centang Semua',
    },
    equipment: {
        prefix: 'equipments', key: 'parts', container: 'equipmentBlocks', count: 'equipmentCount',
        label: 'Mesin / Peralatan', clean: 'Mesin / Peralatan Bersih', all: 'Centang Semua Part',
    },
};

const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const q = (el, sel) => el.querySelector(sel);
let uid = 0;

/* ================= RENDER ================= */
function itemHtml(c, uuid, it) {
    const n = `${c.prefix}[${uuid}][${c.key}][${it.uuid}]`;
    const id = `chk_${++uid}`;
    return `
    <div class="col-md-6 col-xl-4 item-col mb-3" data-item="${it.uuid}" data-touched="1" style="padding-right: 2px !important;">
        <div class="cl-item h-100 is-clean">
            <div class="d-flex align-items-center justify-content-between">
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="${n}[condition]" value="dirty">
                    <input class="form-check-input state-check" type="checkbox" role="switch" id="${id}"
                        name="${n}[condition]" value="clean" checked>
                    <label class="form-check-label fw-semibold" for="${id}">${esc(it.name)}</label>
                </div>
                <span class="badge bg-success state-badge">Bersih</span>
            </div>

            <div class="cleanliness-fields d-none mt-3">
                <div class="cl-dirty-card mb-3">
                    <div class="cl-dirty-title">Ketidaksesuaian</div>
                    <label class="cl-lbl">Catatan</label>
                    <input name="${n}[notes]" class="form-control form-control-sm mb-2 notes" placeholder="Jelaskan kondisi kotor" disabled>
                    <label class="cl-lbl">Tindakan Koreksi</label>
                    <input name="${n}[corrective_action]" class="form-control form-control-sm mb-2 corrective_action" placeholder="Tindakan yang dilakukan" disabled>
                    <label class="cl-lbl">Verifikasi Setelah Koreksi</label>
                    <select name="${n}[verification]" class="form-select form-select-sm verification form-control">
                        <option value="">-- Pilih Verifikasi --</option>
                        <option value="OK" selected>OK</option>
                        <option value="Tidak OK">Tidak OK</option>
                    </select>
                </div>
                <div class="followup-wrapper"></div>
            </div>
        </div>
    </div>`;
}

function bodyHtml(type, uuid) {
    const c = CFG[type];
    const m = MASTER[type].find(x => x.uuid === uuid);
    const hidden = `<input type="hidden" name="${c.prefix}[${uuid}][name]" value="${esc(m.name)}">`;

    if (!m.items.length) {
        const id = `chk_${++uid}`;
        return hidden + `
        <div class="cl-item single-item mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="${c.prefix}[${uuid}][condition]" value="dirty">
                <input class="form-check-input single-check" type="checkbox" role="switch" id="${id}"
                    name="${c.prefix}[${uuid}][condition]" value="clean" checked>
                <label class="form-check-label fw-semibold" for="${id}">${c.clean}</label>
            </div>
        </div>`;
    }

    const allId = `chk_${++uid}`;
    return hidden + `
        <div class="form-check mb-3">
            <input class="form-check-input checkall" type="checkbox" id="${allId}" checked>
            <label class="form-check-label" for="${allId}">${c.all}</label>
        </div>
        <div class="row g-3">${m.items.map(it => itemHtml(c, uuid, it)).join('')}</div>`;
}

function addBlock(type, preselect = null) {
    const c = CFG[type];
    const div = document.createElement('div');
    div.className = 'cl-block mb-3 cleanliness-item';
    div.innerHTML = `
        <div class="cl-block-head d-flex flex-wrap gap-2 align-items-center p-3">
            <div class="flex-grow-1" style="min-width:220px">
                <select class="form-select form-control block-picker">
                    ${type === 'equipment'
                        ? equipmentOptionsHtml()
                        : `<option value="">-- Pilih ${c.label} --</option>` +
                        MASTER[type].map(m => `<option value="${m.uuid}">${esc(m.name)}</option>`).join('')}
                </select>
            </div>
            <span class="badge bg-info text-dark block-room d-none"></span>
            <span class="badge bg-secondary block-progress">Belum dipilih</span>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-collapse-block" title="Lipat">
                <i class="fas fa-chevron-up"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-block" title="Hapus">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="block-body p-3"></div>`;
    document.getElementById(c.container).appendChild(div);

    if (preselect) {
        const sel = q(div, '.block-picker');
        if (type === 'equipment') sel.innerHTML = equipmentOptionsHtml(preselect);
        sel.value = preselect;
        selectBlock(div, type, preselect);
    } else {
        refreshOptions(type);
        updateCounts();
    }
    return div;
}

function selectBlock(item, type, uuid) {
    item.dataset.uuid = uuid || '';
    q(item, '.block-body').innerHTML = uuid ? bodyHtml(type, uuid) : '';

    if (type === 'equipment') {
        const badge = q(item, '.block-room');
        const eq = MASTER.equipment.find(x => x.uuid === uuid);
        badge.classList.toggle('d-none', !uuid);
        badge.textContent = eq && eq.room ? 'Ruangan: ' + eq.room : 'Ruangan belum diatur';
        badge.classList.toggle('bg-warning', !(eq && eq.room));
        badge.classList.toggle('bg-info', !!(eq && eq.room));
    }

    refreshOptions(type);
    refreshBlock(item);
    updateCounts();
}

// Tambah otomatis mesin milik ruangan yang dipilih
function autoAddEquipments(roomUuid) {
    const wrap = document.getElementById(CFG.equipment.container);
    const picked = [...wrap.querySelectorAll('.block-picker')].map(s => s.value).filter(Boolean);

    MASTER.equipment
        .filter(e => e.room_uuid === roomUuid && !picked.includes(e.uuid))
        .forEach(e => {
            // pakai blok kosong yang ada, kalau tidak ada baru buat blok baru
            const blk = [...wrap.querySelectorAll('.cleanliness-item')].find(i => !i.dataset.uuid)
                || addBlock('equipment');
            const sel = q(blk, '.block-picker');
            sel.innerHTML = equipmentOptionsHtml(e.uuid);
            sel.value = e.uuid;
            blk.dataset.auto = roomUuid;
            selectBlock(blk, 'equipment', e.uuid);
        });
}

// Hapus mesin otomatis dari ruangan itu, kecuali yang sudah mulai diisi
function removeAutoBlocks(roomUuid) {
    if (!roomUuid) return;
    const wrap = document.getElementById(CFG.equipment.container);
    wrap.querySelectorAll(`.cleanliness-item[data-auto="${roomUuid}"]`).forEach(blk => {
        const changed = blk.querySelector('.state-check:not(:checked)')
            || blk.querySelector('.single-check:not(:checked)');
        if (!changed) blk.remove();
    });
    refreshOptions('equipment');
    updateCounts();
}

function refreshOptions(type) {
    const wrap = document.getElementById(CFG[type].container);
    const picked = [...wrap.querySelectorAll('.block-picker')].map(s => s.value).filter(Boolean);
    wrap.querySelectorAll('.block-picker').forEach(sel => {
        sel.querySelectorAll('option').forEach(o => {
            if (o.value) o.disabled = picked.includes(o.value) && o.value !== sel.value;
        });
    });
}

// Daftar mesin yang boleh dipilih = mesin milik ruangan yang sedang dipilih
function selectedRoomUuids() {
    return [...document.getElementById(CFG.room.container).querySelectorAll('.block-picker')]
        .map(s => s.value).filter(Boolean);
}

function equipmentOptionsHtml(currentValue = '') {
    const rooms = selectedRoomUuids();
    const list = MASTER.equipment.filter(e =>
        rooms.includes(e.room_uuid) || e.uuid === currentValue // mesin yang sudah terpilih tetap tampil
    );
    const withLabel = rooms.length > 1;

    return `<option value="">${rooms.length ? '-- Pilih Mesin / Peralatan --' : '-- Pilih ruangan dulu --'}</option>` +
        list.map(m => `<option value="${m.uuid}"${m.uuid === currentValue ? ' selected' : ''}>${esc(m.name)}${withLabel && m.room ? ' — ' + esc(m.room) : ''}</option>`).join('');
}

// Bangun ulang semua dropdown mesin (dipanggil saat pilihan ruangan berubah)
function refreshEquipmentPickers() {
    document.getElementById(CFG.equipment.container).querySelectorAll('.block-picker').forEach(sel => {
        sel.innerHTML = equipmentOptionsHtml(sel.value);
    });
    refreshOptions('equipment');
}

function updateCounts() {
    Object.keys(CFG).forEach(type => {
        const n = [...document.getElementById(CFG[type].container).querySelectorAll('.cleanliness-item')]
            .filter(i => i.dataset.uuid).length;
        document.getElementById(CFG[type].count).textContent = n;
    });
    Object.keys(CFG).forEach(updateToggleAll);
}

/* ================= STATE ITEM ================= */
const ICON = {
    one: collapsed => `<i class="fas fa-chevron-${collapsed ? 'down' : 'up'}"></i>`,
    all: collapsed => `<i class="fas fa-angle-double-${collapsed ? 'down' : 'up'}"></i>`,
};
// Lipat / buka semua blok di satu tab
function setCollapsed(item, collapsed) {
    const body = q(item, '.block-body');
    const btn  = q(item, '.btn-collapse-block');
    body.classList.toggle('d-none', collapsed);
    if (btn) {
        btn.innerHTML = ICON.one(collapsed);
        btn.title = collapsed ? 'Buka' : 'Lipat';
    }
}

function updateToggleAll(type) {
    const btn = document.querySelector(`.btn-toggle-all[data-type="${type}"]`);
    if (!btn) return;
    const items = [...document.getElementById(CFG[type].container).querySelectorAll('.cleanliness-item')];
    const allCollapsed = items.length > 0 && items.every(i => q(i, '.block-body').classList.contains('d-none'));
    btn.innerHTML = ICON.all(allCollapsed);
    btn.title = allCollapsed ? 'Buka semua' : 'Lipat semua';
    btn.disabled = items.length === 0;
}

function itemState(col) {
    if (q(col, '.state-check').checked) return 'clean';
    const filled = ['.notes', '.corrective_action', '.verification']
        .every(s => String(q(col, s).value || '').trim() !== '');
    return filled ? 'fixed' : 'dirty';
}

function refreshBlock(item) {
    const badge = q(item, '.block-progress');
    const cols = item.querySelectorAll('.item-col');
    const single = q(item, '.single-check');
    let total = 0, done = 0;

    if (cols.length) {
        total = cols.length;
        cols.forEach(col => {
            const s = itemState(col);
            const box = q(col, '.cl-item');
            box.classList.remove('is-clean', 'is-dirty', 'is-fixed', 'is-missing');
            if (s !== 'pending') box.classList.add('is-' + s);
            if (s === 'clean' || s === 'fixed') done++;
        });
    } else if (single) {
        total = 1;
        const box = q(item, '.single-item');
        box.classList.toggle('is-clean', single.checked);
        done = single.checked ? 1 : 0;
    }

    item.classList.remove('is-partial', 'is-done');
    badge.className = 'badge block-progress';

    if (!total) {
        badge.classList.add('bg-secondary');
        badge.textContent = 'Belum dipilih';
        return;
    }
    badge.textContent = `${done}/${total} selesai`;
    if (done === total) { item.classList.add('is-done'); badge.classList.add('bg-success'); }
    else if (done > 0)  { item.classList.add('is-partial'); badge.classList.add('bg-warning', 'text-dark'); }
    else                { badge.classList.add('bg-secondary'); }
}

function applyState(cb) {
    const col = cb.closest('.item-col');
    col.dataset.touched = '1';
    const fields = q(col, '.cleanliness-fields');
    const notes = q(col, '.notes'), corr = q(col, '.corrective_action'), ver = q(col, '.verification');
    const badge = q(col, '.state-badge');
    q(col, '.followup-wrapper').innerHTML = '';

    if (cb.checked) {
        fields.classList.add('d-none');
        notes.disabled = corr.disabled = true;
        notes.value = corr.value = '';
        ver.value = 'OK';
        badge.className = 'badge bg-success state-badge';
        badge.textContent = 'Bersih';
    } else {
        fields.classList.remove('d-none');
        notes.disabled = corr.disabled = false;
        ver.value = '';
        badge.className = 'badge bg-warning text-dark state-badge';
        badge.textContent = 'Kotor';
        notes.focus();
    }
}

function syncCheckAll(item) {
    const all = item.querySelectorAll('.state-check');
    const checked = item.querySelectorAll('.state-check:checked');
    const ca = q(item, '.checkall');
    if (ca) ca.checked = all.length > 0 && all.length === checked.length;
}

function addFollowupField(wrapper, baseName) {
    const count = wrapper.querySelectorAll('.followup-group').length;
    wrapper.insertAdjacentHTML('beforeend', `
        <div class="followup-group p-2 mb-3">
            <div class="cl-dirty-title" style="color:#dc3545">Koreksi Lanjutan #${count + 1}</div>
            <input type="text" name="${baseName}[followups][${count}][notes]" class="form-control form-control-sm mb-2" placeholder="Catatan">
            <input type="text" name="${baseName}[followups][${count}][action]" class="form-control form-control-sm mb-2" placeholder="Tindakan koreksi">
            <select name="${baseName}[followups][${count}][verification]" class="form-select form-select-sm followup-verification form-control">
                <option value="">-- Pilih Verifikasi --</option>
                <option value="OK">OK</option>
                <option value="Tidak OK">Tidak OK</option>
            </select>
        </div>`);
}

/* ================= RESTORE (EDIT) ================= */
function restoreItem(col, d) {
    const cb = q(col, '.state-check');
    cb.checked = d.condition === 'clean';
    applyState(cb);

    if (!cb.checked) {
        q(col, '.notes').value = d.notes || '';
        q(col, '.corrective_action').value = d.corrective_action || '';
    }

    const ver = q(col, '.verification');
    ver.value = d.verification || '';

    if (ver.value && ver.value !== 'OK') {
        const wrap = q(col, '.followup-wrapper');
        const base = ver.name.replace('[verification]', '');
        const fus = d.followups || [];
        fus.forEach(f => {
            addFollowupField(wrap, base);
            const g = wrap.lastElementChild;
            q(g, '[name$="[notes]"]').value = f.notes || '';
            q(g, '[name$="[action]"]').value = f.action || '';
            q(g, '[name$="[verification]"]').value = f.verification || '';
        });
        if (!fus.length) addFollowupField(wrap, base);
    }
}

function restoreAll() {
    let any = { room: false, equipment: false };

    Object.keys(CFG).forEach(type => {
        Object.entries(INITIAL[type] || {}).forEach(([uuid, items]) => {
            if (!MASTER[type].some(m => m.uuid === uuid)) return;
            const block = addBlock(type, uuid);
            any[type] = true;

            Object.entries(items).forEach(([itemUuid, d]) => {
                if (itemUuid === '_') {
                    const single = q(block, '.single-check');
                    if (single) single.checked = d.condition === 'clean';
                } else {
                    const col = block.querySelector(`.item-col[data-item="${itemUuid}"]`);
                    if (col) restoreItem(col, d);
                }
            });
            syncCheckAll(block);
            refreshBlock(block);
        });
        if (!any[type]) addBlock(type);
    });
}

/* ================= EVENTS ================= */
document.addEventListener('change', function (e) {
    const t = e.target;
    const item = t.closest('.cleanliness-item');
    const type = t.closest('#roomBlocks') ? 'room' : 'equipment';

    if (t.classList.contains('block-picker')) {
        if (type === 'room') removeAutoBlocks(item.dataset.uuid);
        selectBlock(item, type, t.value);
        if (type === 'room') {
            if (t.value) autoAddEquipments(t.value);
            refreshEquipmentPickers();
        }
        return;
    }

    if (t.classList.contains('state-check')) {
        applyState(t);
        syncCheckAll(item);
    }

    if (t.classList.contains('checkall')) {
        item.querySelectorAll('.item-col').forEach(col => {
            const cb = q(col, '.state-check');
            cb.checked = t.checked;
            applyState(cb);
        });
    }

    if (t.classList.contains('verification')) {
        const col = t.closest('.item-col');
        const wrapper = q(col, '.followup-wrapper');
        const baseName = t.name.replace('[verification]', '');
        wrapper.innerHTML = '';
        if (t.value !== '' && t.value !== 'OK') addFollowupField(wrapper, baseName);
    }

    if (t.classList.contains('followup-verification')) {
        const group = t.closest('.followup-group');
        const wrapper = group.parentElement;
        const groups = wrapper.querySelectorAll('.followup-group');
        const idx = Array.from(groups).indexOf(group);

        if (t.value === 'Tidak OK') {
            if (groups.length === idx + 1) {
                addFollowupField(wrapper, t.name.replace(/\[followups\]\[\d+\]\[verification\]/, ''));
            }
        } else if (t.value === 'OK') {
            for (let i = groups.length - 1; i > idx; i--) groups[i].remove();
        }
    }

    if (item) refreshBlock(item);
});

document.addEventListener('input', function (e) {
    const item = e.target.closest('.cleanliness-item');
    if (item && e.target.matches('.notes, .corrective_action')) refreshBlock(item);
});

document.addEventListener('click', function (e) {
    const item = e.target.closest('.cleanliness-item');
    const toggleAll = e.target.closest('.btn-toggle-all');
    const removeBtn = e.target.closest('.btn-remove-block');
    const collapseBtn = e.target.closest('.btn-collapse-block');

    if (toggleAll) {
        const type = toggleAll.dataset.type;
        const items = [...document.getElementById(CFG[type].container).querySelectorAll('.cleanliness-item')];
        const collapse = items.some(i => !q(i, '.block-body').classList.contains('d-none'));
        items.forEach(i => setCollapsed(i, collapse));
        updateToggleAll(type);
        return;
    }

    if (removeBtn) {
        const type = item.closest('#roomBlocks') ? 'room' : 'equipment';
        if (type === 'room') removeAutoBlocks(item.dataset.uuid);
        item.remove();
        if (type === 'room') refreshEquipmentPickers();
        refreshOptions(type);
        updateCounts();
        updateToggleAll(type);
        return;
    }

    if (collapseBtn) {
        const type = item.closest('#roomBlocks') ? 'room' : 'equipment';
        setCollapsed(item, !q(item, '.block-body').classList.contains('d-none'));
        updateToggleAll(type);
    }
});

document.getElementById('cleanlinessForm').addEventListener('submit', function (e) {
    const pickers = [...document.querySelectorAll('.block-picker')].filter(s => s.value);
    if (!pickers.length) {
        e.preventDefault();
        alert('Pilih minimal satu ruangan atau mesin/peralatan.');
        return;
    }

    const bad = [...document.querySelectorAll('.item-col')].filter(col => itemState(col) === 'dirty');
    if (bad.length) {
        e.preventDefault();
        alert(`Ada ${bad.length} item kotor yang catatan, tindakan, atau verifikasinya belum lengkap.`);

        const first = bad[0];
        const trigger = document.querySelector(`a[href="#${first.closest('.tab-pane').id}"]`);
        if (window.bootstrap && trigger) bootstrap.Tab.getOrCreateInstance(trigger).show();

        setCollapsed(first.closest('.cleanliness-item'), false);
        bad.forEach(col => q(col, '.cl-item').classList.add('is-missing'));
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

/* ================= INIT ================= */
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('addRoom').onclick = () => addBlock('room');
    document.getElementById('addEquipment').onclick = () => addBlock('equipment');
    restoreAll();
});

// Banner info (tutup & ingat)
(function () {
    const box = document.getElementById('clInfo');
    if (!box) return; // aman jika banner tidak dirender (mode edit)
    const KEY = 'cl_info_hidden';
    try { if (localStorage.getItem(KEY) === '1') box.remove(); } catch (e) {}

    document.getElementById('clInfoClose')?.addEventListener('click', () => {
        try { localStorage.setItem(KEY, '1'); } catch (e) {}
        box.remove();
    });
})();
</script>