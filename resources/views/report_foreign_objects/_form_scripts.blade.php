<style>
    .fo-detail { border: 1px solid #e3e6ef; border-radius: .6rem; background: #fff; margin-bottom: 1.25rem;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04); }
    .fo-detail-head { display: flex; justify-content: space-between; align-items: center;
        padding: .75rem 1.1rem; background: #f6f8fc; border-bottom: 1px solid #e3e6ef;
        border-radius: .6rem .6rem 0 0; }
    .fo-detail-title { font-weight: 600; color: #1f2937; }
    .fo-no { background: #3b5bdb; color: #fff; border-radius: 999px; min-width: 1.8rem; height: 1.8rem;
        display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; font-weight: 600; }
    .fo-detail-body { padding: 1.25rem 1.1rem; }

    .fo-sec { margin-bottom: 1.75rem; padding-bottom: 1.5rem; border-bottom: 1px dashed #dde2ee; }
    .fo-sec.mb-0 { border-bottom: 0; padding-bottom: 0; }
    .fo-sec-head { display: flex; align-items: center; gap: .75rem; margin-bottom: 1rem; }
    .fo-sec-letter { width: 2rem; height: 2rem; border-radius: .5rem; background: #edf2ff; color: #3b5bdb;
        display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
    .fo-sec-title { font-weight: 600; color: #1f2937; line-height: 1.2; }
    .fo-sec-sub { font-size: .78rem; color: #8a94a6; }

    .fo-label { font-size: .78rem; font-weight: 600; color: #4b5563; margin-bottom: .3rem; }
    .fo-label .req { color: #e03131; }
    .fo-hint { font-size: .72rem; font-weight: 400; color: #8a94a6; margin-left: .35rem; }

    .fo-detail .form-control, .fo-detail .form-select, .fo-detail .input-group-text {
        border-color: #d5dbe8; font-size: .9rem; }
    .fo-detail .form-control:focus, .fo-detail .form-select:focus {
        border-color: #3b5bdb; box-shadow: 0 0 0 .2rem rgba(59, 91, 219, .15); }
    .fo-detail .input-group-text { background: #f6f8fc; color: #6b7280; }
    .fo-detail .select2-container { width: 100% !important; }
    .fo-detail .select2-container--default .select2-selection--single {
        height: calc(1.5em + .75rem + 2px); border: 1px solid #d5dbe8; border-radius: .375rem; }
    .fo-detail .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + .75rem); font-size: .9rem; }
    .fo-detail .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .75rem); }

    .evidence-existing, .evidence-preview { display: flex; flex-wrap: wrap; gap: .5rem; margin: .5rem 0; }
    .evidence-thumb { position: relative; width: 88px; height: 88px; }
    .evidence-thumb img { width: 100%; height: 100%; object-fit: cover; border-radius: .5rem;
        border: 1px solid #e3e6ef; background: #fff; }
    .evidence-remove { position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%;
        border: 0; background: #e03131; color: #fff; font-size: 14px; line-height: 1; cursor: pointer; padding: 0; }

    .fo-actions { position: sticky; bottom: 0; z-index: 5; display: flex; justify-content: flex-end; gap: .5rem;
        padding: .85rem 0; background: linear-gradient(to top, #fff 75%, rgba(255, 255, 255, 0)); }
</style>

<script>
    function initDetailPlugins(scope) {
        if (window.jQuery && jQuery.fn.select2) {
            jQuery(scope).find('select.select2-detail').select2({ width: '100%' });
        }
    }

    function refreshDetailBlocks() {
        const blocks = document.querySelectorAll('#details-wrapper .detail-block');
        blocks.forEach((block, i) => {
            block.querySelector('.detail-number').textContent = i + 1;
            block.querySelector('.btn-remove-detail').style.display = blocks.length > 1 ? '' : 'none';
        });
    }

    function nextDetailIndex() {
        let max = -1;
        document.querySelectorAll('#details-wrapper .detail-block').forEach(b => {
            max = Math.max(max, parseInt(b.dataset.index, 10) || 0);
        });
        return max + 1;
    }

    function addDetail() {
        const index = nextDetailIndex();
        const html = document.getElementById('detail-template').innerHTML.replaceAll('__INDEX__', index);
        const wrapper = document.getElementById('details-wrapper');

        wrapper.insertAdjacentHTML('beforeend', html);
        initDetailPlugins(wrapper.lastElementChild);
        wrapper.lastElementChild.querySelectorAll('.investigation-list').forEach(refreshInvestigation);
        refreshDetailBlocks();
    }

    function removeDetail(btn) {
        if (document.querySelectorAll('#details-wrapper .detail-block').length <= 1) return;
        btn.closest('.detail-block').remove();
        refreshDetailBlocks();
    }

    document.addEventListener('DOMContentLoaded', function () {
        initDetailPlugins(document);
        refreshDetailBlocks();
    });

    const MAX_INVESTIGATION = 5;

    function refreshInvestigation(list) {
        const rows = list.querySelectorAll('.investigation-row');

        rows.forEach((row, i) => {
            row.querySelector('.investigation-no').textContent = i + 1;
            row.querySelector('.btn-remove-investigation').style.display = rows.length > 1 ? '' : 'none';
        });

        const addBtn = list.parentElement.querySelector('.btn-add-investigation');
        addBtn.style.display = rows.length >= MAX_INVESTIGATION ? 'none' : '';
    }

    document.addEventListener('click', function (e) {
        const addBtn = e.target.closest('.btn-add-investigation');
        if (addBtn) {
            const list = addBtn.parentElement.querySelector('.investigation-list');
            if (list.querySelectorAll('.investigation-row').length >= MAX_INVESTIGATION) return;

            const row = document.createElement('div');
            row.className = 'investigation-row input-group mb-2';
            row.innerHTML = `
                <span class="input-group-text investigation-no"></span>
                <textarea class="form-control" rows="2" placeholder="Analisa asal kontaminasi"></textarea>
                <button type="button" class="btn btn-outline-danger btn-remove-investigation" title="Hapus">&times;</button>`;
            row.querySelector('textarea').name = list.dataset.name;

            list.appendChild(row);
            refreshInvestigation(list);
            row.querySelector('textarea').focus();
            return;
        }

        const removeBtn = e.target.closest('.btn-remove-investigation');
        if (removeBtn) {
            const list = removeBtn.closest('.investigation-list');
            if (list.querySelectorAll('.investigation-row').length <= 1) return;
            removeBtn.closest('.investigation-row').remove();
            refreshInvestigation(list);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.investigation-list').forEach(refreshInvestigation);
    });

    // ===== Evidence: multi-foto + kompres =====
    const COMPRESS_QUALITY = 0.7;
    const MAX_DIMENSION = 1280;
    const evidenceState = new WeakMap(); // input -> File[]

    function evidenceLimit(input) {
        const max = parseInt(input.dataset.max, 10) || 6;
        const kept = input.closest('.fo-evidence').querySelectorAll('.evidence-existing .evidence-thumb').length;
        return Math.max(0, max - kept);
    }

    function syncEvidence(input, files) {
        const dt = new DataTransfer();
        files.forEach(f => dt.items.add(f));
        input.files = dt.files;
        evidenceState.set(input, files);

        const preview = input.closest('.fo-evidence').querySelector('.evidence-preview');
        preview.innerHTML = '';

        files.forEach((file, i) => {
            const box = document.createElement('div');
            box.className = 'evidence-thumb';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.onload = () => URL.revokeObjectURL(img.src);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'evidence-remove';
            btn.title = 'Hapus foto';
            btn.innerHTML = '&times;';
            btn.addEventListener('click', () => {
                const rest = (evidenceState.get(input) || []).filter((_, idx) => idx !== i);
                syncEvidence(input, rest);
            });

            box.appendChild(img);
            box.appendChild(btn);
            preview.appendChild(box);
        });
    }

    // Pilih foto bisa berkali-kali: hasil pilihan baru digabung dengan yang sudah ada
    document.addEventListener('change', function (e) {
        const input = e.target.closest('.evidence-input');
        if (!input) return;

        const current = evidenceState.get(input) || [];
        const merged = current.slice();

        Array.from(input.files).forEach(f => {
            const dup = merged.some(x => x.name === f.name && x.size === f.size && x.lastModified === f.lastModified);
            if (!dup) merged.push(f);
        });

        const limit = evidenceLimit(input);
        if (merged.length > limit) {
            alert('Maksimal ' + (parseInt(input.dataset.max, 10) || 6) + ' foto per detail.');
        }

        syncEvidence(input, merged.slice(0, limit));
    });

    // Hapus foto lama (edit)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.evidence-existing .evidence-remove');
        if (!btn) return;
        btn.closest('.evidence-thumb').remove();
    });

    function compressImage(file, quality) {
        return new Promise((resolve, reject) => {
            if (!file.type.startsWith('image/')) { resolve(file); return; }

            const reader = new FileReader();
            reader.onerror = () => reject(new Error('Gagal membaca file: ' + file.name));

            reader.onload = function (ev) {
                const img = new Image();
                img.onerror = () => reject(new Error('Format gambar tidak didukung: ' + file.name));

                img.onload = function () {
                    let width = img.width, height = img.height;

                    if (width > height) {
                        if (width > MAX_DIMENSION) { height *= MAX_DIMENSION / width; width = MAX_DIMENSION; }
                    } else if (height > MAX_DIMENSION) {
                        width *= MAX_DIMENSION / height; height = MAX_DIMENSION;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#fff';               // PNG transparan -> latar putih, bukan hitam
                    ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function (blob) {
                        if (!blob) { reject(new Error('Gagal mengompres: ' + file.name)); return; }
                        resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' }));
                    }, 'image/jpeg', quality);
                };

                img.src = ev.target.result;
            };

            reader.readAsDataURL(file);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('details-wrapper');
        const form = wrapper ? wrapper.closest('form') : null;
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memproses foto...';
            }

            // input di dalam <template> tidak ikut karena tidak ada di DOM
            for (const input of form.querySelectorAll('input.evidence-input')) {
                if (!input.files || !input.files.length) continue;

                const dt = new DataTransfer();

                for (const file of input.files) {
                    try {
                        dt.items.add(await compressImage(file, COMPRESS_QUALITY));
                    } catch (err) {
                        dt.items.add(file); // gagal kompres: kirim file asli
                    }
                }

                input.files = dt.files;
            }

            form.submit();
        });
    });
</script>