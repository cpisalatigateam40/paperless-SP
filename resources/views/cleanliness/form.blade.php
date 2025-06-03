@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('cleanliness.store') }}" method="POST">
                @csrf

                <!-- Header -->
                <div class="mb-4">
                    <label>Tanggal:</label>
                    <input type="date" name="date" class="form-control">

                    <label>Shift:</label>
                    <input type="text" name="shift" class="form-control">

                    <label>Area (Room Name):</label>
                    <select name="room_name" class="form-control" required>
                        <option value="">-- Pilih Area --</option>
                        <option value="Seasoning">Seasoning</option>
                        <option value="Chillroom">Chillroom</option>
                    </select>

                    {{-- <label>Dibuat oleh:</label>
                    <input type="text" name="created_by" class="form-control">

                    <label>Diketahui oleh:</label>
                    <input type="text" name="known_by" class="form-control">

                    <label>Disetujui oleh:</label>
                    <input type="text" name="approved_by" class="form-control"> --}}
                </div>

                <!-- Detail Jam Inspeksi dan Items -->
                <div id="inspection-details">
                    <h5>Detail Inspeksi</h5>
                    <div class="inspection-block">
                        <label>Jam Inspeksi:</label>
                        <input type="time" name="details[0][inspection_hour]" class="form-control">

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
                                @foreach(['Kondisi dan penempatan barang', 'Pelabelan', 'Kebersihan Ruangan', 'Suhu ruang (℃) / RH (%)'] as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><input type="hidden" name="details[0][items][{{ $i }}][item]" value="{{ $item }}">{{ $item }}</td>
                                    <td>
                                        <select name="details[0][items][{{ $i }}][condition]" class="form-control">
                                            <option value="">-- Pilih --</option>
                                            <option value="1">1. Tertata rapi</option>
                                            <option value="2">2. Sesuai tagging dan jenis allergen</option>
                                            <option value="3">3. Bersih dan bebas kontaminan</option>
                                            <option value="4">4. Tidak tertata rapi</option>
                                            <option value="5">5. Penempatan tidak sesuai</option>
                                            <option value="6">6. Tidak bersih / ada kontaminan</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="details[0][items][{{ $i }}][notes]" class="form-control"></td>
                                    <td><input type="text" name="details[0][items][{{ $i }}][corrective_action]" class="form-control"></td>
                                    <td>
                                        <select name="details[0][items][{{ $i }}][verification]" class="form-control">
                                            <option value="0">Belum</option>
                                            <option value="1">Sudah</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
