@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan Verifikasi Kebersihan Area Proses</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('process-area-cleanliness.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <div class="d-flex" style="gap: 1rem;">
                        <div class="col-md-5 mb-3">
                            <label>Tanggal:</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ $report->date }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label>Shift:</label>
                            <input type="text" name="shift" class="form-control"
                                value="{{ $report->shift }}" required>
                        </div>
                    </div>

                    <label>Area:</label>
                    <select name="section_name" class="form-control col-md-5 mb-5" required>
                        <option value="">-- Pilih Area --</option>
                        <option value="MP" {{ $report->section_name == 'MP' ? 'selected' : '' }}>MP</option>
                        <option value="Cooking" {{ $report->section_name == 'Cooking' ? 'selected' : '' }}>Cooking</option>
                        <option value="Packing" {{ $report->section_name == 'Packing' ? 'selected' : '' }}>Packing</option>
                        <option value="Cartoning" {{ $report->section_name == 'Cartoning' ? 'selected' : '' }}>Cartoning</option>
                    </select>
                </div>

                <div id="inspection-details">
                    <h5 class="mb-3">Detail Inspeksi</h5>
                    @foreach ($report->details as $dIndex => $detail)
                    <div class="inspection-block border rounded p-3 mb-3 position-relative">
                        <label>Jam Inspeksi:</label>
                        <input type="time" name="details[{{ $dIndex }}][inspection_hour]"
                            class="form-control mb-3 col-md-5"
                            value="{{ $detail->inspection_hour }}" required>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Item</th>
                                    <th>Kondisi</th>
                                    <th>Catatan</th>
                                    <th>Tindakan Koreksi</th>
                                    <th>Verifikasi setelah tindakan koreksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detail->items as $iIndex => $item)
                                <tr>
                                    <td>{{ $iIndex + 1 }}</td>
                                    <td>
                                        <input type="hidden"
                                            name="details[{{ $dIndex }}][items][{{ $iIndex }}][item]"
                                            value="{{ $item->item }}">
                                        {{ $item->item }}
                                    </td>

                                    <td>
                                        @if(Str::contains($item->item, 'Suhu ruang'))
                                        <div class="row">
                                            <div class="col">
                                                <input type="number" step="0.1"
                                                    name="details[{{ $dIndex }}][items][{{ $iIndex }}][temperature_actual]"
                                                    value="{{ $item->temperature_actual }}"
                                                    class="form-control mb-1" placeholder="Actual">
                                            </div>
                                            <div class="col">
                                                <input type="number" step="0.1"
                                                    name="details[{{ $dIndex }}][items][{{ $iIndex }}][temperature_display]"
                                                    value="{{ $item->temperature_display }}"
                                                    class="form-control mb-1" placeholder="Display">
                                            </div>
                                        </div>
                                        @else
                                        <select
                                            name="details[{{ $dIndex }}][items][{{ $iIndex }}][condition]"
                                            class="form-control condition-select" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Bersih" {{ $item->condition == 'Bersih' ? 'selected' : '' }}>Bersih</option>
                                            <option value="Kotor" {{ $item->condition == 'Kotor' ? 'selected' : '' }}>Kotor</option>
                                        </select>
                                        @endif
                                    </td>

                                    <td>
                                        <input type="text"
                                            name="details[{{ $dIndex }}][items][{{ $iIndex }}][notes]"
                                            value="{{ $item->notes }}" class="form-control notes-input">
                                    </td>

                                    <td>
                                        <input type="text"
                                            name="details[{{ $dIndex }}][items][{{ $iIndex }}][corrective_action]"
                                            value="{{ $item->corrective_action }}" class="form-control action-input">
                                    </td>

                                    <td>
                                        <select
                                            name="details[{{ $dIndex }}][items][{{ $iIndex }}][verification]"
                                            class="form-control verification-select">
                                            <option value="">-- Pilih --</option>
                                            <option value="0" {{ $item->verification == 0 ? 'selected' : '' }}>Tidak OK</option>
                                            <option value="1" {{ $item->verification == 1 ? 'selected' : '' }}>OK</option>
                                        </select>
                                    </td>
                                </tr>

                                {{-- Koreksi Lanjutan --}}
                                <tr class="followup-row">
                                    <td colspan="6">
                                        <div class="followup-wrapper">
                                            @foreach($item->followups as $fIndex => $followup)
                                            <div class="followup-group border rounded p-2 mb-2">
                                                <label class="small mb-1">Koreksi Lanjutan #{{ $fIndex + 1 }}</label>
                                                <input type="text"
                                                    name="details[{{ $dIndex }}][items][{{ $iIndex }}][followups][{{ $fIndex }}][notes]"
                                                    value="{{ $followup->notes }}" class="form-control mb-1"
                                                    placeholder="Catatan">
                                                <input type="text"
                                                    name="details[{{ $dIndex }}][items][{{ $iIndex }}][followups][{{ $fIndex }}][action]"
                                                    value="{{ $followup->action }}" class="form-control mb-1"
                                                    placeholder="Tindakan Koreksi">
                                                <select
                                                    name="details[{{ $dIndex }}][items][{{ $iIndex }}][followups][{{ $fIndex }}][verification]"
                                                    class="form-control followup-verification">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="0" {{ $followup->verification == 0 ? 'selected' : '' }}>Tidak OK</option>
                                                    <option value="1" {{ $followup->verification == 1 ? 'selected' : '' }}>OK</option>
                                                </select>
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('condition-select')) {
        const row = e.target.closest('tr');
        const notes = row.querySelector('.notes-input');
        const action = row.querySelector('.action-input');
        const verification = row.querySelector('.verification-select');
        const wrapper = row.nextElementSibling.querySelector('.followup-wrapper');

        if (e.target.value === 'Bersih') {
            notes.value = '';
            action.value = '';
            verification.value = '1';
            notes.setAttribute('readonly', true);
            action.setAttribute('readonly', true);
            wrapper.innerHTML = '';
        } else {
            notes.removeAttribute('readonly');
            action.removeAttribute('readonly');
            verification.value = '0';
            wrapper.innerHTML = '';
            addFollowupField(row);
        }
    }
});

function addFollowupField(row) {
    const wrapperRow = row.nextElementSibling;
    const wrapper = wrapperRow.querySelector('.followup-wrapper');
    const baseName = row.querySelector('.verification-select').name.replace('[verification]', '');
    const count = wrapper.querySelectorAll('.followup-group').length;

    const html = `
        <div class="followup-group border rounded p-2 mb-2">
            <label class="small mb-1">Koreksi Lanjutan #${count + 1}</label>
            <input type="text" name="${baseName}[followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan">
            <input type="text" name="${baseName}[followups][${count}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
            <select name="${baseName}[followups][${count}][verification]" class="form-control followup-verification">
                <option value="">-- Pilih --</option>
                <option value="0">Tidak OK</option>
                <option value="1">OK</option>
            </select>
        </div>
    `;

    wrapper.insertAdjacentHTML('beforeend', html);

    const newSelect = wrapper.querySelectorAll('.followup-group')[count].querySelector('.followup-verification');
    newSelect.addEventListener('change', function() {
        const allFollowups = wrapper.querySelectorAll('.followup-group');
        const currentIndex = Array.from(allFollowups).indexOf(this.closest('.followup-group'));

        if (this.value === '0') {
            if (allFollowups.length === currentIndex + 1) {
                addFollowupField(row);
            }
        } else {
            for (let i = allFollowups.length - 1; i > currentIndex; i--) {
                allFollowups[i].remove();
            }
        }
    });
}
</script>
@endsection
