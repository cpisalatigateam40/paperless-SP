@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')

<div class="container-fluid">
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
    <!-- @hasanyrole(['SPV QC', 'superadmin', 'QC Inspector'])
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <div class="row align-items-strech">

                {{-- Kiri: Panel overview suhu (semua room) --}}
                <div class="col-md-4 mb-3 mb-md-0 col-with-divider">
                    <h6 class="section-header mb-2">Overview Suhu Tiap Ruang</h6>
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

                <div class="col-md-4 mb-3 mb-md-0 col-with-divider">
                    <h6 class="section-header mb-2">Detail per Ruangan</h6>
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
                    <h6 class="section-header mb-2">Verifikasi Kebersihan Ruangan</h6>
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
    @endhasanyrole -->

    <!-- CARD 2 -->
    <!-- <div class="card shadow-sm mb-5">
        <div class="card-body">

            <div class="row align-items-stretch mb-5">
                <div class="col-md-4 mb-3 mb-md-0 col-with-divider">
                    <h6 class="mb-2 section-header">Kesesuaian per Mesin</h6>
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

            <div class="row align-items-stretch mb-5">
                <div class="col-md-4 mb-3 mb-md-0 col-with-divider">
                    <h6 class="section-header mb-2">Trend Pengukuran</h6>

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

                    
                    <div class="indicator-pill-group mb-2 mt-3" id="parameterPills">
                        <button type="button" class="indicator-pill active" data-value="long">Panjang</button>
                        <button type="button" class="indicator-pill" data-value="weight">Berat</button>
                        <button type="button" class="indicator-pill" data-value="diameter">Diameter</button>
                    </div>

                    
                    <div class="indicator-pill-group mb-3" id="machinePills">
                        <button type="button" class="indicator-pill active" data-value="townsend">Townsend</button>
                        <button type="button" class="indicator-pill" data-value="hitech">Hitech</button>
                        <button type="button" class="indicator-pill" data-value="vemag">Vemag</button>
                        <button type="button" class="indicator-pill" data-value="vemag2">Vemag 2</button>
                        <button type="button" class="indicator-pill" data-value="handtmann">Handtmann</button>
                    </div>


                    <div class="row mt-5">
                        <div class="col-md-6">
                            <div class="incoming-card {{ $incomingRmNgPercent > 0 ? 'has-issue' : '' }}">
                            <div class="incoming-card-title">
                                <i class="bi bi-truck"></i> Incoming RM
                            </div>

                            <div class="incoming-main">
                                Incoming {{ $incomingRmTotal }}x
                            </div>

                            <div class="incoming-detail">
                                <span class="incoming-ok">{{ $incomingRmOk }} OK</span>
                                <span class="incoming-sep">;</span>
                                <span class="incoming-notok">{{ $incomingRmNotOk }} Tidak OK</span>
                            </div>

                            <div class="incoming-ng">
                                <i class="bi bi-caret-right-fill"></i> NG {{ $incomingRmNgPercent }}%
                            </div>
                        </div>
                        </div>

                        <div class="col-md-6">
                            <div class="incoming-card {{ $sensoryNgPercent > 0 ? 'has-issue' : '' }}">
                                <div class="incoming-card-title">
                                    <i class="bi bi-clipboard2-check"></i> Sensory
                                </div>

                                <div class="incoming-main">
                                    RM {{ $sensoryTotal }} penilaian
                                </div>

                                <div class="incoming-detail">
                                    <span class="incoming-ok">{{ $sensoryOk }} OK</span>
                                    <span class="incoming-sep">;</span>
                                    <span class="incoming-notok">{{ $sensoryNotOk }} Tidak OK</span>
                                </div>

                                <div class="incoming-ng">
                                    <i class="bi bi-caret-right-fill"></i> NG {{ $sensoryNgPercent }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3 mb-md-0 col-with-divider">
                    <h6 class="section-header mb-2">Trend Suhu Pemasakan</h6>

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
                            @foreach($machines as $i => $machine)
                            <div class="carousel-item @if($i == 0) active @endif">
                                <iframe
                                    src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=6&var-machine={{ urlencode($machine) }}&var-plant={{ urlencode($plant) }}&var-date={{ $date }}&var-product={{ urlencode($product) }}&timezone=UTC"
                                    width="100%" height="250" frameborder="0" loading="lazy">
                                </iframe>
                            </div>
                            @endforeach
                        </div>

                        <div class="carousel-indicators indicator-pill-group mt-3">
                            @foreach($machines as $i => $machine)
                            <button type="button" data-bs-target="#smokehouseCarousel" data-bs-slide-to="{{ $i }}"
                                class="indicator-pill @if($i == 0) active @endif">
                                {{ $machine }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-12 col-lg-12 mb-3">
                            <div class="incoming-card core-temp-card" id="coreTempCard">
                                <div class="incoming-card-title">
                                    <i class="bi bi-thermometer-half"></i> Core Temp
                                    <span class="core-temp-machine-label" id="coreTempMachineLabel">- Fessmann</span>
                                </div>

                                <div id="coreTempContent">
                                    {{-- diisi otomatis oleh JS --}}
                                </div>

                                <div class="mt-3">
                                    <select class="form-select form-select-sm core-temp-select" id="coreTempStepSelect">
                                        @foreach($processes as $processName)
                                        <option value="{{ $processName }}" @if($loop->first) selected @endif>
                                            {{ $processName }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div> -->

    <!-- CARD 3 -->
    <!-- <div class="section-card mb-5 shadow-sm">
        <div class="section-header">
            <i class="bi bi-activity"></i> Current Processing
        </div>

        <div class="row align-items-stretch">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card {{ $mixingIsCurrent ? 'is-active' : '' }}">
                    <div class="processing-card-top">
                        <div class="processing-card-title">
                            <i class="bi bi-shuffle"></i> MIXING
                        </div>
                        @if($mixingIsCurrent)
                            <span class="status-badge">
                                <span class="pulse-dot"></span> Sedang Berjalan
                            </span>
                        @endif
                    </div>

                    @if($mixingIsCurrent)
                        <div class="processing-product">{{ $mixingLatest->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $mixingLatest->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $mixingLatest->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $mixingLatest->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada proses berjalan
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card {{ $stuffingIsCurrent ? 'is-active' : '' }}">
                    <div class="processing-card-top">
                        <div class="processing-card-title">
                            <i class="bi bi-egg-fried"></i> STUFFING
                        </div>
                        @if($stuffingIsCurrent)
                            <span class="status-badge">
                                <span class="pulse-dot"></span> Sedang Berjalan
                            </span>
                        @endif
                    </div>

                    @if($stuffingIsCurrent)
                        <div class="processing-product">{{ $stuffingLatest->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $stuffingLatest->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $stuffingLatest->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $stuffingLatest->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada proses berjalan
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card {{ $cookingIsCurrent ? 'is-active' : '' }}">
                    <div class="processing-card-top">
                        <div class="processing-card-title">
                            <i class="bi bi-fire"></i> COOKING
                        </div>
                        @if($cookingIsCurrent)
                            <span class="status-badge">
                                <span class="pulse-dot"></span> Sedang Berjalan
                            </span>
                        @endif
                    </div>

                    @if($cookingIsCurrent)
                        <div class="processing-product">{{ $cookingLatest->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $cookingLatest->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $cookingLatest->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $cookingLatest->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada proses berjalan
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card {{ $pasteurizingIsCurrent ? 'is-active' : '' }}">
                    <div class="processing-card-top">
                        <div class="processing-card-title">
                            <i class="bi bi-droplet-half"></i> PASTEURIZING
                        </div>
                        @if($pasteurizingIsCurrent)
                            <span class="status-badge">
                                <span class="pulse-dot"></span> Sedang Berjalan
                            </span>
                        @endif
                    </div>

                    @if($pasteurizingIsCurrent)
                        <div class="processing-product">{{ $pasteurizingLatest->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $pasteurizingCode ?? '-' }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $pasteurizingLatest->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $pasteurizingLatest->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada proses berjalan
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card {{ $packingIsCurrent ? 'is-active' : '' }}">
                    <div class="processing-card-top">
                        <div class="processing-card-title">
                            <i class="bi bi-box-seam-fill"></i> PACKING
                        </div>
                        @if($packingIsCurrent)
                            <span class="status-badge">
                                <span class="pulse-dot"></span> Sedang Berjalan
                            </span>
                        @endif
                    </div>

                    @if($packingIsCurrent)
                        <div class="processing-product">{{ $packingLatest->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $packingLatest->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $packingLatest->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $packingLatest->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada proses berjalan
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card {{ $cartoningIsCurrent ? 'is-active' : '' }}">
                    <div class="processing-card-top">
                        <div class="processing-card-title">
                            <i class="bi bi-box2-fill"></i> CARTONING
                        </div>
                        @if($cartoningIsCurrent)
                            <span class="status-badge">
                                <span class="pulse-dot"></span> Sedang Berjalan
                            </span>
                        @endif
                    </div>

                    @if($cartoningIsCurrent)
                        <div class="processing-product">{{ $cartoningLatest->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $cartoningLatest->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $cartoningLatest->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $cartoningLatest->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada proses berjalan
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div> -->

    <!-- CARD 4 -->
    <!-- <div class="section-card mb-5 shadow-sm">
        <div class="section-header">
            <i class="bi bi-activity"></i> Produksi Sebelumnya
        </div>

        <div class="row align-items-stretch">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card is-previous">
                    <div class="processing-card-top">
                        <div class="processing-card-title text-previous">
                            <i class="bi bi-shuffle"></i> MIXING (Sebelumnya)
                        </div>
                    </div>

                    @if($mixingPrevious)
                        <div class="processing-product">{{ $mixingPrevious->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $mixingPrevious->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $mixingPrevious->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $mixingPrevious->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada data sebelumnya
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card is-previous">
                    <div class="processing-card-top">
                        <div class="processing-card-title text-previous">
                            <i class="bi bi-egg-fried"></i> STUFFING (Sebelumnya)
                        </div>
                    </div>

                    @if($stuffingPrevious)
                        <div class="processing-product">{{ $stuffingPrevious->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $stuffingPrevious->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $stuffingPrevious->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $stuffingPrevious->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada data sebelumnya
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card is-previous">
                    <div class="processing-card-top">
                        <div class="processing-card-title text-previous">
                            <i class="bi bi-fire"></i> COOKING (Sebelumnya)
                        </div>
                    </div>

                    @if($cookingPrevious)
                        <div class="processing-product">{{ $cookingPrevious->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $cookingPrevious->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $cookingPrevious->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $cookingPrevious->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada data sebelumnya
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card is-previous">
                    <div class="processing-card-top">
                        <div class="processing-card-title text-previous">
                            <i class="bi bi-droplet-half"></i> PASTEURIZING (Sebelumnya)
                        </div>
                    </div>

                    @if($pasteurizingPrevious)
                        <div class="processing-product">{{ $pasteurizingPrevious->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $pasteurizingPreviousCode ?? '-' }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $pasteurizingPrevious->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $pasteurizingPrevious->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada data sebelumnya
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="processing-card is-previous">
                    <div class="processing-card-top">
                        <div class="processing-card-title text-previous">
                            <i class="bi bi-box-seam-fill"></i> PACKING (Sebelumnya)
                        </div>
                    </div>

                    @if($packingPrevious)
                        <div class="processing-product">{{ $packingPrevious->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $packingPrevious->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $packingPrevious->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $packingPrevious->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada data sebelumnya
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-3">
                <div class="processing-card is-previous">
                    <div class="processing-card-top">
                        <div class="processing-card-title text-previous">
                            <i class="bi bi-box2-fill"></i> CARTONING (Sebelumnya)
                        </div>
                    </div>

                    @if($cartoningPrevious)
                        <div class="processing-product">{{ $cartoningPrevious->product->product_name ?? '-' }}</div>
                        <div class="processing-code">
                            <i class="bi bi-upc-scan"></i> {{ $cartoningPrevious->production_code }}
                        </div>
                        <div class="processing-time">
                            <i class="bi bi-clock"></i> {{ $cartoningPrevious->created_at->format('H.i') }}
                            <span class="processing-ago">({{ $cartoningPrevious->created_at->diffForHumans() }})</span>
                        </div>
                    @else
                        <p class="processing-empty">
                            <i class="bi bi-moon-stars"></i> Belum ada data sebelumnya
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div> -->

    
    @hasanyrole([ 'SPV QC'])
    <div class="process-status-container mb-5">
        <div class="process-status-banner">
            <span class="process-status-number">1</span>
            <span class="process-status-title">PROCESS PRODUCTION STATUS</span>
            <span class="process-status-subtitle">Real-time production flow and QC verification status</span>
        </div>

        <div class="process-status-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="spv-mixing-card">
                        <div class="pipeline-header pipeline-header-blue">
                            <div>
                                <i class="bi bi-egg-fried"></i> MEAT PREPARATION
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Stuffing <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="spv-mixing-body">
                            @if($mixingLatest)
                                <div class="spv-mixing-label">Current Product</div>
                                <div class="spv-mixing-product">{{ $mixingLatest->product->product_name ?? '-' }}</div>
                                <div class="spv-mixing-batch">Batch No. {{ $mixingLatest->production_code ?? '-' }}</div>

                                <div class="spv-mixing-status-row">
                                    <span class="spv-mixing-status-label">Status</span>
                                    @if($mixingStageStatus === 'running')
                                        <span class="status-badge-running"><span class="pulse-dot"></span> RUNNING</span>
                                    @elseif($mixingStageStatus === 'completed')
                                        <span class="status-badge-completed">COMPLETED</span>
                                    @else
                                        <span class="status-badge-waiting">WAITING</span>
                                    @endif
                                </div>

                                <div class="spv-mixing-time">
                                    @if($mixingLatest)
                                        Start {{ $mixingLatest->created_at->format('H:i') }}
                                        | Last QC {{ $mixingLatest->updated_at->format('H:i') }}
                                    @else
                                        Belum ada data
                                    @endif
                                </div>

                                <hr>

                                <div class="spv-mixing-label mb-2">QC Verification</div>

                                <div class="spv-qc-item">
                                    @if($mixingForeignOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($mixingForeignOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Visual / Foreign Material
                                </div>

                                <div class="spv-qc-item">
                                    @if($mixingTemperatureOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($mixingTemperatureOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Temperature
                                </div>

                                <div class="spv-qc-item">
                                    @if($mixingWeighingOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($mixingWeighingOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Weighing
                                </div>
                            @else
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data mixing
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="spv-mixing-card">
                        <div class="pipeline-header pipeline-header-teal">
                            <div>
                                <i class="bi bi-egg-fried"></i> STUFFING
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Cooking <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="spv-mixing-body">
                            @if($mixingLatest)
                                <div class="spv-mixing-label">Current Product</div>
                                <div class="spv-mixing-product">{{ $mixingLatest->product->product_name ?? '-' }}</div>
                                <div class="spv-mixing-batch">Batch No. {{ $mixingLatest->production_code }}</div>

                                <div class="spv-mixing-status-row">
                                    <span class="spv-mixing-status-label">Status</span>
                                    @if($stuffingStageStatus === 'running')
                                        <span class="status-badge-running"><span class="pulse-dot"></span> RUNNING</span>
                                    @elseif($stuffingStageStatus === 'completed')
                                        <span class="status-badge-completed">COMPLETED</span>
                                    @else
                                        <span class="status-badge-waiting">WAITING</span>
                                    @endif
                                </div>

                                <div class="spv-mixing-time">
                                    @if($stuffingLatest)
                                        Start {{ $stuffingLatest->created_at->format('H:i') }}
                                        | Last QC {{ $stuffingLatest->updated_at->format('H:i') }}
                                    @else
                                        Belum ada data
                                    @endif
                                </div>

                                <hr>

                                <div class="spv-mixing-label mb-2">QC Verification</div>

                                <div class="spv-qc-item">
                                    @if($stuffingWeighingOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($stuffingWeighingOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Weight
                                </div>

                                <div class="spv-qc-item">
                                    @if($stuffingLengthOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($stuffingLengthOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Length
                                </div>

                                <div class="spv-qc-item">
                                    @if($stuffingDiameterOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($stuffingDiameterOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Diameter
                                </div>
                            @else
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data mixing
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="spv-mixing-card">
                        <div class="pipeline-header pipeline-header-orange">
                            <div>
                                <i class="bi bi-fire"></i> COOKING
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Pasteurization <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="spv-mixing-body">
                            @if($mixingLatest)
                                <div class="spv-mixing-label">Current Product</div>
                                <div class="spv-mixing-product">{{ $mixingLatest->product->product_name ?? '-' }}</div>
                                <div class="spv-mixing-batch">Batch No. {{ $mixingLatest->production_code }}</div>

                                <div class="spv-mixing-status-row">
                                    <span class="spv-mixing-status-label">Status</span>
                                    @if($cookingStageStatus === 'running')
                                        <span class="status-badge-running"><span class="pulse-dot"></span> RUNNING</span>
                                    @elseif($cookingStageStatus === 'completed')
                                        <span class="status-badge-completed">COMPLETED</span>
                                    @else
                                        <span class="status-badge-waiting">WAITING</span>
                                    @endif
                                </div>

                                <div class="spv-mixing-time">
                                    @if($cookingLatest)
                                        Start {{ $cookingLatest->created_at->format('H:i') }}
                                        | Last QC {{ $cookingLatest->updated_at->format('H:i') }}
                                    @else
                                        Belum ada data
                                    @endif
                                </div>

                                <hr>

                                <div class="spv-mixing-label mb-2">QC Verification</div>

                                <div class="spv-qc-item">
                                    @if($cookingCoreTempOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($cookingCoreTempOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Core Temp
                                </div>

                                <div class="spv-qc-item">
                                    @if($cookingSensoryOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($cookingSensoryOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Sensory
                                </div>
                            @else
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data mixing
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="spv-mixing-card">
                        <div class="pipeline-header pipeline-header-purple">
                            <div>
                                <i class="bi bi-droplet-half"></i> PASTEURIZATION
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Packing <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="spv-mixing-body">
                            @if($mixingLatest)
                                <div class="spv-mixing-label">Current Product</div>
                                <div class="spv-mixing-product">{{ $mixingLatest->product->product_name ?? '-' }}</div>
                                <div class="spv-mixing-batch">Batch No. {{ $mixingLatest->production_code }}</div>

                                <div class="spv-mixing-status-row">
                                    <span class="spv-mixing-status-label">Status</span>
                                    @if($pasteurizingStageStatus === 'running')
                                        <span class="status-badge-running"><span class="pulse-dot"></span> RUNNING</span>
                                    @elseif($pasteurizingStageStatus === 'completed')
                                        <span class="status-badge-completed">COMPLETED</span>
                                    @else
                                        <span class="status-badge-waiting">WAITING</span>
                                    @endif
                                </div>

                                <div class="spv-mixing-time">
                                    @if($pasteurizingLatest)
                                        Start {{ $pasteurizingLatest->created_at->format('H:i') }}
                                        | Last QC {{ $pasteurizingLatest->updated_at->format('H:i') }}
                                    @else
                                        Belum ada data
                                    @endif
                                </div>

                                <hr>

                                <div class="spv-mixing-label mb-2">QC Verification</div>

                                <div class="spv-qc-item">
                                    @if($pasteurizingTemperatureOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($pasteurizingTemperatureOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Temperature
                                </div>
                            @else
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data mixing
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="spv-mixing-card">
                        <div class="pipeline-header pipeline-header-teal">
                            <div>
                                <i class="bi bi-box-seam-fill"></i> PACKING
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Cartoning <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="spv-mixing-body">
                            @if($mixingLatest)
                                <div class="spv-mixing-label">Current Product</div>
                                <div class="spv-mixing-product">{{ $mixingLatest->product->product_name ?? '-' }}</div>
                                <div class="spv-mixing-batch">Batch No. {{ $mixingLatest->production_code }}</div>

                                <div class="spv-mixing-status-row">
                                    <span class="spv-mixing-status-label">Status</span>
                                    @if($packingStageStatus === 'running')
                                        <span class="status-badge-running"><span class="pulse-dot"></span> RUNNING</span>
                                    @elseif($packingStageStatus === 'completed')
                                        <span class="status-badge-completed">COMPLETED</span>
                                    @else
                                        <span class="status-badge-waiting">WAITING</span>
                                    @endif
                                </div>

                                <div class="spv-mixing-time">
                                    @if($packingLatest)
                                        Start {{ $packingLatest->created_at->format('H:i') }}
                                        | Last QC {{ $packingLatest->updated_at->format('H:i') }}
                                    @else
                                        Belum ada data
                                    @endif
                                </div>

                                <hr>

                                <div class="spv-mixing-label mb-2">QC Verification</div>

                                <div class="spv-qc-item">
                                    @if($packingWeightOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($packingWeightOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Weight
                                </div>

                                <div class="spv-qc-item">
                                    @if($packingLengthOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($packingLengthOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Length
                                </div>
                            @else
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data mixing
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="spv-mixing-card">
                        <div class="pipeline-header pipeline-header-blue">
                            <div>
                                <i class="bi bi-box2-fill"></i> CARTONING
                            </div>
                        </div>

                        <div class="spv-mixing-body">
                            @if($mixingLatest)
                                <div class="spv-mixing-label">Current Product</div>
                                <div class="spv-mixing-product">{{ $mixingLatest->product->product_name ?? '-' }}</div>
                                <div class="spv-mixing-batch">Batch No. {{ $mixingLatest->production_code }}</div>

                                <div class="spv-mixing-status-row">
                                    <span class="spv-mixing-status-label">Status</span>
                                    @if($cartoningStageStatus === 'running')
                                        <span class="status-badge-running"><span class="pulse-dot"></span> RUNNING</span>
                                    @elseif($cartoningStageStatus === 'completed')
                                        <span class="status-badge-completed">COMPLETED</span>
                                    @else
                                        <span class="status-badge-waiting">WAITING</span>
                                    @endif
                                </div>

                                <div class="spv-mixing-time">
                                    @if($cartoningLatest)
                                        Start {{ $cartoningLatest->created_at->format('H:i') }}
                                        | Last QC {{ $cartoningLatest->updated_at->format('H:i') }}
                                    @else
                                        Belum ada data
                                    @endif
                                </div>

                                <hr>

                                <div class="spv-mixing-label mb-2">QC Verification</div>

                                <div class="spv-qc-item">
                                    @if($cartoningCartonOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($cartoningCartonOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Carton Condition
                                </div>

                                <div class="spv-qc-item">
                                    @if($cartoningLabelOk === true)
                                        <i class="bi bi-check-circle-fill spv-qc-ok"></i>
                                    @elseif($cartoningLabelOk === false)
                                        <i class="bi bi-x-circle-fill spv-qc-ng"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                    Label Condition
                                </div>
                            @else
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data mixing
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="journey-container">
                <div class="journey-flex">

                    {{-- Kiri: Tabel Batch Tracking --}}
                    <div class="tracking-wrapper">
                        <div class="tracking-header">
                            <strong>PRODUCT JOURNEY</strong> <span class="tracking-subtitle">(Batch Tracking)</span>
                        </div>

                        <div class="table-responsive">
                            <table class="tracking-table">
                                <thead>
                                    <tr>
                                        <th>Batch No.</th>
                                        <th>Product</th>
                                        <th><i class="bi bi-egg-fried"></i> Meat Preparation</th>
                                        <th><i class="bi bi-egg-fried"></i> Stuffing</th>
                                        <th><i class="bi bi-fire"></i> Cooking</th>
                                        <th><i class="bi bi-droplet-half"></i> Pasteurization</th>
                                        <th><i class="bi bi-box-seam-fill"></i> Packing</th>
                                        <th><i class="bi bi-box2-fill"></i> Cartoning</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batchTracking as $row)
                                        <tr>
                                            <td class="tracking-batch">{{ $row->batch_no }}</td>
                                            <td class="tracking-product">{{ $row->product_name }}</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->meat_preparation, 'time' => $row->meat_preparation_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->stuffing, 'time' => $row->stuffing_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->cooking, 'time' => $row->cooking_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->pasteurization, 'time' => $row->pasteurization_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->packing, 'time' => $row->packing_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->cartoning, 'time' => $row->cartoning_time])</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada data batch</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Kanan: Production at a Glance --}}
                    <div class="overall-status-wrapper">
                        <div class="glance-card">
                            <div class="glance-header">Production at a Glance</div>

                            <div class="glance-body">
                                <div class="glance-item">
                                    <i class="bi bi-calendar3 glance-icon"></i>
                                    <div class="glance-label">Total Products Running</div>
                                    <div class="glance-value">{{ $totalProductsRunning }}</div>
                                </div>

                                <div class="glance-item">
                                    <i class="bi bi-clipboard-data glance-icon"></i>
                                    <div class="glance-label">Total Batches Today</div>
                                    <div class="glance-value">{{ $totalBatchesToday }}</div>
                                </div>

                                <!-- <div class="glance-item">
                                    <i class="bi bi-box-seam glance-icon"></i>
                                    <div class="glance-label">Total Output (est.)</div>
                                    <div class="glance-value">-</div>
                                </div> -->
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endhasanyrole
    
    @hasanyrole(['superadmin', 'QC Inspector'])
    <div class="process-status-container mb-5">
        <div class="process-status-banner">
            <span class="process-status-number">1</span>
            <span class="process-status-title">PROCESS PRODUCTION STATUS</span>
            <span class="process-status-subtitle">Real-time production flow and QC verification status</span>
        </div>

        <div class="process-status-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="pipeline-stage">
                        <div class="pipeline-header pipeline-header-blue">
                            <div>
                                <i class="bi bi-egg-fried"></i> MEAT PREPARATION
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Stuffing <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="pipeline-card">
                            <div class="pipeline-card-title">Products Running</div>

                            @forelse($mixingBatches as $index => $batch)
                                <div class="pipeline-item">
                                    <div class="pipeline-item-info">
                                        <div class="pipeline-item-name">{{ $batch->product->product_name ?? '-' }}</div>
                                        <div class="pipeline-item-batch">Batch {{ $batch->production_code }}</div>
                                    </div>
                                    <div class="pipeline-item-status">
                                        @include('partials.pipeline-item-status', ['status' => $batchTracking[$index]->meat_preparation])
                                        <span class="pipeline-item-time">{{ $batch->created_at->format('H.i') }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data mixing
                                </p>
                            @endforelse

                            @if($mixingLastCheck)
                                <div class="pipeline-footer">
                                    <span>Last QC Check {{ $mixingLastCheck->format('H.i') }}</span>
                                    <a href="#" class="pipeline-view-link">View Details <i class="bi bi-caret-right-fill"></i></a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="pipeline-stage">
                        <div class="pipeline-header pipeline-header-teal">
                            <div>
                                <i class="bi bi-egg-fried"></i> STUFFING
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Cooking <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="pipeline-card">
                            <div class="pipeline-card-title">Products Running</div>

                            @forelse($stuffingStatuses as $index => $item)
                                <div class="pipeline-item">
                                    <div class="pipeline-item-info">
                                        <div class="pipeline-item-name">{{ $item->product_name }}</div>
                                        <div class="pipeline-item-batch">Batch {{ $item->production_code }}</div>
                                    </div>
                                    <div class="pipeline-item-status">
                                        @include('partials.pipeline-item-status', ['status' => $batchTracking[$index]->stuffing])
                                        <span class="pipeline-item-time">{{ $item->created_at ? $item->created_at->format('H.i') : '-' }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data
                                </p>
                            @endforelse

                            @if($stuffingLastCheck)
                                <div class="pipeline-footer">
                                    <span>Last QC Check {{ $stuffingLastCheck->format('H.i') }}</span>
                                    <a href="#" class="pipeline-view-link">View Details <i class="bi bi-caret-right-fill"></i></a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="pipeline-stage">
                        <div class="pipeline-header pipeline-header-orange">
                            <div>
                                <i class="bi bi-fire"></i> COOKING
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Pasteurization <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="pipeline-card">
                            <div class="pipeline-card-title">Products Running</div>

                            @forelse($cookingStatuses as $index => $item)
                                <div class="pipeline-item">
                                    <div class="pipeline-item-info">
                                        <div class="pipeline-item-name">{{ $item->product_name }}</div>
                                        <div class="pipeline-item-batch">Batch {{ $item->production_code }}</div>
                                    </div>
                                    <div class="pipeline-item-status">
                                        @include('partials.pipeline-item-status', ['status' => $batchTracking[$index]->cooking])
                                        <span class="pipeline-item-time">{{ $item->created_at ? $item->created_at->format('H.i') : '-' }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data
                                </p>
                            @endforelse

                            @if($cookingLastCheck)
                                <div class="pipeline-footer">
                                    <span>Last QC Check {{ $cookingLastCheck->format('H.i') }}</span>
                                    <a href="#" class="pipeline-view-link">View Details <i class="bi bi-caret-right-fill"></i></a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="pipeline-stage">
                        <div class="pipeline-header pipeline-header-purple">
                            <div>
                                <i class="bi bi-droplet-half"></i> PASTEURIZATION
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Packing <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="pipeline-card">
                            <div class="pipeline-card-title">Products Running</div>

                            @forelse($pasteurizingStatuses as $index => $item)
                                <div class="pipeline-item">
                                    <div class="pipeline-item-info">
                                        <div class="pipeline-item-name">{{ $item->product_name }}</div>
                                        <div class="pipeline-item-batch">Batch {{ $item->production_code }}</div>
                                    </div>
                                    <div class="pipeline-item-status">
                                        @include('partials.pipeline-item-status', ['status' => $batchTracking[$index]->pasteurization])
                                        <span class="pipeline-item-time">{{ $item->created_at ? $item->created_at->format('H.i') : '-' }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data
                                </p>
                            @endforelse

                            @if($pasteurizingLastCheck)
                                <div class="pipeline-footer">
                                    <span>Last QC Check {{ $pasteurizingLastCheck->format('H.i') }}</span>
                                    <a href="#" class="pipeline-view-link">View Details <i class="bi bi-caret-right-fill"></i></a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="pipeline-stage">
                        <div class="pipeline-header pipeline-header-teal">
                            <div>
                                <i class="bi bi-box-seam-fill"></i> PACKING
                            </div>
                            <span class="pipeline-next-chip">
                                Next: Cartoning <i class="bi bi-caret-right-fill"></i>
                            </span>
                        </div>

                        <div class="pipeline-card">
                            <div class="pipeline-card-title">Products Running</div>

                            @forelse($packingStatuses as $index => $item)
                                <div class="pipeline-item">
                                    <div class="pipeline-item-info">
                                        <div class="pipeline-item-name">{{ $item->product_name }}</div>
                                        <div class="pipeline-item-batch">Batch {{ $item->production_code }}</div>
                                    </div>
                                    <div class="pipeline-item-status">
                                        @include('partials.pipeline-item-status', ['status' => $batchTracking[$index]->packing])
                                        <span class="pipeline-item-time">{{ $item->created_at ? $item->created_at->format('H.i') : '-' }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data
                                </p>
                            @endforelse

                            @if($packingLastCheck)
                                <div class="pipeline-footer">
                                    <span>Last QC Check {{ $packingLastCheck->format('H.i') }}</span>
                                    <a href="#" class="pipeline-view-link">View Details <i class="bi bi-caret-right-fill"></i></a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="pipeline-stage">
                        <div class="pipeline-header pipeline-header-blue">
                            <div>
                                <i class="bi bi-box2-fill"></i> CARTONING
                            </div>
                        </div>

                        <div class="pipeline-card">
                            <div class="pipeline-card-title">Products Running</div>

                            @forelse($cartoningStatuses as $index => $item)
                                <div class="pipeline-item">
                                    <div class="pipeline-item-info">
                                        <div class="pipeline-item-name">{{ $item->product_name }}</div>
                                        <div class="pipeline-item-batch">Batch {{ $item->production_code }}</div>
                                    </div>
                                    <div class="pipeline-item-status">
                                        @include('partials.pipeline-item-status', ['status' => $batchTracking[$index]->cartoning])
                                        <span class="pipeline-item-time">{{ $item->created_at ? $item->created_at->format('H.i') : '-' }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="processing-empty">
                                    <i class="bi bi-moon-stars"></i> Belum ada data
                                </p>
                            @endforelse

                            @if($cartoningLastCheck)
                                <div class="pipeline-footer">
                                    <span>Last QC Check {{ $cartoningLastCheck->format('H.i') }}</span>
                                    <a href="#" class="pipeline-view-link">View Details <i class="bi bi-caret-right-fill"></i></a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="journey-container">
                <div class="journey-flex">

                    {{-- Kiri: Tabel Batch Tracking --}}
                    <div class="tracking-wrapper">
                        <div class="tracking-header">
                            <strong>PRODUCT JOURNEY</strong> <span class="tracking-subtitle">(Batch Tracking)</span>
                        </div>

                        <div class="table-responsive">
                            <table class="tracking-table">
                                <thead>
                                    <tr>
                                        <th>Batch No.</th>
                                        <th>Product</th>
                                        <th><i class="bi bi-egg-fried"></i> Meat Preparation</th>
                                        <th><i class="bi bi-egg-fried"></i> Stuffing</th>
                                        <th><i class="bi bi-fire"></i> Cooking</th>
                                        <th><i class="bi bi-droplet-half"></i> Pasteurization</th>
                                        <th><i class="bi bi-box-seam-fill"></i> Packing</th>
                                        <th><i class="bi bi-box2-fill"></i> Cartoning</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batchTracking as $row)
                                        <tr>
                                            <td class="tracking-batch">{{ $row->batch_no }}</td>
                                            <td class="tracking-product">{{ $row->product_name }}</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->meat_preparation, 'time' => $row->meat_preparation_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->stuffing, 'time' => $row->stuffing_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->cooking, 'time' => $row->cooking_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->pasteurization, 'time' => $row->pasteurization_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->packing, 'time' => $row->packing_time])</td>
                                            <td>@include('partials.tracking-status', ['status' => $row->cartoning, 'time' => $row->cartoning_time])</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada data batch</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Kanan: Overall Production Status (donut Grafana) --}}
                    <div class="overall-status-wrapper">
                        <iframe
                            src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=7&var-plant={{ urlencode($plant) }}&var-date={{ $date }}&var-product={{ urlencode($product) }}&timezone=UTC"
                            width="100%" height="260" frameborder="0" loading="lazy">
                        </iframe>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endhasanyrole

    <div class="process-status-container mb-5">
        <div class="process-status-banner">
            <span class="process-status-number">2</span>
            <span class="process-status-title">NON-PROCESS VERIFICATION</span>
            <span class="process-status-subtitle">Area condition monitoring, & routine QC Schedule (static)</span>
        </div>

        <div class="process-status-body">
            <div class="verification-grid mb-5">

                <div class="verification-card">
                    <div class="verification-header">
                        <i class="bi bi-thermometer-half"></i>
                        <strong>AREA TEMPERATURE & CONDITION</strong>
                    </div>

                    <div class="table-responsive">
                        <table class="tracking-table">
                            <thead>
                                <tr>
                                    <th>Area</th>
                                    <th>Temperature (°C)</th>
                                    <th>Standard</th>
                                    <th>Area Condition</th>
                                    <th>Last Check</th>
                                    <th>Next Check</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($areaConditions as $row)
                                <tr>
                                    <td>
                                        <span class="status-dot {{ $row->is_temp_ok === false || $row->is_condition_ok === false ? 'dot-warning' : 'dot-ok' }}"></span>
                                        {{ $row->area }}
                                    </td>
                                    <td>{{ $row->temperature !== null ? number_format($row->temperature, 1) : '-' }}</td>
                                    <td>{{ $row->standard_label }}</td>
                                    <td>
                                        @if($row->is_condition_ok === null)
                                            <span class="text-muted">-</span>
                                        @elseif($row->is_condition_ok)
                                            <span class="tracking-status tracking-completed">
                                                <i class="bi bi-check-circle-fill"></i> OK
                                            </span>
                                        @else
                                            <span class="tracking-status tracking-finding">
                                                <i class="bi bi-exclamation-circle-fill"></i> Finding
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $row->last_check ?? '-' }}</td>
                                    <td>{{ $row->next_check }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="verification-card">
                    <div class="verification-header">
                        <i class="bi bi-clipboard2-check"></i>
                        <strong>AREA CONDITION CHECKLIST</strong>
                    </div>

                    <div class="table-responsive">
                        <table class="tracking-table checklist-table">
                            <thead>
                                <tr>
                                    <th>Area</th>
                                    @foreach($checklistColumns as $column)
                                        <th>{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($checklistData as $row)
                                <tr>
                                    <td>
                                        <span class="status-dot {{ $row->has_ng ? 'dot-danger' : 'dot-ok' }}"></span>
                                        {{ $row->area }}
                                    </td>
                                    @foreach($checklistColumns as $column)
                                        <td class="text-center">
                                            @if($row->items[$column] === null)
                                                <span class="text-muted">-</span>
                                            @elseif($row->items[$column])
                                                <i class="bi bi-check-circle-fill checklist-ok"></i>
                                            @else
                                                <i class="bi bi-x-circle-fill checklist-ng"></i>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="checklist-summary">
                        <span>{{ $checklistTotalAreas }} Areas</span>
                        <span class="summary-ok">
                            <i class="bi bi-check-circle-fill"></i> {{ $checklistOkCount }} OK
                        </span>
                        <span class="summary-ng">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $checklistNgCount }} NG
                        </span>
                    </div>
                </div>

            </div>

            <div class="col-md-12 mb-4">
                <div class="verification-card trend-card">
                    <div class="verification-header">
                        <i class="bi bi-graph-up"></i>
                        <strong>TEMPERATURE TREND</strong>
                    </div>

                    <div class="trend-area-select mb-2">
                        <label class="filter-label mb-1">Area</label>
                        <select class="form-select filter-input" id="trendRoomSelect">
                            @foreach($trendRoomConfig as $roomName => $config)
                            <option value="{{ $roomName }}" @if($loop->first) selected @endif>{{ $roomName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="trend-chart-wrapper">
                        <iframe
                            id="trendTempIframe"
                            src="http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang?orgId=1&theme=light&panelId=9&timezone=Asia%2FJakarta&var-plant={{ urlencode($plant) }}&var-date={{ urlencode($date) }}&showCategory=Legend"
                            width="100%" height="260" frameborder="0" loading="lazy">
                        </iframe>
                    </div>

                    <div class="trend-summary" id="trendSummaryBox">
                        {{-- diisi otomatis oleh JS --}}
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

<script>
(function () {
    const coreTempData = @json($coreTempByMachineStep);
    const machines = @json($machines);

    const machineLabel = document.getElementById('coreTempMachineLabel');
    const content = document.getElementById('coreTempContent');
    const stepSelect = document.getElementById('coreTempStepSelect');

    let currentMachine = machines[0];
    let currentStep = stepSelect.value;

    function render() {
        machineLabel.textContent = '- ' + currentMachine;

        const data = (coreTempData[currentMachine] && coreTempData[currentMachine][currentStep])
            ? coreTempData[currentMachine][currentStep]
            : { total: 0, ok: 0, not_ok: 0, ng_percent: 0 };

        content.innerHTML = `
            <div class="incoming-main">${currentStep} ${data.total}x</div>
            <div class="incoming-detail">
                <span class="incoming-ok">${data.ok} OK</span>
                <span class="incoming-sep">;</span>
                <span class="incoming-notok">${data.not_ok} Tidak OK</span>
            </div>
            <div class="incoming-ng">
                <i class="bi bi-caret-right-fill"></i> NG ${data.ng_percent}%
            </div>
        `;
    }

    // Ganti pill click listener jadi select change listener
    stepSelect.addEventListener('change', function () {
        currentStep = stepSelect.value;
        render();
    });

    // Sinkron dengan carousel mesin di atas (tetap sama)
    const carouselEl = document.getElementById('smokehouseCarousel');
    carouselEl.addEventListener('slide.bs.carousel', function (event) {
        currentMachine = machines[event.to];
        render();
    });

    render();
})();
</script>

<script>
(function () {
    const roomConfig = @json($trendRoomConfig);
    const select = document.getElementById('trendRoomSelect');
    const iframe = document.getElementById('trendTempIframe');
    const summaryBox = document.getElementById('trendSummaryBox');
    const baseUrl = "http://10.68.8.205:3000/d-solo/ad4x5hj/suhu-ruang";
    const trendFrom = {{ $trendFrom }};
    const trendTo = {{ $trendTo }};
    const staticParams = "orgId=1&theme=light&panelId=9&timezone=Asia%2FJakarta"
        + "&from=" + trendFrom + "&to=" + trendTo
        + "&var-plant={{ urlencode($plant) }}"
        + "&var-date={{ urlencode($date) }}"
        + "&showCategory=Legend";

    function render() {
        const roomName = select.value;
        const config = roomConfig[roomName];
        if (!config) return;

        iframe.src = baseUrl + '?' + staticParams
            + '&var-room=' + encodeURIComponent(roomName)
            + '&var-db_room=' + encodeURIComponent(config.db_room)
            + '&var-source=' + encodeURIComponent(config.source)
            + '&var-standard=' + encodeURIComponent(config.standard_value);

        const withinBadge = config.within_standard === null
            ? '<span class="trend-badge trend-badge-muted"><i class="bi bi-dash-circle"></i> Belum Ada Data</span>'
            : config.within_standard
                ? '<span class="trend-badge trend-badge-ok"><i class="bi bi-check-square-fill"></i> Within Standard</span>'
                : '<span class="trend-badge trend-badge-ng"><i class="bi bi-x-square-fill"></i> Out of Standard</span>';

        summaryBox.innerHTML = `
            <div class="trend-stat">
                <div class="trend-stat-label">Average</div>
                <div class="trend-stat-value">${config.average ?? '-'}<span class="trend-unit">°C</span></div>
            </div>
            <div class="trend-stat">
                <div class="trend-stat-label">Min</div>
                <div class="trend-stat-value trend-stat-min">${config.min ?? '-'}<span class="trend-unit">°C</span></div>
            </div>
            <div class="trend-stat">
                <div class="trend-stat-label">Max</div>
                <div class="trend-stat-value trend-stat-max">${config.max ?? '-'}<span class="trend-unit">°C</span></div>
            </div>
            <div class="trend-badge-wrap">${withinBadge}</div>
        `;
    }

    select.addEventListener('change', render);
    render();
})();
</script>
@endsection