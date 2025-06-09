@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Tambah Detail Sanitasi untuk Laporan {{ \Carbon\Carbon::parse($report->date)->format('d M Y') }} - Shift {{ $report->shift }}</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('gmp-employee.sanitation-detail.store', $report->id) }}" method="POST">
                @csrf

                {{-- Jam dan Verifikasi --}}
                {{-- <div class="mb-3">
                    <label for="hour_1" class="form-label">Jam 1</label>
                    <input type="time" name="sanitation[hour_1]" id="hour_1" class="form-control" value="{{ old('sanitation.hour_1') }}" required>
                    @error('sanitation.hour_1') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="hour_2" class="form-label">Jam 2</label>
                    <input type="time" name="sanitation[hour_2]" id="hour_2" class="form-control" value="{{ old('sanitation.hour_2') }}" required>
                    @error('sanitation.hour_2') <small class="text-danger">{{ $message }}</small> @enderror
                </div> --}}

                <div class="mb-3">
                    <label for="verification" class="form-label">Verifikasi</label>
                    <select name="sanitation[verification]" id="verification" class="form-control" required>
                        <option value="">Pilih</option>
                        <option value="1" {{ old('sanitation.verification') == '1' ? 'selected' : '' }}>✔</option>
                        <option value="0" {{ old('sanitation.verification') == '0' ? 'selected' : '' }}>✘</option>
                    </select>
                    @error('sanitation.verification') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <hr>

                {{-- Area Sanitasi --}}
                <h5>Area Sanitasi</h5>
                <div class="border p-3 mb-3" id="sanitation-areas-container">
                    <div class="sanitation-area mb-4 p-3 border rounded">
                        <div class="mb-3">
                            <label class="form-label">Nama Area</label>
                            <input type="text" name="sanitation_area[0][area_name]" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Standar Klorin</label>
                            <input type="number" step="0.01" name="sanitation_area[0][chlorine_std]" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Hasil Pengecekan Jam 1</strong></p>
                                <div class="mb-3">
                                    <label class="form-label">Kadar Klorin</label>
                                    <input type="number" step="0.01" name="sanitation_area[0][result][1][chlorine_level]" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Suhu</label>
                                    <input type="number" step="0.1" name="sanitation_area[0][result][1][temperature]" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <p><strong>Hasil Pengecekan Jam 2</strong></p>
                                <div class="mb-3">
                                    <label class="form-label">Kadar Klorin</label>
                                    <input type="number" step="0.01" name="sanitation_area[0][result][2][chlorine_level]" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Suhu</label>
                                    <input type="number" step="0.1" name="sanitation_area[0][result][2][temperature]" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="sanitation_area[0][notes]" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tindakan Korektif</label>
                            <input type="text" name="sanitation_area[0][corrective_action]" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Button untuk tambah area baru (jika perlu) --}}
                <button type="button" id="add-area-btn" class="btn btn-secondary mb-3">+ Tambah Area Sanitasi</button>

                <button type="submit" class="btn btn-primary">Simpan Detail Sanitasi</button>
            </form>
        </div>
    </div>
</div>

{{-- Script untuk duplicate input area jika perlu --}}
<script>
    let areaIndex = 1;
    document.getElementById('add-area-btn').addEventListener('click', function() {
        const container = document.getElementById('sanitation-areas-container');
        const template = container.querySelector('.sanitation-area').cloneNode(true);

        // Reset semua input di template
        template.querySelectorAll('input').forEach(input => {
            input.value = '';
        });

        // Update nama input agar index berubah
        template.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                const newName = name.replace(/\[\d+\]/, `[${areaIndex}]`);
                input.setAttribute('name', newName);
            }
        });

        container.appendChild(template);
        areaIndex++;
    });
</script>
@endsection
