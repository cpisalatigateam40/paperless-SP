<style>
.topbar-info-item {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 0 14px;
    border-right: 1px solid #e9ecef;
}

.topbar-info-item i {
    font-size: 1rem;
    color: #868e96;
}

.info-label {
    font-size: 0.6rem;
    color: #adb5bd;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    line-height: 1.1;
}

.info-value {
    font-size: 0.8rem;
    font-weight: 700;
    color: #343a40;
    line-height: 1.2;
}
</style>

<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto align-items-center">

        {{-- Info items --}}
        <li class="nav-item d-none d-md-flex topbar-info-item">
            <i class="bi bi-calendar3"></i>
            <div>
                <div class="info-label">Date</div>
                <div class="info-value">{{ now()->translatedFormat('d M Y') }}</div>
            </div>
        </li>

        <li class="nav-item d-none d-md-flex topbar-info-item">
            <i class="bi bi-arrow-repeat"></i>
            <div>
                <div class="info-label">Shift</div>
                <div class="info-value">{{ session('shift_number') ?? '-' }}</div>
            </div>
        </li>

        <li class="nav-item d-none d-md-flex topbar-info-item">
            <i class="bi bi-clock"></i>
            <div>
                <div class="info-label">Current Time</div>
                <div class="info-value" id="topbarCurrentTime">--:--</div>
            </div>
        </li>

        <li class="nav-item d-none d-md-flex topbar-info-item">
            <i class="bi bi-person-badge"></i>
            <div>
                <div class="info-label">Role</div>
                <div class="info-value">{{ Auth::user()->getRoleNames()->first() ?? '-' }}</div>
            </div>
        </li>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                <i class="fas fa-user" style="margin-top: -.2rem;"></i>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Logout
                    </button>
                </form>
            </div>
        </li>

    </ul>

</nav>

<script>
(function () {
    const timeEl = document.getElementById('topbarCurrentTime');

    function updateTime() {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        timeEl.textContent = `${hh}:${mm}`;
    }

    updateTime();
    setInterval(updateTime, 1000 * 30);
})();
</script>