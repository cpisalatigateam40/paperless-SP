@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Edit Laporan Verifikasi Pembekuan IQF & Pengemasan Karton Box</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('report_freez_packagings.update', $report->uuid) }}" method="POST">
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
            ${p.product_name} - ${p.nett_weight} g
        </option>`
    ).join('');
}

function addDetailRow(detail = null) {
    const container = document.getElementById('detail-container');
    const now = new Date();
    const currentTime = now.toLocaleTimeString('it-IT', {hour:'2-digit', minute:'2-digit'});

    const currentIndex = index;
    
    const html = `
<div class="card mb-4 p-3 border">
    <div class="d-flex justify-content-between align-items-center">
        <h6 style="font-weight:bold; margin-bottom:1rem;">Detail #${index+1}</h6>
        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.card').remove()">Hapus</button>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <label>Produk</label>
            <select name="details[${index}][product_uuid]" class="form-control select2-product" onchange="updateBestBefore(this, ${index})">
                <option value="">- Pilih Produk -</option>
                ${renderProductOptions(detail?.product_uuid ?? '')}
            </select>
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

    <div class="row mt-2">
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

    <h6 class="mt-5 mb-3" style="font-weight:bold;">Pembekuan</h6>
    <div class="row mb-3">
        <div class="col-md-6">
            <label>Mesin IQF</label>
            <select name="details[${index}][freezing][iqf_machine]" class="form-control">
                <option value="">-- Pilih Mesin --</option>
                <option value="IQF 1" ${detail?.freezing?.iqf_machine === 'IQF 1' ? 'selected' : ''}>IQF 1</option>
                <option value="IQF 2" ${detail?.freezing?.iqf_machine === 'IQF 2' ? 'selected' : ''}>IQF 2</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <label>Suhu Akhir Produk (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][end_product_temp]" class="form-control"
                value="${detail?.freezing?.end_product_temp ?? ''}">
        </div>
        <div class="col-md-6">
            <label>Standard Suhu (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][standard_temp]" class="form-control"
                value="${detail?.freezing?.standard_temp ?? '-18'}">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Suhu Room IQF (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][iqf_room_temp]" class="form-control"
                value="${detail?.freezing?.iqf_room_temp ?? ''}">
        </div>
        <div class="col-md-6">
            <label>Suhu Suction IQF (°C)</label>
            <input type="number" step="0.0000001" name="details[${index}][freezing][iqf_suction_temp]" class="form-control"
                value="${detail?.freezing?.iqf_suction_temp ?? ''}">
        </div>
    </div>

    <div class="row mt-3">
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

    <h6 class="mt-5 mb-3" style="font-weight:bold;">Kartoning</h6>
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">Verifikasi Kondisi Karton</label>
            <select name="details[${index}][kartoning][carton_condition]" class="form-control">
                <option value="✓" ${detail?.kartoning?.carton_condition === '✓' ? 'selected' : ''}>✓</option>
                <option value="x" ${detail?.kartoning?.carton_condition === 'x' ? 'selected' : ''}>x</option>
            </select>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Isi Bag</label>
            <input type="number" name="details[${index}][kartoning][content_bag]" class="form-control"
                value="${detail?.kartoning?.content_bag ?? ''}">
        </div>
        <div class="col-md-6">
            <label>Isi Binded</label>
            <input type="number" name="details[${index}][kartoning][content_binded]" class="form-control"
                value="${detail?.kartoning?.content_binded ?? ''}">
        </div>
        <div class="col-md-6 mt-3">
            <label>Isi Inner RTG</label>
            <input type="number" name="details[${index}][kartoning][content_rtg]" class="form-control"
                value="${detail?.kartoning?.content_rtg ?? ''}">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Berat Standar (kg)</label>
            <input type="text" name="details[${index}][kartoning][carton_weight_standard]" class="form-control"
                value="${detail?.kartoning?.carton_weight_standard ?? ''}" placeholder="contoh: 12-13">
        </div>
    </div>

    <div class="row kartoning-group mt-3" data-index="${index}">
        ${[1,2,3,4,5].map(i => `
        <div class="col-md-2">
            <label>Berat Karton ${i}</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][weight_${i}]" class="form-control weight-input"
                value="${detail?.kartoning?.['weight_'+i] ?? ''}">
        </div>`).join('')}
        <div class="col-md-2">
            <label>Rata-Rata Berat</label>
            <input type="number" step="0.01" name="details[${index}][kartoning][avg_weight]" class="form-control avg-weight"
                value="${detail?.kartoning?.avg_weight ?? ''}">
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <label>Tindakan Koreksi</label>
            <input type="text" name="details[${index}][corrective_action]" class="form-control"
                value="${detail?.corrective_action ?? ''}">
        </div>

        <div class="col-md-6">
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


</script>
@endsection
