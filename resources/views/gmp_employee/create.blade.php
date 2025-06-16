@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Buat Laporan GMP Karyawan & Kontrol Sanitasi</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('gmp-employee.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>

                <div class="mb-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" required>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="gmpTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
                            GMP Karyawan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="sanitasi-tab" data-bs-toggle="tab" data-bs-target="#sanitasi" type="button" role="tab">
                            Sanitasi Area
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="gmpTabsContent">
                    {{-- Tab Detail Inspeksi --}}
                    <div class="tab-pane fade show active" id="detail" role="tabpanel">
                        <div id="detail-container">
                            <div class="detail-group border rounded p-3 mb-3">
                                <h6>Detail Inspeksi</h6>
                                <div class="mb-2">
                                    <label>Jam Inspeksi</label>
                                    <input type="time" name="details[0][inspection_hour]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="mb-2">
                                    <label>Nama Bagian</label>
                                    <select name="details[0][section_name]" class="form-control">
                                        <option value="">-- Pilih Bagian --</option>
                                        <option value="MP">MP</option>
                                        <option value="Cooking">Cooking</option>
                                        <option value="Packing">Packing</option>
                                        <option value="Cartoning">Cartoning</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label>Nama Karyawan</label>
                                    <input type="text" name="details[0][employee_name]" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Catatan</label>
                                    <input type="text" name="details[0][notes]" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Tindakan Korektif</label>
                                    <input type="text" name="details[0][corrective_action]" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Verifikasi</label>
                                    <select name="details[0][verification]" class="form-control">
                                        <option value="">Pilih</option>
                                        <option value="1">OK</option>
                                        <option value="0">Tidak OK</option>
                                    </select>
                                </div>
                            </div>
                        </div>                      
                    </div>

                    {{-- Tab Sanitasi Area --}}
                    <div class="tab-pane fade" id="sanitasi" role="tabpanel">
                        <div class="border rounded p-3">
                            <h6>Data Sanitasi</h6>
                            <div class="mb-2">
                                <label>Jam 1</label>
                                <input type="time" name="sanitation[hour_1]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}" {{ $isEdit ? 'disabled' : '' }}>
                            </div>
                            <div class="mb-2">
                                <label>Jam 2</label>
                                <input type="time" name="sanitation[hour_2]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}" {{ !$isEdit ? 'disabled' : '' }}>
                            </div>
                            <div class="mb-2">
                                <label>Verifikasi</label>
                                <select name="sanitation[verification]" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="1">✔</option>
                                    <option value="0">✘</option>
                                </select>
                            </div>

                            <hr>
                            @php
                                $areaList = [
                                    ['name' => 'Foot Basin', 'chlorine_std' => 200],
                                    ['name' => 'Hand Basin', 'chlorine_std' => 50],
                                    ['name' => 'Air Cuci Tangan', 'chlorine_std' => null],
                                    ['name' => 'Air Cleaning', 'chlorine_std' => null],
                                ];
                            @endphp
                            <h6>Area Sanitasi</h6>

                            @foreach ($areaList as $index => $area)
                                <div class="border p-2 mb-3">
                                    <div class="mb-2">
                                        <label>Nama Area</label>
                                        <input type="text" name="sanitation_area[{{ $index }}][area_name]" class="form-control"
                                            value="{{ $area['name'] }}" readonly>
                                    </div>
                                    <div class="mb-2">
                                        <label>Standar Klorin</label>
                                        <input type="number" name="sanitation_area[{{ $index }}][chlorine_std]" class="form-control"
                                            value="{{ $area['chlorine_std'] }}">
                                    </div>

                                    <div class="d-flex" style="gap: 1rem">
                                        <div class="col-md-6">
                                            <p style="margin-top: 2rem; font-weight: bold;">Hasil Pengecekan Jam 1</p>
                                            <div class="mb-2">
                                                <label>Kadar Klorin</label>
                                                <input type="number" name="sanitation_area[{{ $index }}][result][1][chlorine_level]" class="form-control" {{ $isEdit ? 'disabled' : '' }}>
                                            </div>
                                            <div class="mb-2">
                                                <label>Suhu</label>
                                                <input type="number" name="sanitation_area[{{ $index }}][result][1][temperature]" class="form-control" {{ $isEdit ? 'disabled' : '' }}>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <p style="margin-top: 2rem; font-weight: bold;">Hasil Pengecekan Jam 2</p>
                                            <div class="mb-2">
                                                <label>Kadar Klorin</label>
                                                <input type="number" name="sanitation_area[{{ $index }}][result][2][chlorine_level]" class="form-control" {{ !$isEdit ? 'disabled' : '' }}>
                                            </div>
                                            <div class="mb-2">
                                                <label>Suhu</label>
                                                <input type="number" name="sanitation_area[{{ $index }}][result][2][temperature]" class="form-control" {{ !$isEdit ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label>Catatan</label>
                                        <input type="text" name="sanitation_area[{{ $index }}][notes]" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label>Tindakan Korektif</label>
                                        <input type="text" name="sanitation_area[{{ $index }}][corrective_action]" class="form-control">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                {{-- <button type="button" id="add-detail" class="btn btn-secondary mr-1">Tambah Detail</button> --}}

                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>

<script>
    let detailIndex = 1;
    document.getElementById('add-detail').addEventListener('click', function () {
        const container = document.getElementById('detail-inspections');
        const clone = container.firstElementChild.cloneNode(true);
        clone.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name');
            const newName = name.replace(/\d+/, detailIndex);
            input.setAttribute('name', newName);
            input.value = '';
        });
        container.appendChild(clone);
        detailIndex++;
    });
</script>

@endsection

