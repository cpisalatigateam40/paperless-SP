@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-body">
                <form action="{{ route('cleanliness.detail.store', $report->id) }}" method="POST">
                @csrf
                <div class="border rounded p-3 mb-3 position-relative">
                
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
                            $items = ['Kondisi dan penempatan barang', 'Pelabelan', 'Kebersihan Ruangan', 'Suhu ruang (℃) / RH (%)'];
                        @endphp

                        @foreach($items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><input type="hidden" name="details[__index__][items][{{ $i }}][item]" value="{{ $item }}" required>{{ $item }}</td>

                            <td>
                                @if($item === 'Suhu ruang (℃) / RH (%)')
                                    <div class="d-flex gap-1" style="gap: 1rem;">
                                        <input type="number" step="0.1" name="details[__index__][items][{{ $i }}][temperature]" placeholder="℃" class="form-control" required>
                                        <input type="number" step="0.1" name="details[__index__][items][{{ $i }}][humidity]" placeholder="RH%" class="form-control" required>
                                    </div>  
                                @else
                                    <select name="details[__index__][items][{{ $i }}][condition]" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="1">1. Tertata rapi</option>
                                        <option value="2">2. Sesuai tagging dan jenis allergen</option>
                                        <option value="3">3. Bersih dan bebas kontaminan</option>
                                        <option value="4">4. Tidak tertata rapi</option>
                                        <option value="5">5. Penempatan tidak sesuai</option>
                                        <option value="6">6. Tidak bersih / ada kontaminan</option>
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

                <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
@endsection