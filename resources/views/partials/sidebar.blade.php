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
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-database"></i>
            <span>Master data</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                @can('user view')
                <a class="collapse-item" href="{{ route('users.index') }}">User</a>
                @endcan
                <a class="collapse-item" href="{{ route('areas.index') }}">Area</a>
                <a class="collapse-item" href="{{ route('sections.index') }}">Section</a>
                <a class="collapse-item" href="{{ route('raw-materials.index') }}">Raw Material</a>
                <a class="collapse-item" href="{{ route('fragile-item.index') }}">Fragile Item</a>
                <a class="collapse-item" href="{{ route('qc-equipment.index') }}">Inventaris Peralatan QC</a>
                <a class="collapse-item" href="{{ route('scales.index') }}">Timbangan</a>
                <a class="collapse-item" href="{{ route('thermometers.index') }}">Thermometer</a>
                <a class="collapse-item" href="{{ route('rooms.index') }}">Ruangan, Mesin, dan Peralatan</a>
                <a class="collapse-item" href="{{ route('products.index') }}">Produk</a>
                <a class="collapse-item" href="{{ route('premixes.index') }}">
                    Premix
                </a>
                <a class="collapse-item" href="{{ route('sharp_tools.index') }}">
                    Benda Tajam
                </a>
            </div>
        </div>
    </li>

    <!-- access control -->
    @can('user view')
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-wrench"></i>
            <span>Access Control</span>
        </a>
        <div id="collapseThree" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('roles.index') }}">Role</a>
                <a class="collapse-item" href="{{ route('permissions.index') }}">Permission</a>
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

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesMeatPrep"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-drumstick-bite"></i>
            <span>Meat Preparation</span>
        </a>
        <div id="collapsePagesMeatPrep" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('report_rm_arrivals.index') }}">
                    Pemeriksaan Kedatangan Bahan Baku Chillroom
                </a>

                <a class="collapse-item" href="{{ route('report-premixes.index') }}">
                    Pemeriksaan Premix
                </a>

                <a class="collapse-item" href="{{ route('report-foreign-objects.index') }}">
                    Pemeriksaan Kontaminasi Benda Asing
                </a>

                <a class="collapse-item" href="{{ route('report_returns.index') }}">
                    Retur Ketidaksesuaian Bahan Baku / Bahan Kemas
                </a>

                <a class="collapse-item" href="{{ route('report_metal_detectors.index') }}">
                    Pemeriksaan Metal Detector Adonan
                </a>

                <a class="collapse-item" href="{{ route('report_magnet_traps.index') }}">
                    Pemeriksaan Magnet Trap
                </a>

                <a class="collapse-item" href="{{ route('report_stuffers.index') }}">
                    Rekap Stuffer dan Cooking Loss
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesCooking"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fire"></i>
            <span>Cooking</span>
        </a>
        <div id="collapsePagesCooking" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('report_maurer_cookings.index') }}">
                    Pemeriksaan Pemasakan Rumah Asap, Showering, dan Cooling Down Maurer
                </a>

                <a class="collapse-item" href="{{ route('report_fessman_cookings.index') }}">
                    Pemeriksaan Pemasakan Rumah Asap, Showering, dan Cooling Down Fessman
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesPacking"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-box-open"></i>
            <span>Packing</span>
        </a>
        <div id="collapsePagesPacking" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('report_repack_verifs.index') }}">
                    Verifikasi Repack Produk
                </a>

                <a class="collapse-item" href="{{ route('report_lab_samples.index') }}">
                    Pembuatan Sample Laboratorium
                </a>

                <a class="collapse-item" href="{{ route('report_retains.index') }}">
                    Retained Sample Report
                </a>

                <a class="collapse-item" href="{{ route('report_retain_exterminations.index') }}">
                    Pemusnahan Retain Sample
                </a>

                <a class="collapse-item" href="{{ route('report_md_products.index') }}">
                    Pemeriksaan Metal Detector Produk
                </a>

                <a class="collapse-item" href="{{ route('report_retain_samples.index') }}">
                    Pendataan Retain Sample ABF/IQF
                </a>

                <a class="collapse-item" href="{{ route('report_product_verifs.index') }}">
                    Verifikasi Produk
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesCartoning"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-box"></i>
            <span>Cartoning</span>
        </a>
        <div id="collapsePagesCartoning" class="collapse" aria-labelledby="headingPages"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('report_iqf_freezings.index') }}">
                    Verifikasi Pembekuan IQF
                </a>

                <a class="collapse-item" href="{{ route('report_vacuum_conditions.index') }}">
                    Verifikasi Kondisi Vakum Produk Setelah IQF
                </a>

                <a class="collapse-item" href="{{ route('report_freez_packagings.index') }}">
                    Verifikasi Pembekuan IQF dan Pengemasan Karton Box
                </a>

                <a class="collapse-item" href="{{ route('report_checkweigher_boxes.index') }}">
                    Pemeriksaan Checkweigher Box
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesProdNonProd"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-clipboard-check"></i>
            <span>Verifikasi Non Proses</span>
        </a>
        <div id="collapsePagesProdNonProd" class="collapse" aria-labelledby="headingPages"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('report-re-cleanliness.index') }}">
                    Report Verifikasi Kebersihan Ruangan, Mesin, dan Peralatan
                </a>

                <a class="collapse-item" href="{{ route('report_pre_operations.index') }}">
                    Pemeriksaan Pra Operasi Produk
                </a>

                <a class="collapse-item" href="{{ route('report_product_changes.index') }}">
                    Verifikasi Pergantian Produk
                </a>

                <a class="collapse-item" href="{{ route('cleanliness.index') }}">
                    Report kebersihan area penyimpanan bahan
                </a>

                <a class="collapse-item" href="{{ route('process-area-cleanliness.index') }}">
                    Report kebersihan area proses
                </a>

                <a class="collapse-item" href="{{ route('report-conveyor-cleanliness.index') }}">
                    Report Pemeriksaan Kebersihan Conveyor Packing
                </a>

                <a class="collapse-item" href="{{ route('repair-cleanliness.index') }}">
                    Report Pemeriksaan dan Sanitasi Setelah Perbaikan Mesin
                </a>

                <a class="collapse-item" href="{{ route('gmp-employee.index') }}">
                    Report GMP karyawan & Kontrol Sanitasi
                </a>

                <a class="collapse-item" href="{{ route('report_chlorine_residues.index') }}">
                    Pemeriksaan Air Proses Produksi
                </a>

                <a class="collapse-item" href="{{ route('report-solvents.index') }}">
                    Report Pembuatan Larutan Cleaning dan Sanitasi
                </a>

                <a class="collapse-item" href="{{ route('report-fragile-item.index') }}">
                    Pemeriksaan Barang Mudah Pecah
                </a>

                <a class="collapse-item" href="{{ route('report-scales.index') }}">
                    Pemeriksaan Timbangan & Thermometer
                </a>

                <a class="collapse-item" href="{{ route('report-qc-equipment.index') }}">
                    Report Inventaris Peralatan QC
                </a>

                <a class="collapse-item" href="{{ route('report_sharp_tools.index') }}">
                    Report Benda Tajam
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesketidaksesuaian"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Verifikasi dan Penanganan Ketidaksesuaian</span>
        </a>
        <div id="collapsePagesketidaksesuaian" class="collapse" aria-labelledby="headingPages"
            data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('report_production_nonconformities.index') }}">
                    Pemeriksaan Ketidaksesuaian Proses Produksi
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