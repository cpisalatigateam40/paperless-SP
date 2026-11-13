<style>
    .out-of-std, .out-of-std:focus { color: #dc3545 !important; font-weight: 600; }
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const TOL = 0.001;        // toleransi desimal untuk suhu & tekanan
    const TIME_TOL = 0;       // toleransi menit untuk Act Time vs Set Time
    const MASTER_URL = "{{ route('report_pasteurs.master', ':uuid') }}";

    /* ---------- helper ---------- */
    const isNil = v => v === null || v === undefined || v === '';
    const fmt   = v => isNil(v) ? '-' : String(+Number(v).toFixed(3));
    const range = (min, max) => isNil(min) ? '-' : fmt(min) + (isNil(max) ? '' : ' – ' + fmt(max));

    // Nilai tunggal (max kosong): harus sama dgn min. Range: harus di antara min-max.
    const outOfStd = (val, min, max, tol = TOL) => {
        if (isNil(val) || isNil(min)) return false;
        val = parseFloat(val); min = parseFloat(min);
        if (isNil(max)) return Math.abs(val - min) > tol;
        return val < min - tol || val > parseFloat(max) + tol;
    };

    const toMin = t => { if (!t) return null; const [h, m] = t.split(':').map(Number); return h * 60 + m; };
    const diff  = (s, e) => { if (s === null || e === null) return null; let d = e - s; if (d < 0) d += 1440; return d; }; // lewat tengah malam

    /* ---------- render satu run ---------- */
    function renderRun(run) {
        const std   = readStd(run);
        const steps = std.steps || {};

        // Initial temperature
        const ini = run.querySelector('.inp-initial');
        run.querySelector('.set-initial').textContent = range(std.init_min, std.init_max);
        ini.classList.toggle('out-of-std', outOfStd(ini.value, std.init_min, std.init_max));

        // Total time (jam mulai & selesai satu kali per run)
        const start = run.querySelector('.inp-run-start').value;
        const end   = run.querySelector('.inp-run-end').value;
        const total = diff(toMin(start), toMin(end));
        run.querySelector('.total-time').textContent =
            total === null ? '-' : `${start} - ${end} (${total} menit)`;

        // Per step
        run.querySelectorAll('.step-row').forEach(tr => {
            const s = steps[tr.dataset.order] || {};

            tr.querySelector('.set-time').textContent     = fmt(s.set_time);
            tr.querySelector('.set-water').textContent    = range(s.water_temp_min, s.water_temp_max);
            tr.querySelector('.set-pressure').textContent = range(s.pressure_min, s.pressure_max);

            const a = tr.querySelector('.inp-act-time');
            const w = tr.querySelector('.inp-water');
            const p = tr.querySelector('.inp-pressure');
            a.classList.toggle('out-of-std', outOfStd(a.value, s.set_time, null, TIME_TOL));
            w.classList.toggle('out-of-std', outOfStd(w.value, s.water_temp_min, s.water_temp_max));
            p.classList.toggle('out-of-std', outOfStd(p.value, s.pressure_min, s.pressure_max));
        });
    }

    function renumber(detail) {
        detail.querySelectorAll('.run-item').forEach((run, i) => {
            run.querySelector('.run-title').textContent =
                i === 0 ? 'Hasil Verifikasi' : 'Pasteurisasi Ulang' + (i > 1 ? ' #' + i : '');
        });
    }

    function applyStd(detail, std) {
        detail.querySelectorAll('.run-item').forEach(run => {
            run.dataset.std = JSON.stringify(std);
            renderRun(run);
        });
    }

    /* ---------- autofill dari master ---------- */
    $(document).on('change', '.select2-product', function () {
        const detail = this.closest('.detail-item');
        const warn   = detail.querySelector('.master-warning');

        if (!this.value) { applyStd(detail, {}); warn.classList.add('d-none'); return; }

        fetch(MASTER_URL.replace(':uuid', this.value), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                if (!isNil(d.gramase)) detail.querySelector('.inp-gramase').value = +d.gramase;

                applyStd(detail, d.found
                    ? { init_min: d.initial_temp_min, init_max: d.initial_temp_max, steps: d.steps }
                    : {});
                warn.classList.toggle('d-none', d.found);
            })
            .catch(() => alert('Gagal mengambil master standar. Coba pilih produk lagi.'));
    });

    /* ---------- tambah / hapus pasteurisasi ulang ---------- */
    $(document).on('click', '.btn-add-run', function () {
        const detail  = this.closest('.detail-item');
        const wrapper = detail.querySelector('.runs-wrapper');
        const idx     = parseInt(detail.dataset.nextRun, 10);
        detail.dataset.nextRun = idx + 1;

        wrapper.insertAdjacentHTML('beforeend',
            detail.querySelector('.run-template').innerHTML.replaceAll('__RI__', idx));

        const run = wrapper.lastElementChild;
        run.dataset.std = wrapper.querySelector('.run-item').dataset.std; // standar sama dgn run pertama
        renderRun(run);
        renumber(detail);
    });

    $(document).on('click', '.btn-remove-run', function () {
        if (!confirm('Hapus data pasteurisasi ulang ini?')) return;
        const detail = this.closest('.detail-item');
        this.closest('.run-item').remove();
        renumber(detail);
    });

    document.addEventListener('input', e => {
        const run = e.target.closest('.run-item');
        if (run) renderRun(run);
    });

    /* ---------- init ---------- */
    document.querySelectorAll('.run-item').forEach(renderRun);
    document.querySelectorAll('.detail-item').forEach(detail => {
        renumber(detail);
        const sel = detail.querySelector('.select2-product');
        if (detail.dataset.autofill === '1' && sel.value) $(sel).trigger('change');
    });

    /* ---------- signature ---------- */
    const pads = [];
    document.querySelectorAll('canvas').forEach(canvas => {
        const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)', penColor: 'rgb(0,0,0)' });
        pads.push({ pad, canvas });

        const clearBtn = canvas.parentElement.querySelector('.clear-signature');
        if (clearBtn) clearBtn.addEventListener('click', () => {
            pad.clear();
            const input = document.getElementById(canvas.dataset.input);
            if (input) input.value = '';
        });
    });

    document.querySelector('form#pasteur-form').addEventListener('submit', () => {
        pads.forEach(({ pad, canvas }) => {
            const input = document.getElementById(canvas.dataset.input);
            if (input && !pad.isEmpty()) input.value = pad.toDataURL('image/png');
        });
    });

    function readStd(run) {
        try { return JSON.parse(run.dataset.std || '{}') || {}; }
        catch (e) { return {}; }
    }
});
</script>