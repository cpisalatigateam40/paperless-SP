@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Buat Laporan Verifikasi Proses Pembekuan, Pengemasan Sekunder, dan Release Produk</h4>
        </div>

        <div class="card-body">
            <form id="reportFreezPackagingForm" action="{{ route('report_freez_packagings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" >
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" >
                    </div>
                </div>

                <hr>

                <h5 class="mt-5">Detail Produk</h5>
                <div id="detail-container"></div>

                <div class="row">
                    <div class="col-md-12 mb-5">
                        <label>Catatan Laporan</label>
                        <textarea
                            name="notes"
                            class="form-control"
                            rows="3"
                            placeholder="Masukkan catatan laporan..."></textarea>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary" onclick="addDetailRow()">+ Tambah Baris
                    Detail</button>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('report_freez_packagings.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
let index = 0;
const productOptions = `
    @foreach($products as $product)
        <option value="{{ $product->uuid }}"
            data-shelf-life="{{ $product->shelf_life }}"
            data-created-at="{{ $product->created_at }}">
            {{ $product->product_name }}
        </option>
    @endforeach
`;

function addDetailRow() {
    const container = document.getElementById('detail-container');
    const now = new Date();
    const currentTime = now.toLocaleTimeString('it-IT', {
        hour: '2-digit',
        minute: '2-digit'
    }); // HH:mm

    $(document).ready(function() {
        $('.select2-product').select2({
            placeholder: '-- Pilih Produk --',
            allowClear: true,
            width: '100%'
        });
    });

    const html = `
<div class="card mb-4 p-3 border">
    <div class="d-flex justify-content-between align-items-center">
        <h6 style="font-weight: bold; margin-bottom: 1rem;">Detail #${index + 1}</h6>
        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.card').remove()">Hapus</button>
    </div>
    <div class="row detail-row">
        <div class="col-md-6 mb-3">
            <label>Produk</label>
            <select name="details[${index}][product_uuid]" class="form-control select2-product" onchange="updateBestBefore(this, ${index})">
                <option value="">- Pilih Produk -</option>
                ${productOptions}
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Gramase</label>
            <input type="number" step="0.01" name="details[${index}][gramase]" class="form-control"
                placeholder="Masukkan gramase" required>
        </div>
        <div class="col-md-6 mb-3">
            <label>Kode Produksi</label>
            <input type="text" name="details[${index}][production_code]" class="form-control production-code" placeholder="Kode Batch/Produksi">
        </div>
        <div class="col-md-6">
            <label>Best Before</label>
            <input type="date" name="details[${index}][best_before]" class="form-control best-before">
        </div>
    </div>

    <h6 class="mt-5 mb-3" style="font-weight: bold;">Pembekuan</h6>
    <div class="row mb-3">
        <div class="col-md-6">
            <label>Tipe Mesin</label>
            <select name="details[${index}][freezing][machine_type]" class="form-control">
                <option value="">Pilih Tipe Mesin</option>
                <option value="IQF">IQF</option>
                <option value="ABF">ABF</option>
            </select>
        </div>
        <div class="col-md-6">
            <label>Nama Mesin</label>
            <input type="text" name="details[${index}][freezing][iqf_machine]" class="form-control production-code" placeholder="Nama Mesin">
        </div>
    </div>
    <div class="row mt-2 mb-3">
        <div class="col-md-6">
            <label>Waktu Mulai</label>
            <input type="time" name="details[${index}][start_time]" class="form-control" value="${currentTime}">
        </div>
        <div class="col-md-6">
            <label>Waktu Akhir</label>
            <input type="time" name="details[${index}][end_time]" class="form-control" value="${currentTime}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <label>Standard Suhu Produk (°C)</label>
            <input type="number" step="0.0000001" 
                name="details[${index}][freezing][standard_temp]" 
                class="form-control"
                value="-18">
        </div>
        <div class="col-md-6">
            <label>Suhu Aktual Produk (°C)</label>

            <div class="actual-temp-wrapper">
                <div class="input-group mb-2">
                    <input type="number"
                        step="0.0000001"
                        name="details[${index}][freezing][actual_temps][]"
                        class="form-control">

                    <button type="button"
                        class="btn btn-success add-temp">
                        +
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Suhu Room IQF/ABF (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][iqf_room_temp]" class="form-control">
        </div>

        <div class="col-md-6 mb-3">
            <label>Catatan Pembekuan</label>
            <textarea
                name="details[${index}][freezing][notes]"
                class="form-control"
                rows="1"
                placeholder="Masukkan catatan..."></textarea>
        </div>

        <div class="col-md-12 mb-3">
            <label>Dokumentasi Pembekuan</label>

            <input
                type="file"
                name="details[${index}][documentation][]"
                class="form-control"
                accept="image/*"
                multiple>
        </div>
        
        <div class="col-md-6 d-none">
            <label>Suhu Suction IQF (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][iqf_suction_temp]" class="form-control">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6 d-none">
            <label>Durasi Display (menit)</label>
            <input type="number" name="details[${index}][freezing][freezing_time_display]" class="form-control">
        </div>
        
        <div class="col-md-6 d-none">
            <label>Durasi Aktual (menit)</label>
            <input type="number" name="details[${index}][freezing][freezing_time_actual]" class="form-control">
        </div>
    </div>

    <h6 class="mt-5 mb-3" style="font-weight: bold;">Pengemasan Sekunder</h6>
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">Kondisi Kemasan Sekunder</label>
            <select name="details[${index}][kartoning][carton_condition]" class="form-control">
                <option value="✓">✓</option>
                <option value="x">x</option>
            </select>
        </div>
        <div class="col-md-6">
            <label>Label Kemasan Sekunder</label>
            <select name="details[${index}][kartoning][label_condition]" class="form-control">
                <option value="">Pilih</option>
                <option value="OK">OK</option>
                <option value="Tidak OK">Tidak OK</option>
            </select>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label>Isi Per Kemasan Sekunder</label>
            <input type="number" name="details[${index}][kartoning][content_bag]" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label>Isi Per Inner *RTG</label>
            <input type="number" name="details[${index}][kartoning][content_rtg]" class="form-control">
        </div>
        <div class="col-md-6">
            <label>Isi per binded *prod. binded</label>
            <input type="number" name="details[${index}][kartoning][content_binded]" class="form-control">
        </div>
        
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label>Standar Berat Hasil Kartoning (kg)</label>
            <input type="text" name="details[${index}][kartoning][carton_weight_standard]" class="form-control" placeholder="contoh: 12-13">
        </div>
    </div>
    
    <div class="row kartoning-group mt-3" data-index="${index}">
        <div class="col-md-2">
            <label>Berat Aktual Hasil Karton 1</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][weight_1]" class="form-control weight-input">
        </div>
        <div class="col-md-2">
            <label>Berat Aktual Hasil Karton 2</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][weight_2]" class="form-control weight-input">
        </div>
        <div class="col-md-2">
            <label>Berat Aktual Hasil Karton 3</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][weight_3]" class="form-control weight-input">
        </div>
        <div class="col-md-2">
            <label>Berat Aktual Hasil Karton 4</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][weight_4]" class="form-control weight-input">
        </div>
        <div class="col-md-2">
            <label>Berat Aktual Hasil Karton 5</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][weight_5]" class="form-control weight-input">
        </div>
        <div class="col-md-2">
            <label>Rata-Rata Berat</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][avg_weight]" class="form-control avg-weight">
        </div>
    </div>

    <div class="row mt-3 mb-3">
        <div class="col-md-6 mb-3">
            <label>Catatan Pengemasan Sekunder</label>
            <textarea
                name="details[${index}][kartoning][notes]"
                class="form-control"
                rows="1"></textarea>
        </div>

        <div class="col-md-12 mb-3">
            <label>Dokumentasi Pengemasan Sekunder</label>

            <input
                type="file"
                name="details[${index}][kartoning_documentation][]"
                class="form-control"
                accept="image/*"
                multiple>
        </div>
    </div>



    <h6 class="mt-5 mb-3" style="font-weight: bold;">Status Produk</h6>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Status Release</label>
            <select name="details[${index}][release_status]" class="form-control">
                <option value="">Pilih Status</option>
                <option value="Release">Release</option>
                <option value="Hold">Hold</option>
            </select>
        </div>

        <div class="col-md-6">
            <label>Tindakan Perbaikan</label>
            <input type="text" name="details[${index}][corrective_action]" class="form-control">
        </div>

        <div class="col-md-6 mb-3">
            <label>Catatan Status Produk</label>
            <textarea
                name="details[${index}][notes]"
                class="form-control"
                rows="1"></textarea>
        </div>

        <div class="col-md-6 d-none">
            <label class="form-label">Verifikasi Setelah Tindakan Perbaikan</label>
            <select name="details[${index}][verif_after]" class="form-control">
                <option value="✓">✓</option>
                <option value="x">x</option>
            </select>
        </div>
    </div>
</div>
`;
    container.insertAdjacentHTML('beforeend', html);
    index++;
}

window.onload = () => addDetailRow();

function updateBestBefore(select, index) {
    const option = select.options[select.selectedIndex];
    const shelfLife = parseInt(option.getAttribute('data-shelf-life'));
    const createdAt = option.getAttribute('data-created-at');

    if (!shelfLife || !createdAt) return;

    const createdDate = new Date(createdAt);
    createdDate.setMonth(createdDate.getMonth() + shelfLife);

    const yyyy = createdDate.getFullYear();
    const mm = String(createdDate.getMonth() + 1).padStart(2, '0');
    const dd = String(createdDate.getDate()).padStart(2, '0');

    const formattedDate = `${yyyy}-${mm}-${dd}`;
    const bestBeforeInput = document.querySelector(`input[name="details[${index}][best_before]"]`);
    if (bestBeforeInput) bestBeforeInput.value = formattedDate;
}

document.addEventListener("input", function(e) {
    if (e.target.classList.contains("weight-input")) {
        const group = e.target.closest(".kartoning-group");
        const weightInputs = group.querySelectorAll(".weight-input");
        const avgInput = group.querySelector(".avg-weight");

        let total = 0;
        let count = 0;

        weightInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) {
                total += val;
                count++;
            }
        });

        avgInput.value = count > 0 ? (total / count).toFixed(2) : "";
    }
});
</script>

<script>
function formatDateLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function parseBatchCodeToDate(batchCode) {
    if (!batchCode || batchCode.length < 4) {
        return null;
    }

    try {
        const yearChar = batchCode[0].toUpperCase();
        const baseYear = 2009;
        const year = baseYear + (yearChar.charCodeAt(0) - 'A'.charCodeAt(0));

        const monthChar = batchCode[1].toUpperCase();
        const month = (monthChar.charCodeAt(0) - 'A'.charCodeAt(0)) + 1;

        const day = parseInt(batchCode.substring(2, 4), 10);

        if (
            isNaN(year) ||
            isNaN(month) || month < 1 || month > 12 ||
            isNaN(day) || day < 1 || day > 31
        ) {
            return null;
        }

        return new Date(year, month - 1, day);
    } catch (e) {
        return null;
    }
}

function calculateExpirationDate(batchCode, expirationMonths) {
    const productionDate = parseBatchCodeToDate(batchCode);

    if (!productionDate || isNaN(expirationMonths)) {
        return null;
    }

    const originalDay = productionDate.getDate();

    let expirationDate = new Date(
        productionDate.getFullYear(),
        productionDate.getMonth(),
        originalDay
    );

    expirationDate.setMonth(expirationDate.getMonth() + expirationMonths);

    const lastDayOfNewMonth = new Date(
        expirationDate.getFullYear(),
        expirationDate.getMonth() + 1,
        0
    ).getDate();

    expirationDate.setDate(Math.min(originalDay, lastDayOfNewMonth));

    return {
        production_date: formatDateLocal(productionDate),
        expiration_date: formatDateLocal(expirationDate)
    };
}


document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('production-code')) return;

    const row = e.target.closest('.detail-row');
    const bestBeforeInput = row.querySelector('.best-before');

    // ambil QA01
    const match = e.target.value.match(/([A-Z]{2}\d{2})/i);
    if (!match) {
        bestBeforeInput.value = '';
        return;
    }

    const batchCode = match[1].toUpperCase();
    const expirationMonths = 24;

    const result = calculateExpirationDate(batchCode, expirationMonths);
    bestBeforeInput.value = result ? result.expiration_date : '';
});

document.addEventListener('click', function(e) {

    if (e.target.classList.contains('add-temp')) {

        const wrapper = e.target.closest('.actual-temp-wrapper');
        const inputName = wrapper.querySelector('input').name;

        const div = document.createElement('div');
        div.className = 'input-group mb-2';

        div.innerHTML = `
            <input type="number"
                step="0.0000001"
                name="${inputName}"
                class="form-control">

            <button type="button"
                class="btn btn-danger remove-temp">
                -
            </button>
        `;

        wrapper.appendChild(div);
    }

    if (e.target.classList.contains('remove-temp')) {
        e.target.closest('.input-group').remove();
    }
});

// ===== Compress & Validasi Upload File =====
    const MAX_SIZE_MB = 2;
    const MAX_FILES = 10;
    const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024;
    const COMPRESS_QUALITY = 0.7; // 70% kualitas JPEG

    // Fungsi compress 1 file gambar
    function compressImage(file, quality) {
        return new Promise((resolve, reject) => {
            if (!file.type.startsWith('image/')) {
                resolve(file);
                return;
            }

            const reader = new FileReader();
            reader.onerror = () => reject(new Error('Gagal membaca file: ' + file.name));

            reader.onload = function(e) {
                const img = new Image();
                img.onerror = () => reject(new Error('Format gambar tidak didukung: ' + file.name));

                img.onload = function() {
                    let width = img.width;
                    let height = img.height;
                    const MAX_DIMENSION = 1280;

                    if (width > height) {
                        if (width > MAX_DIMENSION) {
                            height *= MAX_DIMENSION / width;
                            width = MAX_DIMENSION;
                        }
                    } else {
                        if (height > MAX_DIMENSION) {
                            width *= MAX_DIMENSION / height;
                            height = MAX_DIMENSION;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            reject(new Error('Gagal mengompres: ' + file.name));
                            return;
                        }
                        resolve(new File(
                            [blob],
                            file.name.replace(/\.[^.]+$/, '.jpg'),
                            { type: 'image/jpeg' }
                        ));
                    }, 'image/jpeg', quality);
                };

                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        });
    }

document.getElementById('reportFreezPackagingForm')
.addEventListener('submit', async function(e) {

    e.preventDefault();

    const form = this;

    const fileInputs = form.querySelectorAll(
        'input[type="file"]'
    );

    for (const input of fileInputs) {

        if (!input.files || !input.files.length) {
            continue;
        }

        const dataTransfer = new DataTransfer();

        for (const file of input.files) {

            let compressedFile;

            try {
                compressedFile = await compressImage(
                    file,
                    COMPRESS_QUALITY
                );
            } catch (err) {
                compressedFile = file;
            }

            dataTransfer.items.add(compressedFile);
        }

        input.files = dataTransfer.files;
    }

    form.submit();
});

</script>
@endsection