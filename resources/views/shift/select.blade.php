@extends('layouts.app-no-sidebar')

@section('style')
<style>
/* Buat container full height dan center */
.container-fluid {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 4.375rem);
    /* 4.375rem = tinggi topbar standar */
    padding: 20px;
}

/* Card Shift Selection */
.shift-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    padding: 40px;
    max-width: 500px;
    width: 100%;
    margin: 0 auto;
}

.shift-option {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s;
}

.shift-option:hover {
    border-color: #cc7064;
    background-color: #fff4f2;
    transform: translateY(-2px);
}

.shift-option.selected {
    border-color: #cc7064;
    background-color: #fff0ed;
}

.shift-option input[type="radio"] {
    display: none;
}

.shift-option label {
    cursor: pointer;
    margin-bottom: 0;
}

.group-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.group-btn {
    flex: 1;
    min-width: 60px;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
}

.group-btn:hover {
    border-color: #cc7064;
    background-color: #fff4f2;
}

.group-btn.selected {
    border-color: #cc7064;
    background-color: #cc7064;
    color: white;
}

.submit-btn {
    background: #cc7064;
    border: none;
    padding: 12px 40px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    width: 100%;
    margin-top: 20px;
    transition: all 0.3s;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(204, 112, 100, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 576px) {
    .container-fluid {
        min-height: calc(100vh - 3.5rem);
        padding: 15px;
    }

    .shift-card {
        padding: 30px 20px;
    }

    .group-btn {
        min-width: 50px;
        font-size: 14px;
    }
}
</style>
@endsection

@section('content')
<div class="shift-card">
    <h2 class="text-center mb-4">{{ isset($isChange) && $isChange ? 'Ubah Shift' : 'Pilih Shift Anda' }}</h2>
    <p class="text-center text-muted mb-4">Silakan pilih shift dan group untuk hari ini</p>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('shift.store') }}" method="POST" id="shiftForm">
        @csrf

        <div class="mb-4">
            <label class="form-label fw-bold">Pilih Shift:</label>

            @for($i = 1; $i <= 3; $i++) <div class="shift-option" data-shift="{{ $i }}">
                <input type="radio" name="shift_number" value="{{ $i }}" id="shift{{ $i }}" required>
                <label for="shift{{ $i }}" class="d-block">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="mb-0">Shift {{ $i }}</h5>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-check-circle fs-4 text-primary d-none"></i>
                        </div>
                    </div>
                </label>
        </div>
        @endfor
</div>

<div class="mb-4">
    <label class="form-label fw-bold">Pilih Group:</label>
    <div class="group-buttons">
        @foreach(['A', 'B', 'C', 'D', 'E'] as $group)
        <button type="button" class="group-btn" data-group="{{ $group }}">
            {{ $group }}
        </button>
        @endforeach
    </div>
    <input type="hidden" name="group" id="groupInput" required>
</div>

<button type="submit" class="submit-btn" id="submitBtn" disabled>
    Konfirmasi Shift
</button>

@if(isset($isChange) && $isChange)
<div class="text-center mt-3">
    <a href="{{ route('dashboard') }}" class="text-muted">Kembali ke Dashboard</a>
</div>
@endif
</form>
</div>
@endsection

@section('script')
<script>
let selectedShift = null;
let selectedGroup = null;

// Handle shift selection
document.querySelectorAll('.shift-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.shift-option').forEach(opt => {
            opt.classList.remove('selected');
            opt.querySelector('i').classList.add('d-none');
        });

        this.classList.add('selected');
        this.querySelector('i').classList.remove('d-none');
        this.querySelector('input[type="radio"]').checked = true;

        selectedShift = this.dataset.shift;
        checkFormValidity();
    });
});

// Handle group selection
document.querySelectorAll('.group-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.group-btn').forEach(b => {
            b.classList.remove('selected');
        });

        this.classList.add('selected');
        selectedGroup = this.dataset.group;
        document.getElementById('groupInput').value = selectedGroup;

        checkFormValidity();
    });
});

function checkFormValidity() {
    const submitBtn = document.getElementById('submitBtn');
    if (selectedShift && selectedGroup) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
}

// Prevent form submission if not valid
document.getElementById('shiftForm').addEventListener('submit', function(e) {
    if (!selectedShift || !selectedGroup) {
        e.preventDefault();
        alert('Silakan pilih shift dan group terlebih dahulu');
    }
});
</script>
@endsection