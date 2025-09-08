@php
$isMasterData =
Request::is('users*','area*','section*','rooms*','fragile-item*','sharp-tools*','scales*','thermometers*','products*','raw-material*','premixes*','formulas*',
'standard-stuffers*', 'maurer-standards*', 'fessman-standards*');
$isAccessControl = Request::is('roles*') || Request::is('permissions*');
$isMeatPrep =
Request::is('report-rm-arrivals*') ||
Request::is('report-premixes*') ||
Request::is('report-metal-detectors*') ||
Request::is('report-weight-stuffers*') ||
Request::is('report-emulsion-makings*') ||
Request::is('report-process-productions*');
$isCooking =
Request::is('report-maurer-cookings*') ||
Request::is('report-fessman-cookings*');
$isPacking =
Request::is('report-lab-samples*') ||
Request::is('report-md-products*') ||
Request::is('report-retain-samples*') ||
Request::is('report-product-verifs*') ||
Request::is('report-tofu-verifs*') ||
Request::is('report-prod-loss-vacums*') ||
Request::is('report-packaging-verifs*');
$isCartoning =
Request::is('report-freez-packagings*');
$isNonProses = Request::is([
'report-re-cleanliness*',
'storage-rm-cleanliness*',
'process-area-cleanliness*',
'report-conveyor-cleanliness*',
'gmp-employee*',
'report-chlorine-residues*',
'report-fragile-item*',
'report-scales*',
'report-sharp-tools*'
]);
$isKetidaksesuaian = Request::is([
'report-production-nonconformities*',
'report-foreign-objects*'
]);
@endphp

<style>
.collapse-item {
    white-space: normal !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    margin-bottom: .2rem !important;
}

.collapse-inner {
    overflow: hidden !important;
    padding: 0.5rem !important;
}

#sidebarSuggestions {
    position: absolute;
    width: 19%;
    z-index: 9999;
    background: white;
    border: 1px solid #ddd;
    border-radius: 0.25rem;
}
</style>

<!-- Sidebar -->
<ul class="navbar-nav bg-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard">
        <div class="sidebar-brand-icon rotate-n-15">
            {{-- <i class="fas fa-laugh-wink"></i> --}}
        </div>
        <div class="sidebar-brand-text mx-3">Paperless SP</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Database
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    @can('view master data')
    <li class="nav-item {{ $isMasterData ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="{{ $isMasterData ? 'true' : 'false' }}" aria-controls="collapseTwo">
            <i class="fas fa-database"></i>
            <span>Master data</span>
        </a>
        <div id="collapseTwo" class="collapse {{ $isMasterData ? 'show' : '' }}" aria-labelledby="headingTwo"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                @can('user view')
                <a class="collapse-item {{ Request::is('users*') ? 'active' : '' }}"
                    href="{{ route('users.index') }}">User</a>
                @endcan
                <a class="collapse-item {{ Request::is('area*') ? 'active' : '' }}"
                    href="{{ route('areas.index') }}">Area</a>
                <a class="collapse-item {{ Request::is('section*') ? 'active' : '' }}"
                    href="{{ route('sections.index') }}">Section</a>
                <a class="collapse-item {{ Request::is('rooms*') ? 'active' : '' }}"
                    href="{{ route('rooms.index') }}">Ruangan, Mesin, dan Peralatan</a>
                <a class="collapse-item {{ Request::is('fragile-item*') ? 'active' : '' }}"
                    href="{{ route('fragile-item.index') }}">Barang Mudah Pecah</a>
                <!-- <a class="collapse-item {{ Request::is('sharp-tools*') ? 'active' : '' }}"
                    href="{{ route('sharp_tools.index') }}">Benda Tajam</a> -->
                <!-- <a class="collapse-item" href="{{ route('qc-equipment.index') }}">Inventaris Peralatan QC</a> -->
                <a class="collapse-item {{ Request::is('scales*') ? 'active' : '' }}"
                    href="{{ route('scales.index') }}">Timbangan</a>
                <a class="collapse-item {{ Request::is('thermometers*') ? 'active' : '' }}"
                    href="{{ route('thermometers.index') }}">Thermometer</a>
                <a class="collapse-item {{ Request::is('products*') ? 'active' : '' }}"
                    href="{{ route('products.index') }}">Produk</a>
                <a class="collapse-item {{ Request::is('raw-material*') ? 'active' : '' }}"
                    href="{{ route('raw-materials.index') }}">Raw Material</a>
                <a class="collapse-item {{ Request::is('premixes*') ? 'active' : '' }}"
                    href="{{ route('premixes.index') }}">Premix</a>
                <a class="collapse-item {{ Request::is('formulas*') ? 'active' : '' }}"
                    href="{{ route('formulas.index') }}">Formulasi</a>
                <!-- <a class="collapse-item {{ Request::is('standard-stuffers*') ? 'active' : '' }}"
                    href="{{ route('standard-stuffers.index') }}">Standar Stuffer</a> -->

                <a class="collapse-item {{ Request::is('maurer-standards*') ? 'active' : '' }}"
                    href="{{ route('maurer-standards.index') }}">Standar Maurer</a>
                <a class="collapse-item {{ Request::is('fessman-standards*') ? 'active' : '' }}"
                    href="{{ route('fessman-standards.index') }}">Standar Fessman</a>
            </div>
        </div>
    </li>
    @endcan

    <!-- access control -->
    @can('user view')
    <li class="nav-item {{ $isAccessControl ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree"
            aria-expanded="{{ $isAccessControl ? 'true' : 'false' }}" aria-controls="collapseThree">
            <i class="fas fa-wrench"></i>
            <span>Access Control</span>
        </a>
        <div id="collapseThree" class="collapse {{ $isAccessControl ? 'show' : '' }}" aria-labelledby="headingTwo"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('roles*') ? 'active' : '' }}"
                    href="{{ route('roles.index') }}">Role</a>
                <a class="collapse-item {{ Request::is('permissions*') ? 'active' : '' }}"
                    href="{{ route('permissions.index') }}">Permission</a>
            </div>
        </div>
    </li>
    @endcan

    <!-- Divider -->
    <hr class="sidebar-divider">

    <div class="p-2 mb-3">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
            </div>
            <input type="text" id="sidebarSearch" class="form-control border-left-0" placeholder="Cari Menu...">
        </div>
        <div id="sidebarSuggestions" class="list-group mt-1" style="max-height:300px; overflow-y:auto; display:none;">
        </div>
    </div>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading mt-3">
        Report
    </div>

    <li class="nav-item {{ $isMeatPrep ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesMeatPrep"
            aria-expanded="{{ $isMeatPrep ? 'true' : 'false' }}" aria-controls="collapsePagesMeatPrep">
            <i class="fas fa-drumstick-bite"></i>
            <span>Meat Preparation</span>
        </a>
        <div id="collapsePagesMeatPrep" class="collapse {{ $isMeatPrep ? 'show' : '' }}" aria-labelledby="headingPages"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('report-rm-arrivals*') ? 'active' : '' }}"
                    href="{{ route('report_rm_arrivals.index') }}">
                    Pemeriksaan Kedatangan Bahan Baku Chillroom
                </a>
                <a class="collapse-item {{ Request::is('report-premixes*') ? 'active' : '' }}"
                    href="{{ route('report-premixes.index') }}">
                    Pemeriksaan Premix
                </a>
                <a class="collapse-item {{ Request::is('report-metal-detectors*') ? 'active' : '' }}"
                    href="{{ route('report_metal_detectors.index') }}">
                    Pemeriksaan Metal Detector Adonan
                </a>
                <a class="collapse-item {{ Request::is('report-weight-stuffers*') ? 'active' : '' }}"
                    href="{{ route('report_weight_stuffers.index') }}">
                    Verifikasi Berat Stuffer
                </a>
                <a class="collapse-item {{ Request::is('report-emulsion-makings*') ? 'active' : '' }}"
                    href="{{ route('report_emulsion_makings.index') }}">
                    Verifikasi Pembuatan Emulsi / CCM Block
                </a>
                <a class="collapse-item {{ Request::is('report-process-productions*') ? 'active' : '' }}"
                    href="{{ route('report_process_productions.index') }}">
                    Verifikasi Proses Produksi
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item {{ $isCooking ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesCooking"
            aria-expanded="{{ $isCooking ? 'true' : 'false' }}" aria-controls="collapsePagesCooking">
            <i class="fas fa-fire"></i>
            <span>Cooking</span>
        </a>
        <div id="collapsePagesCooking" class="collapse {{ $isCooking ? 'show' : '' }}" aria-labelledby="headingPages"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('report-maurer-cookings*') ? 'active' : '' }}"
                    href="{{ route('report_maurer_cookings.index') }}">
                    Maurer
                </a>
                <a class="collapse-item {{ Request::is('report-fessman-cookings*') ? 'active' : '' }}"
                    href="{{ route('report_fessman_cookings.index') }}">
                    Fessman
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item {{ $isPacking ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesPacking"
            aria-expanded="{{ $isPacking ? 'true' : 'false' }}" aria-controls="collapsePagesPacking">
            <i class="fas fa-box-open"></i>
            <span>Packing</span>
        </a>
        <div id="collapsePagesPacking" class="collapse {{ $isPacking ? 'show' : '' }}" aria-labelledby="headingPages"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">

                {{-- <a class="collapse-item" href="{{ route('report_repack_verifs.index') }}">Verifikasi Repack
                Produk</a> --}}

                <a class="collapse-item {{ Request::is('report-lab-samples*') ? 'active' : '' }}"
                    href="{{ route('report_lab_samples.index') }}">
                    Verifikasi Pengambilan Sample
                </a>

                {{-- <a class="collapse-item" href="{{ route('report_retains.index') }}">Retained Sample Report</a> --}}
                {{-- <a class="collapse-item" href="{{ route('report_retain_exterminations.index') }}">Pemusnahan Retain
                Sample</a> --}}

                <a class="collapse-item {{ Request::is('report-md-products*') ? 'active' : '' }}"
                    href="{{ route('report_md_products.index') }}">
                    Pemeriksaan Metal Detector Produk
                </a>

                <!-- <a class="collapse-item {{ Request::is('report-retain-samples*') ? 'active' : '' }}"
                    href="{{ route('report_retain_samples.index') }}">
                    Pendataan Retain Sample ABF/IQF
                </a> -->

                <!-- <a class="collapse-item {{ Request::is('report-product-verifs*') ? 'active' : '' }}"
                    href="{{ route('report_product_verifs.index') }}">
                    Verifikasi Produk
                </a> -->

                <a class="collapse-item {{ Request::is('report-tofu-verifs*') ? 'active' : '' }}"
                    href="{{ route('report_tofu_verifs.index') }}">
                    Verifikasi Produk Tofu
                </a>

                <!-- <a class="collapse-item {{ Request::is('report-prod-loss-vacums*') ? 'active' : '' }}"
                    href="{{ route('report_prod_loss_vacums.index') }}">
                    Verifikasi Produk Loss Vacum
                </a> -->

                <a class="collapse-item {{ Request::is('report-packaging-verifs*') ? 'active' : '' }}"
                    href="{{ route('report_packaging_verifs.index') }}">
                    Verifikasi Pemeriksaan Kemasan Plastik
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item {{ $isCartoning ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesCartoning"
            aria-expanded="{{ $isCartoning ? 'true' : 'false' }}" aria-controls="collapsePagesCartoning">
            <i class="fas fa-box"></i>
            <span>Cartoning</span>
        </a>
        <div id="collapsePagesCartoning" class="collapse {{ $isCartoning ? 'show' : '' }}"
            aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                {{-- <a class="collapse-item" href="{{ route('report_iqf_freezings.index') }}">Verifikasi Pembekuan
                IQF</a> --}}
                {{-- <a class="collapse-item" href="{{ route('report_vacuum_conditions.index') }}">Verifikasi Kondisi
                Vakum Produk Setelah IQF</a> --}}

                <a class="collapse-item {{ Request::is('report-freez-packagings*') ? 'active' : '' }}"
                    href="{{ route('report_freez_packagings.index') }}">
                    Verifikasi Pembekuan IQF dan Pengemasan Karton Box
                </a>

                {{-- <a class="collapse-item" href="{{ route('report_checkweigher_boxes.index') }}">Pemeriksaan
                Checkweigher Box</a> --}}
            </div>
        </div>
    </li>

    <li class="nav-item {{ $isNonProses ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesProdNonProd"
            aria-expanded="{{ $isNonProses ? 'true' : 'false' }}" aria-controls="collapsePagesProdNonProd">
            <i class="fas fa-clipboard-check"></i>
            <span>Verifikasi Non Proses</span>
        </a>
        <div id="collapsePagesProdNonProd" class="collapse {{ $isNonProses ? 'show' : '' }}"
            aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">

                <a class="collapse-item {{ Request::is('report-re-cleanliness*') ? 'active' : '' }}"
                    href="{{ route('report-re-cleanliness.index') }}">
                    Kebersihan Ruangan, Mesin, dan Peralatan
                </a>

                {{-- <a class="collapse-item" href="{{ route('report_pre_operations.index') }}">Pemeriksaan Pra Operasi
                Produk</a> --}}
                {{-- <a class="collapse-item" href="{{ route('report_product_changes.index') }}">Verifikasi Pergantian
                Produk</a> --}}

                <a class="collapse-item {{ Request::is('storage-rm-cleanliness*') ? 'active' : '' }}"
                    href="{{ route('cleanliness.index') }}">
                    Kebersihan Area Penyimpanan Bahan
                </a>

                <a class="collapse-item {{ Request::is('process-area-cleanliness*') ? 'active' : '' }}"
                    href="{{ route('process-area-cleanliness.index') }}">
                    Kebersihan Area Proses
                </a>

                <!-- <a class="collapse-item {{ Request::is('report-conveyor-cleanliness*') ? 'active' : '' }}"
                    href="{{ route('report-conveyor-cleanliness.index') }}">
                    Kebersihan Conveyor Packing
                </a> -->

                {{-- <a class="collapse-item" href="{{ route('repair-cleanliness.index') }}">Report Pemeriksaan dan
                Sanitasi Setelah Perbaikan Mesin</a> --}}

                <a class="collapse-item {{ Request::is('gmp-employee*') ? 'active' : '' }}"
                    href="{{ route('gmp-employee.index') }}">
                    GMP karyawan & Kontrol Sanitasi
                </a>

                <!-- <a class="collapse-item {{ Request::is('report-chlorine-residues*') ? 'active' : '' }}"
                    href="{{ route('report_chlorine_residues.index') }}">
                    Air Proses Produksi
                </a> -->

                {{-- <a class="collapse-item" href="{{ route('report-solvents.index') }}">Report Pembuatan Larutan
                Cleaning dan Sanitasi</a> --}}

                <a class="collapse-item {{ Request::is('report-fragile-item*') ? 'active' : '' }}"
                    href="{{ route('report-fragile-item.index') }}">
                    Barang Mudah Pecah
                </a>

                <a class="collapse-item {{ Request::is('report-scales*') ? 'active' : '' }}"
                    href="{{ route('report-scales.index') }}">
                    Timbangan & Thermometer
                </a>

                {{-- <a class="collapse-item" href="{{ route('report-qc-equipment.index') }}">Report Inventaris
                Peralatan QC</a> --}}

                <!-- <a class="collapse-item {{ Request::is('report-sharp-tools*') ? 'active' : '' }}"
                    href="{{ route('report_sharp_tools.index') }}">
                    Benda Tajam
                </a> -->
            </div>
        </div>
    </li>

    <li class="nav-item {{ $isKetidaksesuaian ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesketidaksesuaian"
            aria-expanded="{{ $isKetidaksesuaian ? 'true' : 'false' }}" aria-controls="collapsePagesketidaksesuaian">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Verifikasi dan Penanganan Ketidaksesuaian</span>
        </a>
        <div id="collapsePagesketidaksesuaian" class="collapse {{ $isKetidaksesuaian ? 'show' : '' }}"
            aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('report-production-nonconformities*') ? 'active' : '' }}"
                    href="{{ route('report_production_nonconformities.index') }}">
                    Pemeriksaan Ketidaksesuaian Proses Produksi
                </a>

                <a class="collapse-item {{ Request::is('report-foreign-objects*') ? 'active' : '' }}"
                    href="{{ route('report-foreign-objects.index') }}">
                    Pemeriksaan Kontaminasi Benda Asing
                </a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('sidebarSearch');
    const suggestionsBox = document.getElementById('sidebarSuggestions');

    // Ambil semua link menu sidebar yang ingin dicari
    const menuItems = Array.from(document.querySelectorAll('.collapse-item'));

    searchInput.addEventListener('input', function() {
        const keyword = this.value.toLowerCase().trim();
        suggestionsBox.innerHTML = '';

        if (keyword.length === 0) {
            suggestionsBox.style.display = 'none';
            return;
        }

        // Filter menu berdasarkan keyword
        const filtered = menuItems.filter(item => item.textContent.toLowerCase().includes(keyword));

        if (filtered.length === 0) {
            suggestionsBox.style.display = 'none';
            return;
        }

        // Buat suggestion
        filtered.forEach(item => {
            const suggestion = document.createElement('a');
            suggestion.href = item.href;
            suggestion.className = 'list-group-item list-group-item-action';
            suggestion.textContent = item.textContent.trim();
            suggestionsBox.appendChild(suggestion);
        });

        suggestionsBox.style.display = 'block';
    });

    // Opsional: hide suggestions saat klik di luar
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
});
</script>