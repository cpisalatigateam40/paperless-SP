@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Form Pemeriksaan Kondisi Kebersihan Area Proses</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('process-area-cleanliness.store') }}" method="POST">
                @csrf

                <!-- Header -->
                <div class="mb-4">
                    <div class="d-flex" style="gap: 1rem;">
                        <div class="col-md-5 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Tanggal:</label>
                            <input type="date" name="date" class="form-control" value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        </div>

                        <div class="col-md-5 mb-3" style="margin-inline: unset; padding-inline: unset;">
                            <label>Shift:</label>
                            <input type="text" name="shift" class="form-control" required>
                        </div>
                    </div>
                    

                    

                    <label>Area:</label>
                    <select name="section_name" class="form-control col-md-5 mb-5" required>
                        <option value="">-- Pilih Area --</option>
                        <option value="MP">MP</option>
                        <option value="Cooking">Cooking</option>
                        <option value="Packing">Packing</option>
                        <option value="Cartoning">Cartoning</option>
                    </select>
                </div>

                <!-- Detail Jam Inspeksi dan Items -->
                <div id="inspection-details">
                    <h5 class="mb-3">Detail Inspeksi</h5>
                </div>

                <button type="button" id="add-inspection" class="btn btn-secondary mr-2">+ Tambah Detail Inspeksi</button>

                <button type="submit" class="btn btn-primary">Simpan</button>

                <!-- Template untuk form detail inspeksi -->
                <template id="inspection-template">
                    <div class="inspection-block border rounded p-3 mb-3 position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute remove-inspection" style="z-index: 1; right: 0; top: 0; margin-top: .5rem; margin-right: .5rem;" aria-label="Close">x</button>
                        
                        <label>Jam Inspeksi:</label>
                        <input type="time" name="details[__index__][inspection_hour]" class="form-control mb-3 col-md-5">

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
                                @php
                                    $items = ['Kondisi dan penempatan barang', 'Pelabelan', 'Kebersihan Ruangan', 'Suhu ruang (℃)'];
                                @endphp

                                @foreach($items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><input type="hidden" name="details[__index__][items][{{ $i }}][item]" value="{{ $item }}" required>{{ $item }}</td>

                                    <td>
                                        @if($item === 'Suhu ruang (℃)')
                                            <div class="d-flex gap-1" style="gap: 1rem;">
                                                <input type="number" step="0.1" name="details[__index__][items][{{ $i }}][temperature]" placeholder="℃" class="form-control" required>
                                            </div>
                                        @else
                                            <select name="details[__index__][items][{{ $i }}][condition]" class="form-control" required>
                                                <option value="">-- Pilih --</option>
                                                <option value="Bersih">1. Bersih</option>
                                                <option value="Kotor">2. Kotor</option>
                                            </select>
                                        @endif
                                    </td>

                                    <td><input type="text" name="details[__index__][items][{{ $i }}][notes]" class="form-control" required></td>
                                    <td><input type="text" name="details[__index__][items][{{ $i }}][corrective_action]" class="form-control" required></td>
                                    <td>
                                        <select name="details[__index__][items][{{ $i }}][verification]" class="form-control" required>
                                            <option value="0">Tidak OK</option>
                                            <option value="1">OK</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </template>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let inspectionIndex = 0;

    document.getElementById('add-inspection').addEventListener('click', function () {
        const template = document.getElementById('inspection-template').innerHTML;
        const rendered = template.replace(/__index__/g, inspectionIndex);
        document.getElementById('inspection-details').insertAdjacentHTML('beforeend', rendered);
        inspectionIndex++;
    });

    // Trigger sekali di awal agar form pertama muncul
    document.getElementById('add-inspection').click();

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-inspection')) {
            e.target.closest('.inspection-block').remove();
        }
    });
</script>
@endsection