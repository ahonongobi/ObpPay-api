<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>ObpPay Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
:root {
    --indigo: #4f46e5;
    --indigo-dark: #4338ca;
    --gray-dark: #1f2937;
    --gray-medium: #374151;
}

/* GLOBAL LIGHT DEFAULT */
body {
    background-color: #f3f4f6;
    font-family: 'Inter', sans-serif;
}

/* DARK MODE */
body.dark-mode {
    background-color: #111827 !important;
    color: #f3f4f6 !important;
}

/* SIDEBAR */
.sidebar {
    width: 270px;
    height: 100vh;
    background-color: var(--indigo);
    padding: 25px;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
}

body.dark-mode .sidebar {
    background-color: var(--gray-dark);
}

/* LINKS */
.sidebar a {
    display: block;
    padding: 12px 18px;
    border-radius: 12px;
    color: #e0e7ff;
    margin-bottom: 10px;
    font-size: 1.05rem;
    text-decoration: none;
    transition: background 0.2s;
}

.sidebar a.active {
    background-color: rgba(255,255,255,0.2);
    font-weight: bold;
}

.sidebar a:hover {
    background-color: rgba(255,255,255,0.15);
}

/* MAIN CONTENT */
.main-content {
    margin-left: 270px;
    padding: 35px;
}

/* ---- TABLE STYLING ---- */

/* Container */
.table-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 20px;
    border: 1px solid #e6e6e6;
}

/* Dark */
body.dark-mode .table-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.05);
}

/* Table wrapper */
.table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

/* Table header */
.table-modern thead tr th {
    background: transparent;
    font-weight: 600;
    padding: 10px 15px;
    color: #555;
}

/* DARK HEADERS */
body.dark-mode .table-modern thead tr th {
    color: #9ca3af;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

/* Table rows */
.table-modern tbody tr {
    background: #f8f9fa;
    transition: 0.2s;
    border-radius: 12px;
}

.table-modern tbody tr:hover {
    background: #eef1f4;
}

/* DARK ROWS */
body.dark-mode .table-modern tbody tr {
    background: rgba(255,255,255,0.03);
}

body.dark-mode .table-modern tbody tr:hover {
    background: rgba(255,255,255,0.08);
}

/* Table cells */
.table-modern tbody td {
    padding: 16px;
    color: #333;
}

/* DARK CELL TEXT */
body.dark-mode .table-modern tbody td {
    color: #e5e7eb;
}

/* Badges */
.badge-modern {
    border-radius: 10px;
    font-size: 12px;
    padding: 6px 10px;
}

.badge-pending { background: #ffca2c; color: #332a00; }
.badge-none { background: #6c757d; }
.badge-approved { background: #198754; }

/* SEARCH BOX */
.search-box {
    background: #ffffff;
    border: 1px solid #ddd;
    color: #333;
}

body.dark-mode .search-box {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
}


/* ===== MODAL DARK MODE ===== */

body.dark-mode .modal-content {
    background-color: #1f2937 !important; /* gris foncé */
    color: #f3f4f6 !important; /* texte white-soft */
    border: 1px solid rgba(255, 255, 255, 0.1);
}

body.dark-mode .modal-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

body.dark-mode .modal-footer {
    border-top: 1px solid rgba(255,255,255,0.1);
}

body.dark-mode .modal-title {
    color: #f3f4f6 !important;
}

body.dark-mode .btn-close {
    filter: invert(1) grayscale(100%); /* close button visible */
}

/* Text inside modal */
body.dark-mode #modalContent h4,
body.dark-mode #modalContent h5,
body.dark-mode #modalContent h6,
body.dark-mode #modalContent p,
body.dark-mode #modalContent strong {
    color: #e5e7eb !important;
}

/* Optional: image shadow more visible in dark mode */
body.dark-mode #modalContent img {
    box-shadow: 0 0 10px rgba(255,255,255,0.08);
}

</style>

<style>

/* STAT CARDS BASE */
.stat-card {
    border-radius: 20px;
    padding: 25px;
    border: none;
    transition: 0.2s;
    color: white;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* 🔵 USERS (BLUE / INDIGO) */
.stat-blue {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
}
body.dark-mode .stat-blue {
    background: linear-gradient(135deg, #3730a3, #4f46e5);
}

/* 🟡 KYC PENDING (YELLOW) */
.stat-yellow {
    background: linear-gradient(135deg, #facc15, #eab308);
}
body.dark-mode .stat-yellow {
    background: linear-gradient(135deg, #a16207, #ca8a04);
}

/* 🟢 APPROVED (GREEN) */
.stat-green {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}
body.dark-mode .stat-green {
    background: linear-gradient(135deg, #15803d, #166534);
}

/* TEXT FIXES */
.stat-card h6 {
    opacity: 0.9;
    font-weight: 500;
}

.stat-card h2 {
    font-size: 2.6rem;
    font-weight: 800;
}

</style>



    <script>
        document.addEventListener("DOMContentLoaded", () => {
            if (localStorage.theme === "dark") {
                document.body.classList.add("dark-mode");
                toggleIcon();
            }
        });

        function toggleTheme() {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.theme = "dark";
            } else {
                localStorage.theme = "light";
            }

            toggleIcon();
        }

        function toggleIcon() {
            const icon = document.getElementById("themeIcon");
            if (document.body.classList.contains("dark-mode")) {
                icon.classList.replace("bi-moon", "bi-sun");
            } else {
                icon.classList.replace("bi-sun", "bi-moon");
            }
        }
    </script>



</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar shadow-lg">
        <h3 class="fw-bold mb-4">ObpPay Admin</h3>

        <a href="{{ route('admin.dashboard') }}"
           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill me-2"></i> Dashboard
        </a>
        @if(auth()->user()->role === 'superadmin')
    <a href="{{ route('admin.settings.admins') }}"
    class="{{ request()->routeIs('admin.settings.admins') ? 'active' : '' }}">
        <i class="bi bi-people-fill me-2"></i> Gestion admins
        </a>
         @endif

        


         <a href="{{ route('admin.users.index') }}"
           class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
            <i class="bi bi-people me-2"></i> Utilisateurs
        </a>


        <a href="{{ route('admin.kyc.index') }}"
           class="{{ request()->routeIs('admin.kyc.index') ? 'active' : '' }}">
            <i class="bi bi-person-vcard me-2"></i> Validation KYC
        </a>
        <a href="{{ route('admin.transactions.index') }}"
        class="{{ request()->routeIs('admin.transactions.index') ? 'active' : '' }}">
            <i class="bi bi-cash-stack me-2"></i> Transactions
        </a>

        {{-- withdraw menu --}}

        <a href="{{ route('admin.withdrawals.index') }}"
           class="{{ request()->routeIs('admin.withdrawals.index') ? 'active' : '' }}">
            <i class="bi bi-wallet2 me-2"></i> Retraits Utilisateurs
        </a>

    <a href="{{ route('admin.loans.index') }}"
    class="{{ request()->routeIs('admin.loan.index') ? 'active' : '' }}">
        <i class="bi bi-hand-thumbs-up-fill me-2"></i> Aide / Prêts
        </a>

        {{-- user withdrawal, Marketplace, Settings can be added later --}}
{{--
        <a href="{{route('admin.withdrawals.index')}}"
              class="{{ request()->routeIs('admin.withdrawals.index') ? 'active' : '' }}">
                <i class="bi bi-wallet2 me-2"></i> Retraits Utilisateurs 
        </a>
            --}}
    
            {{-- Marketplace --}}
        <a href="{{ route('admin.marketplace.index') }}"
           class="{{ request()->routeIs('admin.marketplace.index') ? 'active' : '' }}">
            <i class="bi bi-shop me-2"></i> Marketplace
        </a>

         {{-- Product Marketplace --}}
        <a href="{{ route('admin.products.index') }}"
           class="{{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
            <i class="bi bi-box-seam me-2"></i> Produits
        </a>

        <a href="{{ route('admin.settings.admins.index') }}"
           class="{{ request()->routeIs('admin.settings.admins.index') ? 'active' : '' }}">
            <i class="bi bi-gear-fill me-2"></i> Paramètres
        </a>

        <hr class="border-light">

        <button onclick="toggleTheme()" class="btn btn-light w-100 mt-3 d-flex align-items-center justify-content-center gap-2">
            <i id="themeIcon" class="bi bi-moon"></i> Mode sombre
        </button>
    </div>

    <!-- Main content -->
    <div class="main-content">
        @yield('content')
    </div>
@yield('scripts')

</body>
</html>
