document.addEventListener('DOMContentLoaded', function () {
    let currentStandard = null;

    const fieldMap = {
        'std-suhu-tangki-1': { label: 'suhu_tangki_1_label', min: 'suhu_tangki_1_min', unit: '°C', input: '.detail-suhu-tangki-1-input' },
        'std-suhu-tangki-2': { label: 'suhu_tangki_2_label', min: 'suhu_tangki_2_min', unit: '°C', input: '.detail-suhu-tangki-2-input' },
        'std-berat-mentah': { label: 'berat_mentah_label', min: 'berat_mentah_min', unit: 'gr', input: '.check-berat-mentah-input' },
        'std-actual-core-temp': { label: 'actual_core_temp_label', min: 'actual_core_temp_min', unit: '°C', input: '.check-actual-core-temp-input' },
        'std-berat-matang': { label: 'berat_matang_label', min: 'berat_matang_min', unit: 'gr', input: '.check-berat-matang-input' },
    };

    // Tandai semua input yang sudah berisi dari server (data draft) sebagai "touched"
    // supaya tidak tertimpa nilai master standar.
    Object.values(fieldMap).forEach(function (cfg) {
        document.querySelectorAll(cfg.input).forEach(function (input) {
            if (input.value !== '') input.dataset.touched = '1';
        });
    });

    function applyStandardToDom(fillValues = true) {
        Object.entries(fieldMap).forEach(function ([labelClass, cfg]) {
            // Update teks "Std ..." (selalu)
            document.querySelectorAll('.' + labelClass).forEach(function (el) {
                const label = currentStandard?.[cfg.label];
                el.textContent = label ? `Std ${label}${cfg.unit === '°C' ? '°C' : ' ' + cfg.unit}` : '';
            });

            if (!fillValues) return;

            // Auto-isi hanya input yang kosong dan belum disentuh
            const min = currentStandard?.[cfg.min];
            if (min === undefined || min === null || min === '') return;

            document.querySelectorAll(cfg.input).forEach(function (input) {
                if (input.dataset.touched !== '1' && input.value === '') {
                    input.value = min;
                }
            });
        });
    }

    function fetchStandard(productUuid, fillValues = true) {
        if (!productUuid) {
            currentStandard = null;
            applyStandardToDom(false);
            return;
        }

        fetch(`/master-boiling-tank-standard/by-product/${productUuid}`, {
            headers: { 'Accept': 'application/json' },
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                currentStandard = data.found ? data : null;
                applyStandardToDom(fillValues);
            })
            .catch(function () {
                currentStandard = null;
            });
    }

    // User mengganti produk -> label + isi field yang kosong
    document.getElementById('productSelect')?.addEventListener('change', function () {
        fetchStandard(this.value, true);
    });

    // Edit mode: produk sudah terpilih -> hanya ambil standar untuk label, jangan isi nilai
    const initialProductUuid = document.getElementById('productSelect')?.value;
    if (initialProductUuid) {
        fetchStandard(initialProductUuid, false);
    }


    let detailCounter = document.querySelectorAll('#detailListContainer .detail-card').length;

    function renderTemplate(templateId, replacements) {
        let html = document.getElementById(templateId).innerHTML;
        for (const [key, value] of Object.entries(replacements)) {
            html = html.replaceAll(key, value);
        }
        return html;
    }

    function markTouched(input) {
        input.dataset.touched = '1';
    }

    // Header Waktu Proses Start/End -> sync ke tiap Kode Produksi
    // Baris yang sudah diedit manual (touched) tidak ikut ke-overwrite
    function syncHeaderTime(headerInput, hiddenInput, targetClass) {
        headerInput.addEventListener('input', function () {
            hiddenInput.value = headerInput.value;
            document.querySelectorAll('.' + targetClass).forEach(function (input) {
                if (input.dataset.touched !== '1') {
                    input.value = headerInput.value;
                }
            });
        });
    }
    syncHeaderTime(
        document.getElementById('waktuProsesStart'),
        document.getElementById('waktuProsesStartHidden'),
        'detail-start-input'
    );
    syncHeaderTime(
        document.getElementById('waktuProsesEnd'),
        document.getElementById('waktuProsesEndHidden'),
        'detail-end-input'
    );

    document.getElementById('addDetailBtn').addEventListener('click', function () {
        const dKey = 'new' + Date.now() + detailCounter++;
        const html = renderTemplate('detailRowTemplate', { '__DKEY__': dKey });
        document.getElementById('detailListContainer').insertAdjacentHTML('beforeend', html);

        // Kartu baru langsung ikut nilai header saat ini
        const newCard = document.querySelector(`.detail-card[data-dkey="${dKey}"]`);
        newCard.querySelector('.detail-start-input').value = document.getElementById('waktuProsesStartHidden').value;
        newCard.querySelector('.detail-end-input').value = document.getElementById('waktuProsesEndHidden').value;

        applyStandardToDom(true);
    });

    document.body.addEventListener('input', function (e) {
        if (
            e.target.classList.contains('detail-start-input') ||
            e.target.classList.contains('detail-end-input') ||
            e.target.classList.contains('detail-suhu-tangki-1-input') ||
            e.target.classList.contains('detail-suhu-tangki-2-input') ||
            e.target.classList.contains('check-berat-mentah-input') ||
            e.target.classList.contains('check-actual-core-temp-input') ||
            e.target.classList.contains('check-berat-matang-input')
        ) {
            markTouched(e.target);
        }
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-detail-btn')) {
            e.target.closest('.detail-card').remove();
        }

        if (e.target.classList.contains('add-check-btn')) {
            const section = e.target.closest('.checks-section');
            const dKey = section.dataset.dkey;
            const list = section.querySelector('.checks-list');
            const cKey = 'new' + Date.now();
            const checkNumber = list.querySelectorAll('.check-row').length + 1;

            const html = renderTemplate('checkRowTemplate', {
                '__DKEY__': dKey,
                '__CKEY__': cKey,
                '__CNUM__': checkNumber,
            });
            list.insertAdjacentHTML('beforeend', html);

            applyStandardToDom(true);
        }

        if (e.target.classList.contains('remove-check-btn')) {
            const row = e.target.closest('.check-row');
            const list = row.closest('.checks-list');
            row.remove();
            renumberChecks(list);
        }
    });

    function renumberChecks(list) {
        list.querySelectorAll('.check-row').forEach((row, i) => {
            row.querySelector('.check-number').textContent = i + 1;
            row.querySelector('.check-index-input').value = i + 1;
        });
    }
});