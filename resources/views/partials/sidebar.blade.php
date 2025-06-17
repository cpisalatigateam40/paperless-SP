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
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-database"></i>
            <span>Master data</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="soft-salmon py-2 collapse-inner rounded">
                @can('user view')
                    <a class="collapse-item" href="{{ route('users.index') }}">User</a>
                @endcan
                <a class="collapse-item" href="{{ route('areas.index') }}">Area</a>
                {{-- <a class="collapse-item" href="{{ route('sections.index') }}">Section</a> --}}
                <a class="collapse-item" href="{{ route('raw-materials.index') }}">Raw Material</a>
                <a class="collapse-item" href="{{ route('fragile-item.index') }}">Fragile Item</a>
                <a class="collapse-item" href="{{ route('qc-equipment.index') }}">Inventaris Peralatan QC</a>
                <a class="collapse-item" href="{{ route('scales.index') }}">Timbangan</a>
                <a class="collapse-item" href="{{ route('thermometers.index') }}">Thermometer</a>
                <a class="collapse-item" href="{{ route('rooms.index') }}">Ruangan, Mesin, dan Peralatan</a>
            </div>
        </div>
    </li>

    <!-- access control -->
    @can('user view')
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree"
                aria-expanded="true" aria-controls="collapseTwo">
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

    <!-- Heading -->
    <div class="sidebar-heading">
        Report
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-recycle"></i>
            <span>Pemeriksaan Kondisi Area, GMP Karyawan, dan Kontrol Sanitasi</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
           <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('cleanliness.index') }}">
                    Report kebersihan area penyimpanan bahan
                </a>

                <a class="collapse-item" href="{{ route('process-area-cleanliness.index') }}">
                    Report kebersihan area proses
                </a>

                <a class="collapse-item" href="{{ route('gmp-employee.index') }}">
                    Report GMP karyawan & Kontrol Sanitasi
                </a>

                <a class="collapse-item" href="{{ route('report-fragile-item.index') }}">
                    Report Barang Mudah Pecah
                </a>

                <a class="collapse-item" href="{{ route('report-re-cleanliness.index') }}">
                    Report Verifikasi Kebersihan Ruangan, Mesin, dan Peralatan
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagesQc"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Pemeriksaan Peralatan QC</span>
        </a>
        <div id="collapsePagesQc" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
           <div class="soft-salmon py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('report-qc-equipment.index') }}">
                    Report Inventaris QC
                </a>

                <a class="collapse-item" href="{{ route('report-scales.index') }}">
                    Report Timbangan & Thermometer
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