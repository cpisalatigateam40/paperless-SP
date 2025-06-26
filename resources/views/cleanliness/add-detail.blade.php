@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Detail Inspeksi</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('cleanliness.detail.store', $report->id) }}" method="POST">
                @csrf
                <div class="border rounded p-3 mb-3 position-relative">
                    <label>Jam Inspeksi:</label>
                    <input type="time" name="details[0][inspection_hour]" class="form-control mb-3 col-md-5"
                        value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Kondisi</th>
                                <th>Catatan</th>
                                <th>Tindakan Koreksi</th>
                                <th>Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Item 1: Penempatan Barang --}}
                            <tr>
                                <td>1</td>
                                <td>
                                    <input type="hidden" name="details[0][items][0][item]"
                                        value="Kondisi dan penempatan barang" required>
                                    Kondisi dan penempatan barang
                                </td>
                                <td>
                                    <select name="details[0][items][0][condition]" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="Tertata rapi">Tertata rapi</option>
                                        <option value="Tidak tertata rapi">Tidak tertata rapi</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="details[0][items][0][notes]" class="form-control note-select"
                                        data-item="0" required>
                                        <option value="">-- Pilih Catatan --</option>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Penataan bahan tidak rapi">Penataan bahan tidak rapi</option>
                                        <option value="Penempatan bahan tidak sesuai dengan labelnya">Penempatan bahan
                                            tidak sesuai dengan labelnya</option>
                                        <option value="Tidak ada label/tagging di tempat penyimpanan">Tidak ada
                                            label/tagging di tempat penyimpanan</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="details[0][items][0][corrective_action]" id="corrective-0"
                                        class="form-control">
                                </td>
                                <td>
                                    <select name="details[0][items][0][verification]" class="form-control" required>
                                        <option value="0">Tidak OK</option>
                                        <option value="1">OK</option>
                                    </select>
                                </td>
                            </tr>

                            {{-- Item 2: Pelabelan --}}
                            <tr>
                                <td>2</td>
                                <td>
                                    <input type="hidden" name="details[0][items][1][item]" value="Pelabelan" required>
                                    Pelabelan
                                </td>
                                <td>
                                    <select name="details[0][items][1][condition]" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="Sesuai tagging dan jenis alergen">Sesuai tagging dan jenis
                                            alergen</option>
                                        <option value="Penempatan tidak sesuai">Penempatan tidak sesuai</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="details[0][items][1][notes]" class="form-control note-select"
                                        data-item="1" required>
                                        <option value="">-- Pilih Catatan --</option>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Penataan bahan tidak rapi">Penataan bahan tidak rapi</option>
                                        <option value="Penempatan bahan tidak sesuai dengan labelnya">Penempatan bahan
                                            tidak sesuai dengan labelnya</option>
                                        <option value="Tidak ada label/tagging di tempat penyimpanan">Tidak ada
                                            label/tagging di tempat penyimpanan</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="details[0][items][1][corrective_action]" id="corrective-1"
                                        class="form-control">
                                </td>
                                <td>
                                    <select name="details[0][items][1][verification]" class="form-control" required>
                                        <option value="0">Tidak OK</option>
                                        <option value="1">OK</option>
                                    </select>
                                </td>
                            </tr>

                            {{-- Item 3: Kebersihan Ruangan --}}
                            <tr>
                                <td>3</td>
                                <td>
                                    <input type="hidden" name="details[0][items][2][item]" value="Kebersihan Ruangan"
                                        required>
                                    Kebersihan Ruangan
                                </td>
                                <td>
                                    <select name="details[0][items][2][condition]" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="Bersih dan bebas kontaminan">Bersih dan bebas kontaminan</option>
                                        <option value="Tidak bersih / ada kontaminan">Tidak bersih / ada kontaminan
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <select name="details[0][items][2][notes]" class="form-control note-select"
                                        data-item="2" required>
                                        <option value="">-- Pilih Catatan --</option>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Rak penyimpanan bahan kotor">Rak penyimpanan bahan kotor</option>
                                        <option value="Langit-langit kotor">Langit-langit kotor</option>
                                        <option value="Pintu kotor">Pintu kotor</option>
                                        <option value="Dinding kotor">Dinding kotor</option>
                                        <option value="Curving kotor">Curving kotor</option>
                                        <option value="Curtain kotor">Curtain kotor</option>
                                        <option value="Lantai kotor/basah">Lantai kotor/basah</option>
                                        <option value="Pallet kotor">Pallet kotor</option>
                                        <option value="Lampu + cover kotor">Lampu + cover kotor</option>
                                        <option value="Exhaust fan kotor">Exhaust fan kotor</option>
                                        <option value="Evaporator kotor">Evaporator kotor</option>
                                        <option value="Temuan pest di area produksi">Temuan pest di area produksi
                                        </option>
                                        <option value="Temuan pest di dalam bahan">Temuan pest di dalam bahan</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="details[0][items][2][corrective_action]" id="corrective-2"
                                        class="form-control">
                                </td>
                                <td>
                                    <select name="details[0][items][2][verification]" class="form-control" required>
                                        <option value="0">Tidak OK</option>
                                        <option value="1">OK</option>
                                    </select>
                                </td>
                            </tr>

                            {{-- Item 4: Suhu / RH --}}
                            <tr>
                                <td>4</td>
                                <td>
                                    <input type="hidden" name="details[0][items][3][item]"
                                        value="Suhu ruang (℃) / RH (%)" required>
                                    Suhu ruang (℃) / RH (%)
                                </td>
                                <td>
                                    <div class="d-flex gap-1" style="gap: 1rem;">
                                        <input type="number" step="0.1" name="details[0][items][3][temperature]"
                                            placeholder="℃" class="form-control" required>
                                        <input type="number" step="0.1" name="details[0][items][3][humidity]"
                                            placeholder="RH%" class="form-control" required>
                                    </div>
                                </td>
                                <td><input type="text" name="details[0][items][3][notes]" class="form-control" required>
                                </td>
                                <td><input type="text" name="details[0][items][3][corrective_action]"
                                        class="form-control" required></td>
                                <td>
                                    <select name="details[0][items][3][verification]" class="form-control" required>
                                        <option value="0">Tidak OK</option>
                                        <option value="1">OK</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
const koreksiMap = {
    'Sesuai': '-',
    'Penataan bahan tidak rapi': 'Penataan bahan dengan rapi',
    'Penempatan bahan tidak sesuai dengan labelnya': 'Penataan bahan sesuai dengan labelnya',
    'Tidak ada label/tagging di tempat penyimpanan': 'Labelling/tagging sesuai tempat penyimpanan',
    'Rak penyimpanan bahan kotor': 'Cleaning area/peralatan yang kotor',
    'Langit-langit kotor': 'Cleaning area/peralatan yang kotor',
    'Pintu kotor': 'Cleaning area/peralatan yang kotor',
    'Dinding kotor': 'Cleaning area/peralatan yang kotor',
    'Curving kotor': 'Cleaning area/peralatan yang kotor',
    'Curtain kotor': 'Cleaning area/peralatan yang kotor',
    'Lantai kotor/basah': 'Cleaning area/peralatan yang kotor',
    'Pallet kotor': 'Cleaning area/peralatan yang kotor',
    'Lampu + cover kotor': 'Cleaning area/peralatan yang kotor',
    'Exhaust fan kotor': 'Cleaning area/peralatan yang kotor',
    'Evaporator kotor': 'Cleaning area/peralatan yang kotor',
    'Temuan pest di area produksi': 'Inspeksi pest',
    'Temuan pest di dalam bahan': 'Inspeksi pest'
};

document.addEventListener('change', function(e) {
    // Logika jika catatan berubah
    if (e.target.classList.contains('note-select')) {
        const itemIndex = e.target.dataset.item;
        const corrective = document.getElementById('corrective-' + itemIndex);
        corrective.value = koreksiMap[e.target.value] || '';
    }

    // Logika jika kondisi dipilih
    if (e.target.name.includes('[condition]')) {
        const name = e.target.name; // misal: details[0][items][0][condition]
        const match = name.match(/details\[\d+]\[items]\[(\d+)]\[condition]/);
        if (match) {
            const itemIndex = match[1];
            const value = e.target.value;
            const noteSelect = document.querySelector(`[name="details[0][items][${itemIndex}][notes]"]`);
            const corrective = document.getElementById(`corrective-${itemIndex}`);
            const verification = document.querySelector(
                `[name="details[0][items][${itemIndex}][verification]"]`);

            if (value === 'Tertata rapi' || value === 'Sesuai tagging dan jenis alergen' || value ===
                'Bersih dan bebas kontaminan') {
                if (noteSelect) noteSelect.value = 'Sesuai';
                if (corrective) corrective.value = koreksiMap['Sesuai'];
                if (verification) verification.value = '1';
            }
        }
    }
});
</script>

@endsection