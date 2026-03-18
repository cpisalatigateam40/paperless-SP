@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail untuk Report Tanggal {{ $report->date }} Shift {{ $report->shift }}</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_packaging_verifs.store-detail', $report->uuid) }}"
                enctype="multipart/form-data">
                @csrf

                <hr>
                <h5 class="mb-3"><strong>Detail Produk</strong></h5>

                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th>Jam</th>
                            <th>Produk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="time" name="details[0][time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}"></td>
                            <td>
                                <select name="details[0][product_uuid]" class="form-control select2-product">
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }} -
                                        {{ $product->nett_weight }} g</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="card mt-3 mb-3">
                    <div class="card-header p-2">
                        <strong>Upload Foto</strong>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Upload MD BPOM, QR Code, Kode Produksi, dan Expire
                                    Date</label>
                                <input type="file" name="details[0][upload_md_multi][]"
                                    class="form-control upload-md-multi" multiple accept="image/*">

                                <!-- Tempat preview -->
                                <div class="preview-md-multi mt-2 d-flex flex-wrap" style="gap: 10px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- In Cutting hanya 1 --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>In Cutting</strong></div>
                    <div class="card-body p-2">
                        <label class="small d-block">Pilih Salah Satu</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][in_cutting]"
                                value="Manual" required>
                            <label class="form-check-label">Manual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][in_cutting]"
                                value="Mesin">
                            <label class="form-check-label">Mesin</label>
                        </div>
                    </div>
                </div>

                {{-- Proses Pengemasan hanya 1 --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Proses Pengemasan</strong></div>
                    <div class="card-body p-2">
                        <label class="small d-block">Pilih Salah Satu</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][packaging]"
                                value="Thermoformer" required>
                            <label class="form-check-label">Thermoformer</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][packaging]"
                                value="Manual">
                            <label class="form-check-label">Manual</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Sampling Kemasan</strong></div>
                    <div class="card-body p-2 row">
                        <div class="col-md-4">
                            <label class="small">Jumlah Sampling</label>
                            <input type="number" name="details[0][checklist][sampling_amount]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="small">Satuan</label>
                            <select name="details[0][checklist][unit]" class="form-control">
                                <option value="kemasan">kemasan</option>
                                <option value="pack">pack</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small">Hasil Sampling</label>
                            <select name="details[0][checklist][sampling_result]" class="form-control">
                                <option value="OK">OK</option>
                                <option value="Tidak OK">Tidak OK</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Sealing Condition 5x --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Hasil Sealing: Kondisi Seal</strong></div>
                    <div class="card-body p-2">
                        <div class="row">
                            @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                                <label class="small">Kondisi Seal {{ $i }}</label>
                                <select name="details[0][checklist][sealing_condition_{{ $i }}]" class="form-control">
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                        </div>
                        @endfor
                    </div>
                </div>
        </div>

        {{-- Sealing Vacuum 5x --}}
        <div class="card mb-3">
            <div class="card-header p-2"><strong>Hasil Sealing: Vacuum</strong></div>
            <div class="card-body p-2">
                <div class="row">
                    @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Vacuum {{ $i }}</label>
                        <select name="details[0][checklist][sealing_vacuum_{{ $i }}]" class="form-control">
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                </div>
                @endfor
            </div>
        </div>

        {{-- Sealing Vacuum 5x --}}
        <div class="card mb-3">
            <div class="card-header p-2"><strong>Hasil Sealing: Vacuum</strong></div>
            <div class="card-body p-2">
                <div class="row">
                    @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Vacuum {{ $i }}</label>
                        <select name="details[0][checklist][sealing_vacuum_{{ $i }}]" class="form-control">
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                </div>
                @endfor
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header p-2"><strong>Panjang Produk Per Pcs</strong></div>
            <div class="card-body p-2">
                <div class="row mb-2">
                    <div class="col-md-2">
                        <label class="small">Standar</label>
                        <input type="text" name="details[0][checklist][standard_long_pcs]" class="form-control">
                    </div>
                </div>
                <div class="row">
                    @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Aktual {{ $i }}</label>
                        <input type="number" step="0.01" name="details[0][checklist][actual_long_pcs_{{ $i }}]"
                            class="form-control actual-input">
                </div>
                @endfor
                <div class="col-md-2">
                    <label class="small">Rata-Rata Panjang</label>
                    <input type="number" step="0.01" name="details[0][checklist][avg_long_pcs]" class="form-control"
                        id="avg-long-pcs" readonly>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header p-2"><strong>Berat Produk Per Pcs</strong></div>
            <div class="card-body p-2">
                <div class="row mb-2">
                    <div class="col-md-2">
                        <label class="small">Standar</label>
                        <input type="text" name="details[0][checklist][standard_weight_pcs]" class="form-control">
                    </div>
                </div>
                <div class="row">
                    @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Aktual {{ $i }}</label>
                        <input type="number" step="0.01" name="details[0][checklist][actual_weight_pcs_{{ $i }}]"
                            class="form-control actual-input-wpcs">
                </div>
                @endfor
                <div class="col-md-2">
                    <label class="small">Rata-Rata Berat</label>
                    <input type="number" step="0.01" name="details[0][checklist][avg_weight_pcs]" class="form-control"
                        id="avg-weight-pcs" readonly>
                </div>
            </div>
        </div>

        {{-- Isi Per-Pack 5x --}}
        <div class="card mb-3">
            <div class="card-header p-2"><strong>Isi Per-Pack</strong></div>
            <div class="card-body p-2">
                <div class="row">
                    @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Aktual {{ $i }}</label>
                        <input type="number" name="details[0][checklist][content_per_pack_{{ $i }}]"
                            class="form-control">
                </div>
                @endfor
            </div>
        </div>

        {{-- Berat Produk --}}
        <div class="card mb-3">
            <div class="card-header p-2"><strong>Berat Produk Per Pack</strong></div>
            <div class="card-body p-2">
                <div class="row mb-2">
                    <div class="col-md-2">
                        <label class="small">Standar</label>
                        <input type="text" name="details[0][checklist][standard_weight]" class="form-control">
                    </div>
                </div>
                <div class="row">
                    @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Aktual {{ $i }}</label>
                        <input type="number" step="0.01" name="details[0][checklist][actual_weight_{{ $i }}]"
                            class="form-control actual-input-w">
                </div>
                @endfor
                <div class="col-md-2">
                    <label class="small">Rata-Rata Berat</label>
                    <input type="number" step="0.01" name="details[0][checklist][avg_weight]" class="form-control"
                        id="avg-weight" readonly>
                </div>
            </div>
        </div>

        <div class="card mb-3 mt-3">
            <div class="card-body p-2 row">
                <div class="col-md-6">
                    <label class="small">Hasil Verifikasi MD</label>
                    <select name="details[0][checklist][verif_md]" class="form-control">
                        <option value="OK">OK</option>
                        <option value="Tidak OK">Tidak OK</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="small">Keterangan</label>
                    <input type="text" name="details[0][checklist][notes]" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<button type="submit" class="btn btn-success">Simpan Detail</button>
</form>
</div>
</div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== Rata-rata Panjang Pcs =====
    const actualLongInputs = document.querySelectorAll('.actual-input');
    const avgLongInput = document.getElementById('avg-long-pcs');

    function calcAvgLong() {
        let sum = 0,
            count = 0;
        actualLongInputs.forEach(i => {
            const v = parseFloat(i.value);
            if (!isNaN(v)) {
                sum += v;
                count++;
            }
        });
        avgLongInput.value = count > 0 ? (sum / count).toFixed(2) : '';
    }
    actualLongInputs.forEach(i => i.addEventListener('input', calcAvgLong));

    // ===== Rata-rata Berat Pcs =====
    const actualWpcsInputs = document.querySelectorAll('.actual-input-wpcs');
    const avgWpcsInput = document.getElementById('avg-weight-pcs');

    function calcAvgWpcs() {
        let sum = 0,
            count = 0;
        actualWpcsInputs.forEach(i => {
            const v = parseFloat(i.value);
            if (!isNaN(v)) {
                sum += v;
                count++;
            }
        });
        avgWpcsInput.value = count > 0 ? (sum / count).toFixed(2) : '';
    }
    actualWpcsInputs.forEach(i => i.addEventListener('input', calcAvgWpcs));

    // ===== Rata-rata Berat Pack =====
    const actualWInputs = document.querySelectorAll('.actual-input-w');
    const avgWInput = document.getElementById('avg-weight');

    function calcAvgW() {
        let sum = 0,
            count = 0;
        actualWInputs.forEach(i => {
            const v = parseFloat(i.value);
            if (!isNaN(v)) {
                sum += v;
                count++;
            }
        });
        avgWInput.value = count > 0 ? (sum / count).toFixed(2) : '';
    }
    actualWInputs.forEach(i => i.addEventListener('input', calcAvgW));

    // ===== Compress & Validasi Upload File =====
    const MAX_SIZE_MB = 2;
    const MAX_FILES = 10;
    const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024;
    const COMPRESS_QUALITY = 0.7;

    function compressImage(file, quality) {
        return new Promise((resolve) => {
            if (!file.type.startsWith('image/')) {
                resolve(file);
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const MAX_DIMENSION = 1920;
                    if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
                        if (width > height) {
                            height = Math.round((height * MAX_DIMENSION) / width);
                            width = MAX_DIMENSION;
                        } else {
                            width = Math.round((width * MAX_DIMENSION) / height);
                            height = MAX_DIMENSION;
                        }
                    }
                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                    canvas.toBlob((blob) => {
                        resolve(new File([blob], file.name.replace(/\.[^.]+$/,
                        '.jpg'), {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        }));
                    }, 'image/jpeg', quality);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    async function handleFileChange(input) {
        const files = Array.from(input.files);
        const col = input.closest('.col-md-4');
        const errorBox = col.querySelector('.invalid-feedback-custom');
        const preview = col.querySelector('.preview-md-multi');

        preview.innerHTML = '';

        // Jika belum ada errorBox (halaman detail tidak punya), buat dinamis
        let errBox = errorBox;
        if (!errBox) {
            errBox = document.createElement('div');
            errBox.className = 'invalid-feedback-custom text-danger mt-1';
            errBox.style.cssText = 'font-size: 0.85rem; display: none;';
            input.parentNode.insertBefore(errBox, preview);
        }

        errBox.style.display = 'none';
        errBox.innerHTML = '';

        if (files.length > MAX_FILES) {
            errBox.classList.remove('text-info');
            errBox.classList.add('text-danger');
            errBox.innerText = `Maksimal ${MAX_FILES} file yang diizinkan.`;
            errBox.style.display = 'block';
            input.value = '';
            return;
        }

        // Loading
        errBox.classList.remove('text-danger');
        errBox.classList.add('text-info');
        errBox.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengompresi gambar...';
        errBox.style.display = 'block';

        const compressedFiles = await Promise.all(
            files.map(file => compressImage(file, COMPRESS_QUALITY))
        );

        errBox.style.display = 'none';
        errBox.classList.remove('text-info');
        errBox.classList.add('text-danger');

        // Validasi setelah compress
        let errors = [];
        compressedFiles.forEach(function(file) {
            if (file.size > MAX_SIZE_BYTES) {
                const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                errors.push(
                    `"${file.name}" (${fileSizeMB} MB) masih melebihi ${MAX_SIZE_MB} MB setelah dikompresi.`
                    );
            }
        });

        if (errors.length > 0) {
            errBox.innerHTML = errors.join('<br>');
            errBox.style.display = 'block';
            input.value = '';
            return;
        }

        // Replace files input dengan hasil compress
        const dataTransfer = new DataTransfer();
        compressedFiles.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;

        // Preview
        compressedFiles.forEach(function(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'display: inline-block; text-align: center;';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText =
                    'width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6; display: block;';

                const sizeLabel = document.createElement('small');
                sizeLabel.style.cssText = 'font-size: 10px; color: #6c757d;';
                sizeLabel.innerText = (file.size / 1024).toFixed(0) + ' KB';

                wrapper.appendChild(img);
                wrapper.appendChild(sizeLabel);
                preview.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }

    // Event delegation — berlaku untuk input yang ada sekarang maupun dinamis
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('upload-md-multi')) {
            handleFileChange(e.target);
        }
    });

    // Blokir submit
    const form = document.getElementById('form-packaging') || document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            let errorFiles = [];

            document.querySelectorAll('input[type="file"]').forEach(function(input) {
                Array.from(input.files).forEach(function(file) {
                    if (file.size > MAX_SIZE_BYTES) {
                        hasError = true;
                        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                        errorFiles.push(`${file.name} (${fileSizeMB} MB)`);
                    }
                });
            });

            if (hasError) {
                e.preventDefault();
                e.stopPropagation();
                alert('❌ File berikut masih melebihi batas 2 MB:\n\n' + errorFiles.join('\n') +
                    '\n\nSilakan hapus dan pilih ulang file.');
            }
        });
    }

});
</script>
@endsection