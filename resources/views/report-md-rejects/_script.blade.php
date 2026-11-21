@php
    $isEdit = isset($report);

    $rows = old('details', $isEdit
        ? $report->details->map(fn ($d) => [
            'uuid'                  => $d->uuid,
            'product_uuid'          => $d->product_uuid,
            'time'                  => substr($d->time, 0, 5),
            'production_code'       => $d->production_code,
            'reject_qty'            => $d->reject_qty,
            'recheck_result'        => $d->recheck_result,
            'contamination_found'   => is_null($d->contamination_found) ? null : (int) $d->contamination_found,
            'suspected_causes'      => $d->suspected_causes ?? [],
            'suspected_cause_other' => $d->suspected_cause_other,
            'corrective_action'     => $d->corrective_action,
            'disposition'           => $d->disposition,
        ])->values()->all()
        : []);
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form      = document.getElementById('md-reject-form');
    const wrap      = document.getElementById('detail-rows');
    const hint      = document.getElementById('empty-hint');
    const addBot    = document.getElementById('add-bottom');
    const toggleAll = document.getElementById('btn-toggle-all');
    const tpl       = document.getElementById('detail-template').innerHTML;
    const initial   = @json($rows);
    const isEdit    = @json($isEdit);
    let idx = 0;

    const nowHi = () => new Date().toTimeString().slice(0, 5);

    /* ---------- Select2 ---------- */
    function initSelect2(row) {
        const $s = $(row).find('select[data-f="product"]');
        $s.select2({
            placeholder: '-- Pilih Produk --',
            width: '100%',
            language: { noResults: () => 'Produk tidak ditemukan' }
        });
        // change dari Select2 hanya bisa ditangkap lewat jQuery
        $s.on('change', () => summary(row));
    }

    /* ---------- Ringkasan + badge di header kartu ---------- */
    function summary(row) {
        const sel  = row.querySelector('[data-f="product"]');
        const name = sel.selectedIndex > 0 ? sel.options[sel.selectedIndex].text : 'Produk belum dipilih';
        const code = row.querySelector('[data-f="code"]').value.trim();
        const qty  = row.querySelector('[data-f="qty"]').value;
        const time = row.querySelector('[data-f="time"]').value;

        row.querySelector('[data-s="title"]').textContent = name;
        row.querySelector('[data-s="sub"]').textContent =
            [time && 'Jam ' + time, code && 'Kode ' + code, qty !== '' && qty + ' pcs reject']
                .filter(Boolean).join('  ·  ') || '-';

        // Disposisi -> badge + warna aksen kartu
        const disp  = row.querySelector('input[name$="[disposition]"]:checked');
        const dBadge = row.querySelector('[data-s="disp"]');
        row.dataset.disp = disp ? disp.value : '';
        dBadge.textContent = disp ? disp.dataset.label : '';
        dBadge.className = 'mdr-badge ' + (disp ? 'b-' + disp.value : 'd-none');
        dBadge.setAttribute('data-s', 'disp');

        // Kontaminasi ditemukan -> badge merah
        const ct = row.querySelector('input[name$="[contamination_found]"]:checked');
        row.querySelector('[data-s="contam"]').classList.toggle('d-none', !(ct && ct.value === '1'));
    }

    /* ---------- Total di bar bawah ---------- */
    function totals() {
        const rows = wrap.querySelectorAll('.detail-row');
        let sum = 0;
        rows.forEach(r => sum += parseInt(r.querySelector('[data-f="qty"]').value || 0, 10));
        document.getElementById('total-products').textContent = rows.length;
        document.getElementById('total-reject').textContent = sum;
    }

    function updateToggleLabel() {
        const rows = wrap.querySelectorAll('.detail-row');
        const allCollapsed = rows.length > 0 && [...rows].every(r => r.classList.contains('is-collapsed'));
        toggleAll.textContent = allCollapsed ? 'Buka semua' : 'Lipat semua';
    }

    function renumber() {
        const rows = wrap.querySelectorAll('.detail-row');
        rows.forEach((el, n) => el.querySelector('.row-no').textContent = n + 1);
        hint.classList.toggle('d-none', rows.length > 0);
        addBot.classList.toggle('d-none', rows.length === 0);
        toggleAll.classList.toggle('d-none', rows.length < 2);
        totals();
        updateToggleLabel();
    }

    /* ---------- Input "Lainnya" ---------- */
    function toggleOther(row) {
        const cb    = row.querySelector('.cause-other-cb');
        const input = row.querySelector('.cause-other-input');
        input.classList.toggle('d-none', !cb.checked);
        if (!cb.checked) input.value = '';
    }

    /* ---------- Isi data ke satu kartu ---------- */
    function fill(row, d) {
        const i     = row.dataset.i;
        const field = n => row.querySelectorAll(`[name="details[${i}][${n}]"]`);

        ['uuid', 'time', 'production_code', 'reject_qty',
         'suspected_cause_other', 'corrective_action'].forEach(n => {
            field(n).forEach(el => el.value = d[n] ?? '');
        });

        // Select2 harus di-set lewat jQuery
        $(row).find('select[data-f="product"]').val(d.product_uuid ?? '').trigger('change');

        ['recheck_result', 'contamination_found', 'disposition'].forEach(n => {
            field(n).forEach(el => {
                el.checked = d[n] !== null && d[n] !== undefined && String(d[n]) === el.value;
            });
        });

        const causes = Object.values(d.suspected_causes || {});
        row.querySelectorAll(`[name="details[${i}][suspected_causes][]"]`).forEach(el => {
            el.checked = causes.includes(el.value);
        });

        toggleOther(row);
        summary(row);
    }

    /* ---------- Tambah kartu ---------- */
    function addRow(data) {
        const holder = document.createElement('div');
        holder.innerHTML = tpl.replaceAll('__I__', idx).trim();
        const row = holder.firstElementChild;
        wrap.appendChild(row);
        idx++;
        initSelect2(row);          // harus sebelum fill()
        fill(row, data || {});
        renumber();
        return row;
    }

    /* ---------- Event ---------- */
    document.querySelectorAll('.btn-add-product').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = addRow({ time: nowHi() });
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            $(row).find('select[data-f="product"]').select2('open');
        });
    });

    toggleAll.addEventListener('click', function () {
        const rows = wrap.querySelectorAll('.detail-row');
        const collapse = ![...rows].every(r => r.classList.contains('is-collapsed'));
        rows.forEach(r => r.classList.toggle('is-collapsed', collapse));
        updateToggleLabel();
    });

    wrap.addEventListener('click', function (e) {
        const rm = e.target.closest('.btn-remove-row');
        if (rm) {
            if (confirm('Hapus produk ini?')) {
                const row = rm.closest('.detail-row');
                $(row).find('select[data-f="product"]').select2('destroy');
                row.remove();
                renumber();
            }
            return;
        }
        const head = e.target.closest('.mdr-card-head');
        if (head) {
            head.closest('.detail-row').classList.toggle('is-collapsed');
            updateToggleLabel();
        }
    });

    wrap.addEventListener('input', function (e) {
        const row = e.target.closest('.detail-row');
        if (!row) return;
        summary(row);
        totals();
    });

    wrap.addEventListener('change', function (e) {
        const row = e.target.closest('.detail-row');
        if (!row) return;
        if (e.target.classList.contains('cause-other-cb')) toggleOther(row);
        summary(row);
    });

    // Field wajib kosong: tandai merah + buka kartu yang terlipat
    form.addEventListener('invalid', function (e) {
        form.classList.add('mdr-validated');
        const row = e.target.closest('.detail-row');
        if (row) row.classList.remove('is-collapsed');
    }, true);

    /* ---------- Cegah submit ganda ---------- */
    const btnSubmit = document.getElementById('btn-submit');
    const btnHtml   = btnSubmit.innerHTML;

    form.addEventListener('submit', function () {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
    });

    // Halaman dipulihkan dari bfcache (tombol Back)
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = btnHtml;
        }
    });

    /* ---------- Init ---------- */
    initial.forEach(d => addRow(d));
    if (!isEdit && initial.length === 0) addRow({ time: nowHi() });
    renumber();
});
</script>