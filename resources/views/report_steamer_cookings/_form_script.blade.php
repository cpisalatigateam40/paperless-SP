<script>
let batchIndex = {{ isset($report) ? $report->batches->count() : 1 }};
let currentStandard = null; // simpan standar produk yg lagi aktif

const sensoryFields = ['sensory_bentuk','sensory_warna','sensory_aroma','sensory_rasa','sensory_tekstur'];

function detailRowTemplate(bIdx, dIdx) {
    let sensoryTds = sensoryFields.map(f => `
        <td>
            <select name="batches[${bIdx}][details][${dIdx}][${f}]" class="form-control form-control-sm">
                <option value="OK">OK</option>
                <option value="Tidak OK">Tidak OK</option>
            </select>
        </td>`).join('');

    return `
    <tr class="detail-row" data-detail-index="${dIdx}">
        <input type="hidden" name="batches[${bIdx}][details][${dIdx}][uuid]" value="">
        <td><input type="text" name="batches[${bIdx}][details][${dIdx}][production_code]" class="form-control form-control-sm" placeholder="mis: QF27801AA0 "></td>
        <td><input type="time" name="batches[${bIdx}][details][${dIdx}][start_process]" class="form-control form-control-sm"></td>
        <td><input type="time" name="batches[${bIdx}][details][${dIdx}][end_process]" class="form-control form-control-sm"></td>
        <td><input type="number" name="batches[${bIdx}][details][${dIdx}][setup_time]" class="form-control form-control-sm setup-time-input" value="${currentStandard?.setup_time_min ?? ''}" placeholder="mis: 12"></td>
        <td><input type="number" step="0.01" name="batches[${bIdx}][details][${dIdx}][room_temp]" class="form-control form-control-sm room-temp-input" value="${currentStandard?.room_temp_min ?? ''}" placeholder="mis: 12"></td>
        <td class="core-temp-wrapper">
            <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addCoreTemp(this)">+ Titik</button>
        </td>
        ${sensoryTds}
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeDetail(this)">&times;</button></td>
    </tr>`;
}

function batchBlockTemplate(bIdx) {
    return `
    <div class="batch-block card mb-4" data-batch-index="${bIdx}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Steamer Batch #${bIdx + 1}</strong>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBatch(this)">Hapus Batch</button>
        </div>
        <div class="card-body">
            <input type="hidden" name="batches[${bIdx}][uuid]" value="">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label>Nomor Steamer</label>
                    <input type="text" name="batches[${bIdx}][steamer_number]" class="form-control" placeholder="mis: 1">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Jumlah Trolly</label>
                    <input type="number" name="batches[${bIdx}][trolley_count]" class="form-control" placeholder="mis: 2">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Tray/Trolly</label>
                    <input type="number" name="batches[${bIdx}][tray_per_trolley]" class="form-control" placeholder="mis: 20">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Waktu Proses</label>
                    <div class="d-flex gap-1">
                        <input type="time" name="batches[${bIdx}][start_time]" class="form-control">
                        <input type="time" name="batches[${bIdx}][end_time]" class="form-control">
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-sm align-middle detail-table">
                <thead>
                    <tr>
                        <th style="min-width:120px">Kode Produksi</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Setup (mnt)</th>
                        <th>Suhu Ruang</th>
                        <th style="min-width:140px">Core Temp</th>
                        <th>Bentuk</th>
                        <th>Warna</th>
                        <th>Aroma</th>
                        <th>Rasa</th>
                        <th>Tekstur</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="detail-wrapper" data-batch-index="${bIdx}" data-detail-counter="1">
                    ${detailRowTemplate(bIdx, 0)}
                </tbody>
            </table>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addDetail(this)">+ Tambah Baris</button>
        </div>
    </div>`;
}

function addBatch() {
    document.getElementById('batchWrapper').insertAdjacentHTML('beforeend', batchBlockTemplate(batchIndex));
    batchIndex++;
}

function removeBatch(button) {
    if (document.querySelectorAll('.batch-block').length > 1) {
        button.closest('.batch-block').remove();
    } else {
        alert('Minimal 1 batch harus ada');
    }
}

function addDetail(button) {
    let tbody = button.closest('.card-body').querySelector('.detail-wrapper');
    let bIdx = tbody.dataset.batchIndex;
    let dIdx = parseInt(tbody.dataset.detailCounter);

    tbody.insertAdjacentHTML('beforeend', detailRowTemplate(bIdx, dIdx));
    tbody.dataset.detailCounter = dIdx + 1;
}

function removeDetail(button) {
    let tbody = button.closest('.detail-wrapper');
    if (tbody.querySelectorAll('.detail-row').length > 1) {
        button.closest('.detail-row').remove();
    } else {
        alert('Minimal 1 baris kode produksi harus ada');
    }
}

function addCoreTemp(button) {
    let wrapper = button.closest('.core-temp-wrapper');
    let row = button.closest('.detail-row');
    let tbody = button.closest('.detail-wrapper');
    let bIdx = tbody.dataset.batchIndex;
    let dIdx = row.dataset.detailIndex;
    let seq = wrapper.querySelectorAll('.core-temp-item').length + 1;

    let html = `
        <div class="core-temp-item">
            <span class="core-temp-badge">${seq}</span>
            <input type="number" step="0.01" name="batches[${bIdx}][details][${dIdx}][core_temps][]" class="form-control form-control-sm" placeholder="°C">
            <button type="button" class="core-temp-remove" onclick="this.closest('.core-temp-item').remove()" title="Hapus titik">&times;</button>
        </div>`;

    button.insertAdjacentHTML('beforebegin', html);
}

// Isi ulang Setup Time & Suhu Ruang di SEMUA baris yang kosong, sesuai standar produk terpilih
function fillStandardDefaults() {
    if (!currentStandard) return;

    document.querySelectorAll('.setup-time-input').forEach(input => {
        if (!input.value) input.value = currentStandard.setup_time_min ?? '';
    });
    document.querySelectorAll('.room-temp-input').forEach(input => {
        if (!input.value) input.value = currentStandard.room_temp_min ?? '';
    });
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('.select2-product').select2({
        placeholder: '-- pilih produk --',
        width: '100%'
    });

    $('#product_uuid').on('change', function() {
        let productUuid = $(this).val();
        if (!productUuid) {
            $('#standardInfo').addClass('d-none');
            currentStandard = null;
            return;
        }

        $.get(`/report-steamer-cookings/standard/${productUuid}`, function(res) {
            if (res.found) {
                currentStandard = res;

                $('#std_room_temp').text(`${res.room_temp_min ?? '-'} - ${res.room_temp_max ?? '-'}`);
                $('#std_setup_time').text(`${res.setup_time_min ?? '-'} - ${res.setup_time_max ?? '-'}`);
                $('#std_core_temp').text(`${res.core_temp_min ?? '-'} - ${res.core_temp_max ?? '-'}`);
                $('#standardInfo').removeClass('d-none');

                fillStandardDefaults();
            } else {
                currentStandard = null;
                $('#standardInfo').addClass('d-none');
            }
        });
    });

    // trigger sekali di awal, berguna untuk mode edit (produk sudah terpilih)
    $('#product_uuid').trigger('change');
});
</script>