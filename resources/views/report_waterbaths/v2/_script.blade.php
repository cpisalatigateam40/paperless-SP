<script>
document.addEventListener('DOMContentLoaded', function () {
    const runsEl   = document.getElementById('runs');
    const alertEl  = document.getElementById('master-alert');
    const runTpl   = document.getElementById('run-template').innerHTML;
    const byProductUrl = @json(route('report_waterbaths.master_standard', '__P__'));

    let runCounter = parseInt(runsEl.dataset.next, 10);
    let master = null; // standar master produk terpilih (dipakai untuk blok baru)

    /* ---------- helper angka ---------- */
    const num = v => (v === '' || v === null || v === undefined || isNaN(v)) ? null : Number(v);
    const f2  = v => num(v) === null ? null : num(v).toFixed(2);

    const rangeText = (min, max) => {
        const a = f2(min), b = f2(max);
        if (a === null) return '-';
        if (b === null || Math.abs(num(max) - num(min)) < 0.0001) return a;
        return a + ' - ' + b;
    };

    const snap    = (scope, key) => key ? scope.querySelector('[data-snap="' + key + '"]') : null;
    const snapVal = (scope, key) => { const el = snap(scope, key); return el ? el.value : ''; };
    const allBlocks = () => Array.from(runsEl.querySelectorAll('.run-block'));

    /* ---------- tampilan range + warna merah ---------- */
    function checkAct(input) {
        const scope = input.closest('[data-scope]');
        const min = num(snapVal(scope, input.dataset.min));
        const max = num(snapVal(scope, input.dataset.max));
        const v   = num(input.value);

        let out = false;
        if (v !== null && min !== null) {
            out = (max === null) ? Math.abs(v - min) > 0.0001 : (v < min || v > max);
        }
        input.classList.toggle('is-out-std', out);
    }

    function updateFlag(block) {
        const n    = block.querySelectorAll('.is-out-std').length;
        const flag = block.querySelector('.run-flag');
        flag.textContent = n + ' di luar standar';
        flag.classList.toggle('d-none', n === 0);
    }

    function refreshBlock(block) {
        block.querySelectorAll('.rng-text').forEach(span => {
            const scope = span.closest('[data-scope]');
            span.textContent = rangeText(snapVal(scope, span.dataset.min), snapVal(scope, span.dataset.max));
        });
        block.querySelectorAll('.rng-act').forEach(checkAct);
        updateFlag(block);
    }

    /* ---------- isi snapshot dari master ---------- */
    function setSnap(scope, values) {
        if (!scope) return;
        Object.entries(values).forEach(([k, v]) => {
            const el = snap(scope, k);
            if (el) el.value = (v === null || v === undefined) ? '' : v;
        });
    }

    function fillBlock(block, data) {
        block.querySelectorAll('[data-snap]').forEach(el => el.value = '');

        if (data) {
            setSnap(block.querySelector('[data-scope="initial"]'), {
                initial_temp_min: data.initial_temp_min,
                initial_temp_max: data.initial_temp_max,
            });

            Object.entries(data.parameters || {}).forEach(([key, p]) => {
                setSnap(block.querySelector('tr[data-scope="' + key + '"]'), {
                    set_temp_min:      p.set_temp_min,
                    set_temp_max:      p.set_temp_max,
                    cold_zone_set_min: p.cold_zone_temp_min,
                    cold_zone_set_max: p.cold_zone_temp_max,
                    set_time:          p.set_time,
                    std_ct_min:        p.std_ct_min,
                    std_ct_max:        p.std_ct_max,
                });
            });
        }
        refreshBlock(block);
    }

    // salin snapshot standar dari blok pertama; return false kalau blok pertama belum punya snapshot
    function copySnap(block, from) {
        if (!from) return false;
        const filled = Array.from(from.querySelectorAll('[data-snap]')).some(e => e.value !== '');
        if (!filled) return false;

        block.querySelectorAll('[data-snap]').forEach(el => {
            const scope = el.closest('[data-scope]').dataset.scope;
            const src = from.querySelector('[data-scope="' + scope + '"] [data-snap="' + el.dataset.snap + '"]');
            el.value = src ? src.value : '';
        });
        refreshBlock(block);
        return true;
    }

    const alertDefault = alertEl.textContent;

    function loadMaster(productUuid, fillExisting) {
        if (!productUuid) {
            master = null;
            alertEl.classList.add('d-none');
            if (fillExisting) allBlocks().forEach(b => fillBlock(b, null));
            return;
        }

        fetch(byProductUrl.replace('__P__', encodeURIComponent(productUuid)), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(d => {
                master = d.found ? d : null;
                alertEl.textContent = alertDefault;
                alertEl.classList.toggle('d-none', !!master);
                if (fillExisting) allBlocks().forEach(b => fillBlock(b, master));
            })
            .catch(err => {
                console.error('Gagal memuat master waterbath:', err);
                master = null;
                alertEl.textContent = 'Gagal memuat master waterbath (' + err.message + '). Hubungi admin.';
                alertEl.classList.remove('d-none');
            });
    }

    /* ---------- judul blok + tombol hapus ---------- */
    function renumber() {
        allBlocks().forEach((b, idx) => {
            b.querySelector('.run-title-text').textContent =
                idx === 0 ? 'Hasil Verifikasi' : 'Pasteurisasi Ulang' + (idx > 1 ? ' #' + idx : '');
            b.querySelector('.btn-remove-run').classList.toggle('d-none', idx === 0);
        });
    }

    /* ---------- event: pasteurisasi ulang ---------- */
    document.getElementById('btn-add-run').addEventListener('click', () => {
        const firstWb = runsEl.querySelector('.run-block [name$="[waterbath_number]"]');

        runsEl.insertAdjacentHTML('beforeend', runTpl.replaceAll('__INDEX__', runCounter++));
        const block = runsEl.lastElementChild;

        if (!copySnap(block, allBlocks()[0])) fillBlock(block, master);

        const wb = block.querySelector('[name$="[waterbath_number]"]');
        if (firstWb && wb) wb.value = firstWb.value; // nomor waterbath biasanya sama

        renumber();
    });

    runsEl.addEventListener('click', e => {
        const btn = e.target.closest('.btn-remove-run');
        if (!btn) return;
        if (confirm('Hapus pasteurisasi ulang ini?')) {
            btn.closest('.run-block').remove();
            renumber();
        }
    });

    runsEl.addEventListener('input', e => {
        if (!e.target.classList.contains('rng-act')) return;
        checkAct(e.target);
        updateFlag(e.target.closest('.run-block'));
    });

    /* ---------- event: ganti produk ---------- */
    const productSelect = document.querySelector('select[name="product_uuid"]');
    if (productSelect) {
        let prev = productSelect.value;
        let reverting = false;

        $(productSelect).on('change', () => {
            if (reverting) return;

            const isEdit = runsEl.dataset.edit === '1';
            if (isEdit && prev && productSelect.value !== prev &&
                !confirm('Ganti produk? Standar (Set / Std) akan mengikuti master produk baru. Nilai Act yang sudah diisi tidak berubah.')) {
                reverting = true;
                $(productSelect).val(prev).trigger('change');
                reverting = false;
                return;
            }

            prev = productSelect.value;
            loadMaster(productSelect.value, true);
        });
    }

    /* ---------- init ---------- */
    allBlocks().forEach(refreshBlock);
    renumber();
    // Edit / validasi gagal: jangan timpa snapshot yang sudah ada, cukup siapkan master untuk blok baru
    loadMaster(runsEl.dataset.product, false);
});
</script>