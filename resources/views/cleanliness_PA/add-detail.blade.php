@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-body">
                <form action="{{ route('process-area-cleanliness.detail.store', $report->id) }}" method="POST">
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
                            $items = ['Kondisi dan penempatan barang', 'Pelabelan', 'Kebersihan Ruangan', 'Suhu ruang (℃)'];
                        @endphp

                        @foreach($items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><input type="hidden" name="details[__index__][items][{{ $i }}][item]" value="{{ $item }}">{{ $item }}</td>

                            <td>
                                @if($item === 'Suhu ruang (℃)')
                                    <div class="d-flex gap-1" style="gap: 1rem;">
                                        <input type="number" step="0.1" name="details[__index__][items][{{ $i }}][temperature]" placeholder="℃" class="form-control">
                                    </div>  
                                @else
                                    <select name="details[__index__][items][{{ $i }}][condition]" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="Bersih">1. Bersih</option>
                                        <option value="Kotor">2. Kotor</option>
                                    </select>
                                @endif
                            </td>

                            <td><input type="text" name="details[__index__][items][{{ $i }}][notes]" class="form-control"></td>
                            <td><input type="text" name="details[__index__][items][{{ $i }}][corrective_action]" class="form-control"></td>
                            <td>
                                <select name="details[__index__][items][{{ $i }}][verification]" class="form-control">
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