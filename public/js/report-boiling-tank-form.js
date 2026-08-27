document.addEventListener('DOMContentLoaded', function () {
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
    });

    document.body.addEventListener('input', function (e) {
        if (e.target.classList.contains('detail-start-input') || e.target.classList.contains('detail-end-input')) {
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