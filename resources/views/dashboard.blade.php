@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
    <p>Selamat datang di dashboard {{ Auth::user()->name }}!</p>

    {{-- Form filter: auto-submit, tanpa tombol --}}
    <div class="filter-bar-card mb-4">
        <form method="GET" action="{{ route('dashboard') }}" id="filterForm" class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="filter-label">
                    <i class="bi bi-building"></i> Plant
                </label>
                <select name="plant" class="form-select filter-input"
                    onchange="this.form.submit()"
                    @disabled(!$isSuperadmin)>
                    @foreach($plants as $p)
                        <option value="{{ $p->uuid }}" @selected($plant == $p->uuid)>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="filter-label">
                    <i class="bi bi-calendar3"></i> Tanggal
                </label>
                <input type="date" name="date" value="{{ $date }}" class="form-control filter-input" onchange="this.form.submit()">
            </div>

            <div class="col-md-3">
                <label class="filter-label">
                    <i class="bi bi-box-seam"></i> Produk
                </label>
                <select name="product" class="form-select filter-input" onchange="this.form.submit()">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->uuid }}" @selected($product == $p->uuid)>
                            {{ $p->product_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <a href="{{ route('dashboard') }}" class="btn btn-reset-filter w-100">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                </a>
            </div>

        </form>
    </div>

    <!-- CARD 1 -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <div class="row align-items-start">

                {{-- Kiri: Panel overview suhu (semua room) --}}
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="text-muted mb-2">Overview Suhu Tiap Ruang</h6>
                    <div class="active-filter-info mb-3">
                        <span class="filter-chip">
                            <i class="bi bi-building"></i>
                            {{ $plants->firstWhere('uuid', $plant)->name ?? '-' }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <iframe
                        src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&panelId=1&theme=light&timezone=UTC&var-plant={{ urlencode($plant) }}&var-date={{ $date }}"
                        width="100%" height="250" frameborder="0" loading="lazy">
                    </iframe>
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="text-muted mb-2">Detail per Ruangan</h6>
                    <div class="active-filter-info mb-3">
                        <span class="filter-chip">
                            <i class="bi bi-building"></i>
                            {{ $plants->firstWhere('uuid', $plant)->name ?? '-' }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <div id="suhuCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach(['Chillroom','Seasoning','MP','Cooking','Packing','Cartoning','Cooking Bakso','Pasteurisasi'] as $i => $room)
                            <div class="carousel-item @if($i == 0) active @endif">
                                <iframe
                                    src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=2&var-room={{ urlencode($room) }}&var-plant={{ urlencode($plant) }}&var-date={{ $date }}&timezone=UTC"
                                    width="100%" height="250" frameborder="0" loading="lazy">
                                </iframe>
                            </div>
                            @endforeach
                        </div>

                        <div class="carousel-indicators indicator-pill-group mt-3">
                            @foreach(['Chillroom','Seasoning','MP','Cooking','Packing','Cartoning','Cooking Bakso','Pasteurisasi'] as $i => $room)
                            <button type="button" data-bs-target="#suhuCarousel" data-bs-slide-to="{{ $i }}"
                                class="indicator-pill @if($i == 0) active @endif">
                                {{ $room }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Kanan: Panel verifikasi OK / Tidak OK per ruangan --}}
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Verifikasi Kebersihan Ruangan</h6>
                    <div class="active-filter-info mb-3">
                        <span class="filter-chip">
                            <i class="bi bi-building"></i>
                            {{ $plants->firstWhere('uuid', $plant)->name ?? '-' }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <iframe
                        src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=3&timezone=UTC&var-plant={{ urlencode($plant) }}&var-date={{ $date }}"
                        width="100%" height="250" frameborder="0" loading="lazy">
                    </iframe>

                    <div class="summary-card mt-3">
                        <div class="summary-title">Ruangan, mesin, dan peralatan</div>
                        <div class="summary-percentage">{{ $percentage }}%</div>
                        <div class="summary-detail">{{ $okPoints }} of {{ $totalPoints }} points</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- CARD 2 -->
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row align-items-start mb-5">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="text-muted mb-2">Kesesuaian per Mesin</h6>
                    <div class="active-filter-info mb-3">
                        <span class="filter-chip">
                            <i class="bi bi-building"></i>
                            {{ $plants->firstWhere('uuid', $plant)->name ?? '-' }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-box-seam"></i>
                            {{ $product ? ($products->firstWhere('uuid', $product)->product_name ?? '-') : 'Semua Produk' }}
                        </span>
                    </div>
                    <div id="mesinCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach(['townsend' => 'Townsend', 'hitech' => 'Hitech', 'vemag' => 'Vemag', 'vemag2' => 'Vemag 2', 'handtmann' => 'Handtmann'] as $value => $label)
                            <div class="carousel-item @if($loop->first) active @endif">
                                <iframe
                                    src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=4&var-machine={{ urlencode($value) }}&var-plant={{ urlencode($plant) }}&var-date={{ $date }}&var-product={{ urlencode($product) }}&timezone=UTC"
                                    width="100%" height="250" frameborder="0" loading="lazy">
                                </iframe>
                            </div>
                            @endforeach
                        </div>

                        <div class="carousel-indicators indicator-pill-group mt-3">
                            @foreach(['townsend' => 'Townsend', 'hitech' => 'Hitech', 'vemag' => 'Vemag', 'vemag2' => 'Vemag 2', 'handtmann' => 'Handtmann'] as $i => $label)
                            <button type="button" data-bs-target="#mesinCarousel" data-bs-slide-to="{{ $loop->index }}"
                                class="indicator-pill @if($loop->first) active @endif">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="row align-items-start">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="text-muted mb-2">Trend Pengukuran</h6>

                    <div class="active-filter-info mb-3">
                        <span class="filter-chip">
                            <i class="bi bi-building"></i>
                            {{ $plants->firstWhere('uuid', $plant)->name ?? '-' }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-box-seam"></i>
                            {{ $product ? ($products->firstWhere('uuid', $product)->product_name ?? '-') : 'Semua Produk' }}
                        </span>
                    </div>

                    <iframe
                        id="trendIframe"
                        src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=5&var-parameter=long&var-machine=townsend&var-plant={{ urlencode($plant) }}&var-date={{ $date }}&var-product={{ urlencode($product) }}&timezone=UTC"
                        width="100%" height="250" frameborder="0" loading="lazy">
                    </iframe>

                    <!-- <div class="pill-group-label">Parameter</div> -->
                    <div class="indicator-pill-group mb-2 mt-3" id="parameterPills">
                        <button type="button" class="indicator-pill active" data-value="long">Panjang</button>
                        <button type="button" class="indicator-pill" data-value="weight">Berat</button>
                        <button type="button" class="indicator-pill" data-value="diameter">Diameter</button>
                    </div>

                    <!-- <div class="pill-group-label">Mesin</div> -->
                    <div class="indicator-pill-group mb-3" id="machinePills">
                        <button type="button" class="indicator-pill active" data-value="townsend">Townsend</button>
                        <button type="button" class="indicator-pill" data-value="hitech">Hitech</button>
                        <button type="button" class="indicator-pill" data-value="vemag">Vemag</button>
                        <button type="button" class="indicator-pill" data-value="vemag2">Vemag 2</button>
                        <button type="button" class="indicator-pill" data-value="handtmann">Handtmann</button>
                    </div>
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="text-muted mb-2">Trend Suhu Pemasakan</h6>

                    <div class="active-filter-info mb-3">
                        <span class="filter-chip">
                            <i class="bi bi-building"></i>
                            {{ $plants->firstWhere('uuid', $plant)->name ?? '-' }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </span>
                        <span class="filter-chip">
                            <i class="bi bi-box-seam"></i>
                            {{ $product ? ($products->firstWhere('uuid', $product)->product_name ?? '-') : 'Semua Produk' }}
                        </span>
                    </div>

                    <div id="smokehouseCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach(['Fessmann','Maurer','Bastra','Vemag'] as $i => $machine)
                            <div class="carousel-item @if($i == 0) active @endif">
                                <iframe
                                    src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=6&var-machine={{ urlencode($machine) }}&var-plant={{ urlencode($plant) }}&var-date={{ $date }}&var-product={{ urlencode($product) }}&timezone=UTC"
                                    width="100%" height="250" frameborder="0" loading="lazy">
                                </iframe>
                            </div>
                            @endforeach
                        </div>

                        <div class="carousel-indicators indicator-pill-group mt-3">
                            @foreach(['Fessmann','Maurer','Bastra','Vemag'] as $i => $machine)
                            <button type="button" data-bs-target="#smokehouseCarousel" data-bs-slide-to="{{ $i }}"
                                class="indicator-pill @if($i == 0) active @endif">
                                {{ $machine }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const iframe = document.getElementById('trendIframe');
    const baseUrl = "http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang";
    const staticParams = "orgId=1&theme=light&panelId=5&var-plant={{ urlencode($plant) }}&var-date={{ $date }}&var-product={{ urlencode($product) }}&timezone=UTC";

    let selectedParameter = 'long';
    let selectedMachine = 'townsend';

    function updateIframe() {
        iframe.src = baseUrl + '?' + staticParams +
            '&var-parameter=' + encodeURIComponent(selectedParameter) +
            '&var-machine=' + encodeURIComponent(selectedMachine);
    }

    function setupPillGroup(containerId, onSelect) {
        const container = document.getElementById(containerId);
        container.querySelectorAll('.indicator-pill').forEach(function (btn) {
            btn.addEventListener('click', function () {
                container.querySelectorAll('.indicator-pill').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                onSelect(btn.dataset.value);
                updateIframe();
            });
        });
    }

    setupPillGroup('parameterPills', function (val) { selectedParameter = val; });
    setupPillGroup('machinePills', function (val) { selectedMachine = val; });
})();
</script>

@endsection