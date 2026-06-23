@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Edit Verifikasi Proses Pembekuan, Pengemasan Sekunder, dan Release Produk</h4>
        </div>

        <div class="card-body">
            <form id="reportFreezPackagingForm" action="{{ route('report_freez_packagings.update', $report->uuid) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ $report->date }}">
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}">
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
                            placeholder="Masukkan catatan laporan...">{{ $report->notes }}</textarea>
                    </div>
                </div>

                <!-- <button type="button" class="btn btn-outline-primary" onclick="addDetailRow()">+ Tambah Baris Detail</button> -->
                <button type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('report_freez_packagings.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<!-- <pre>{{ $details->count() }} details</pre>
<pre>{{ json_encode($details, JSON_PRETTY_PRINT) }}</pre> -->

<script>

let index = 0;
// Simpan sebagai array JSON dulu
const productList = @json($products);

function renderProductOptions(selectedUuid = '') {
    return productList.map(p =>
        `<option value="${p.uuid}"
            data-shelf-life="${p.shelf_life}"
            data-created-at="${p.created_at}"
            ${p.uuid === selectedUuid ? 'selected' : ''}>
            ${p.product_name}
        </option>`
    ).join('');
}

function addDetailRow(detail = null) {
    const container = document.getElementById('detail-container');
    const now = new Date();
    const currentTime = now.toLocaleTimeString('it-IT', {hour:'2-digit', minute:'2-digit'});

    const currentIndex = index;

    let documentationHtml = '';

    if (detail?.documentations?.length) {

        detail.documentations.forEach(doc => {

            documentationHtml += `
                <div class="col-md-2 mb-2">
                    <div class="card">
                        <a href="/storage/${doc.image}" target="_blank">
                            <img
                                src="/storage/${doc.image}"
                                class="img-thumbnail"
                                style="
                                    width:100%;
                                    height:120px;
                                    object-fit:cover;
                                ">
                        </a>

                        <div class="card-body p-1 text-center">
                            <input
                                type="checkbox"
                                name="details[${index}][delete_documentations][]"
                                value="${doc.uuid}">
                            <small>Hapus</small>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    let kartoningDocumentationHtml = '';

    if (detail?.kartoning_documentations?.length) {

        detail.kartoning_documentations.forEach(doc => {

            kartoningDocumentationHtml += `
                <div class="col-md-2 mb-2">
                    <div class="card">
                        <a href="/storage/${doc.image}" target="_blank">
                            <img
                                src="/storage/${doc.image}"
                                class="img-thumbnail"
                                style="
                                    width:100%;
                                    height:120px;
                                    object-fit:cover;
                                ">
                        </a>

                        <div class="card-body p-1 text-center">
                            <input
                                type="checkbox"
                                name="details[${index}][delete_kartoning_documentations][]"
                                value="${doc.uuid}">
                            <small>Hapus</small>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    const html = `
    

<div class="card mb-4 p-3 border">
    <input type="hidden"
        name="details[${index}][uuid]"
        value="${detail.uuid ?? ''}">

    <div class="d-flex justify-content-between align-items-center">
        <h6 style="font-weight:bold; margin-bottom:1rem;">Detail #${index+1}</h6>
        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.card').remove()">Hapus</button>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Produk</label>
            <select name="details[${index}][product_uuid]" class="form-control select2-product" onchange="updateBestBefore(this, ${index})">
                <option value="">- Pilih Produk -</option>
                ${renderProductOptions(detail?.product_uuid ?? '')}
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Gramase</label>
            <input type="number" 
                step="0.01" 
                name="details[${index}][gramase]" 
                class="form-control"
                value="${detail?.gramase ?? ''}"
                placeholder="Masukkan gramase">
        </div>
        <div class="col-md-6 mb-3">
            <label>Kode Produksi</label>
            <input type="text" name="details[${index}][production_code]" class="form-control"
                value="${detail?.production_code ?? ''}">
        </div>
        <div class="col-md-6">
            <label>Best Before</label>
            <input type="date" name="details[${index}][best_before]" class="form-control"
                value="${detail?.best_before ?? ''}">
        </div>
    </div>

    

    <h6 class="mt-5 mb-3" style="font-weight:bold;">Pembekuan</h6>
    <div class="row mb-3">
        <div class="col-md-6">
            <label>Tipe Mesin</label>
            <select name="details[${index}][freezing][machine_type]" class="form-control">
                <option value="">Pilih Tipe Mesin</option>
                <option value="IQF" ${detail?.freezing?.machine_type === 'IQF' ? 'selected' : ''}>
                    IQF
                </option>
                <option value="ABF" ${detail?.freezing?.machine_type === 'ABF' ? 'selected' : ''}>
                    ABF
                </option>
            </select>
        </div>
        <div class="col-md-6">
            <label>Nama Mesin</label>
            <input type="text" name="details[${index}][freezing][iqf_machine]" class="form-control production-code" placeholder="Nama Mesin IQF" value="${detail?.freezing?.iqf_machine ?? ''}">
        </div>
    </div>
    <div class="row mt-2 mb-3">
        <div class="col-md-6">
            <label>Waktu Mulai</label>
            <input type="time" name="details[${index}][start_time]" class="form-control"
                value="${detail?.start_time ?? currentTime}">
        </div>
        <div class="col-md-6">
            <label>Waktu Akhir</label>
            <input type="time" name="details[${index}][end_time]" class="form-control"
                value="${detail?.end_time ?? currentTime}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <label>Standard Suhu Produk (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][standard_temp]" class="form-control"
                value="${detail?.freezing?.standard_temp ?? '-18'}">
        </div>
        <div class="col-md-6">
            <label>Suhu Aktual Produk (°C)</label>

            <div class="actual-temp-wrapper">

                ${
                    detail?.freezing?.actual_temps?.length
                    ?
                    detail.freezing.actual_temps.map(temp => `
                        <div class="input-group mb-2">
                            <input type="number"
                                step="0.0000001"
                                name="details[${index}][freezing][actual_temps][]"
                                class="form-control"
                                value="${Number(temp).toFixed(2)}"

                            <button type="button"
                                class="btn btn-danger remove-temp d-none">
                                -
                            </button>
                        </div>
                    `).join('')
                    :
                    `
                    <div class="input-group mb-2">
                        <input type="number"
                            step="0.0000001"
                            name="details[${index}][freezing][actual_temps][]"
                            class="form-control">
                    </div>
                    `
                }

                <button type="button"
                    class="btn btn-success btn-sm add-temp mt-2 d-none">
                    + Tambah Suhu
                </button>

            </div>
        </div>
        
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Suhu Room IQF/ABF (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][iqf_room_temp]" class="form-control"
                value="${detail?.freezing?.iqf_room_temp ?? ''}">
        </div>
        <div class="col-md-6 mb-3">
            <label>Catatan Pembekuan</label>
            <textarea
                name="details[${index}][freezing][notes]"
                class="form-control"
                rows="1"
                placeholder="Masukkan catatan...">${detail?.freezing?.notes ?? ''}</textarea>
        </div>
        <div class="col-md-12 mb-3">
            <label>Dokumentasi Pembekuan</label>

            <input
                type="file"
                name="details[${index}][documentation][]"
                class="form-control"
                accept="image/*"
                multiple>

            <div class="row mt-2">
                ${documentationHtml}
            </div>
        </div>
        <div class="col-md-6 d-none">
            <label>Suhu Suction IQF (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][iqf_suction_temp]" class="form-control"
                value="${detail?.freezing?.iqf_suction_temp ?? ''}">
        </div>
    </div>

    <div class="row mt-3 d-none">
        <div class="col-md-6">
            <label>Durasi Display (menit)</label>
            <input type="number" name="details[${index}][freezing][freezing_time_display]" class="form-control"
                value="${detail?.freezing?.freezing_time_display ?? ''}">
        </div>
        <div class="col-md-6">
            <label>Durasi Aktual (menit)</label>
            <input type="number" name="details[${index}][freezing][freezing_time_actual]" class="form-control"
                value="${detail?.freezing?.freezing_time_actual ?? ''}">
        </div>
    </div>

    <h6 class="mt-5 mb-3" style="font-weight:bold;">Pengemasan Sekunder</h6>
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">Kondisi Kemasan Sekunder</label>
            <select name="details[${index}][kartoning][carton_condition]" class="form-control">
                <option value="✓" ${detail?.kartoning?.carton_condition === '✓' ? 'selected' : ''}>✓</option>
                <option value="x" ${detail?.kartoning?.carton_condition === 'x' ? 'selected' : ''}>x</option>
            </select>
        </div>
        <div class="col-md-6">
            <label>Label Kemasan Sekunder</label>
            <select name="details[${index}][kartoning][label_condition]" class="form-control">
                <option value="">Pilih</option>
                <option value="OK" ${detail?.kartoning?.label_condition === 'OK' ? 'selected' : ''}>OK</option>
                <option value="Tidak OK" ${detail?.kartoning?.label_condition === 'Tidak OK' ? 'selected' : ''}>Tidak OK</option>
            </select>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Isi Per Kemasan Sekunder</label>
            <input type="number" name="details[${index}][kartoning][content_bag]" class="form-control"
                value="${detail?.kartoning?.content_bag ?? ''}">
        </div>
        <div class="col-md-6 mt-3">
            <label>Isi Per Inner *RTG</label>
            <input type="number" name="details[${index}][kartoning][content_rtg]" class="form-control"
                value="${detail?.kartoning?.content_rtg ?? ''}">
        </div>
        <div class="col-md-6">
            <label>Isi per binded *prod. binded</label>
            <input type="number" name="details[${index}][kartoning][content_binded]" class="form-control"
                value="${detail?.kartoning?.content_binded ?? ''}">
        </div>
        
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Standar Berat Hasil Kartoning (kg)</label>
            <input type="text" name="details[${index}][kartoning][carton_weight_standard]" class="form-control"
                value="${detail?.kartoning?.carton_weight_standard ?? ''}" placeholder="contoh: 12-13">
        </div>
    </div>

    <div class="row kartoning-group mt-3" data-index="${index}">
        ${[1,2,3,4,5].map(i => `
        <div class="col-md-2">
            <label>Berat Aktual Hasil Karton ${i}</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][weight_${i}]" class="form-control weight-input"
                value="${detail?.kartoning?.['weight_'+i] ?? ''}">
        </div>`).join('')}
        <div class="col-md-2">
            <label>Rata-Rata Berat</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][avg_weight]" class="form-control avg-weight"
                value="${detail?.kartoning?.avg_weight ?? ''}">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6 mb-3">
            <label>Catatan Pengemasan Sekunder</label>
            <textarea
                name="details[${index}][kartoning][notes]"
                class="form-control"
                rows="1">${detail?.kartoning?.notes ?? ''}</textarea>
        </div>
        <div class="col-md-12 mb-3">
            <label>Dokumentasi Pengemasan Sekunder</label>

            <input
                type="file"
                name="details[${index}][kartoning_documentation][]"
                class="form-control"
                accept="image/*"
                multiple>

            <div class="row mt-2">
                ${kartoningDocumentationHtml}
            </div>
        </div>
    </div>



    <h6 class="mt-5 mb-3" style="font-weight: bold;">Status Produk</h6>

    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <label>Status Release</label>
            <select name="details[${index}][release_status]" class="form-control">
                <option value="">Pilih Status</option>
                <option value="Release"
                    ${detail?.release_status === 'Release' ? 'selected' : ''}>
                    Release
                </option>
                <option value="Hold"
                    ${detail?.release_status === 'Hold' ? 'selected' : ''}>
                    Hold
                </option>
            </select>
        </div>

        <div class="col-md-6">
            <label>Tindakan Perbaikan</label>
            <input type="text" name="details[${index}][corrective_action]" class="form-control"
                value="${detail?.corrective_action ?? ''}">
        </div>

        <div class="col-md-6 mb-3">
            <label>Catatan Status Produk</label>
            <textarea
                name="details[${index}][notes]"
                class="form-control"
                rows="1">${detail?.notes ?? ''}</textarea>
        </div>

        <div class="col-md-6 d-none">
            <label class="form-label">Verifikasi Setelah Tindakan Koreksi</label>
            <select name="details[${index}][verif_after]" class="form-control">
                <option value="✓" ${detail?.verif_after === '✓' ? 'selected' : ''}>✓</option>
                <option value="x" ${detail?.verif_after === 'x' ? 'selected' : ''}>x</option>
            </select>
        </div>
    </div>
</div>
`;
    container.insertAdjacentHTML('beforeend', html);
    index++;
}

// Prefill existing details
const existingDetails = @json($details);
existingDetails.forEach(detail => addDetailRow(detail));
console.log('existingDetails:', existingDetails); // cek di browser console
console.log('jumlah detail:', existingDetails.length);

// Init select2 semua sekaligus setelah semua detail ter-render
$('.select2-product').select2({
    placeholder: '-- Pilih Produk --',
    allowClear: true,
    width: '100%'
});

// Set selected value untuk semua
existingDetails.forEach((detail, i) => {
    if (detail.product_uuid) {
        $('select[name="details['+ i +'][product_uuid]"]').val(detail.product_uuid).trigger('change');
    }
});

document.addEventListener("input", function(e){
    if(e.target.classList.contains("weight-input")){
        const group = e.target.closest(".kartoning-group");
        const weightInputs = group.querySelectorAll(".weight-input");
        const avgInput = group.querySelector(".avg-weight");

        let total = 0;
        let count = 0;
        weightInputs.forEach(input => {
            const val = parseFloat(input.value);
            if(!isNaN(val)){
                total += val;
                count++;
            }
        });
        avgInput.value = count > 0 ? (total/count).toFixed(2) : "";
    }
});

document.addEventListener('click', function(e) {

    if (e.target.matches('.add-temp')) {

        const wrapper = e.target.closest('.actual-temp-wrapper');
        const inputName = wrapper.querySelector('input').name;

        const tempRow = document.createElement('div');
        tempRow.className = 'input-group mb-2';

        tempRow.innerHTML = `
            <input type="number"
                step="0.0000001"
                name="${inputName}"
                class="form-control">

            <button type="button"
                class="btn btn-danger remove-temp">
                -
            </button>
        `;

        wrapper.insertBefore(tempRow, e.target);

        return;
    }

    if (e.target.matches('.remove-temp')) {
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

    document.getElementById('reportFreezPackagingForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const fileInputs = form.querySelectorAll('input[type="file"][name*="[documentation]"]');

        // Validasi jumlah file
        for (const input of fileInputs) {
            if (input.files && input.files.length > MAX_FILES) {
                alert(`Maksimal ${MAX_FILES} foto per detail produk.`);
                return;
            }
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengompres & menyimpan...';
        }

        try {
            for (const input of fileInputs) {
                if (!input.files || !input.files.length) continue;

                const dataTransfer = new DataTransfer();

                for (const file of input.files) {
                    let processedFile;
                    try {
                        processedFile = await compressImage(file, COMPRESS_QUALITY);
                    } catch (err) {
                        console.warn('Compress gagal, pakai file asli:', file.name, err);
                        processedFile = file; // fallback, jangan sampai macet
                    }

                    if (processedFile.size > MAX_SIZE_BYTES) {
                        alert(`File "${file.name}" masih lebih dari ${MAX_SIZE_MB}MB setelah dikompres.`);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Simpan';
                        return;
                    }

                    dataTransfer.items.add(processedFile);
                }

                input.files = dataTransfer.files;
            }

            form.submit();
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat memproses gambar.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan';
        }
    });


</script>
@endsection
