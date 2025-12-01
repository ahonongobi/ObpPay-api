@extends('admin.layout')
<style>
    /* SEARCH CONTAINER */
.search-container {
    position: relative;
}

/* ICON INSIDE INPUT */
.search-icon {
    position: absolute;
    top: 50%;
    left: 12px;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 1.1rem;
    pointer-events: none;
}

/* INPUT */
.search-input {
    padding-left: 40px !important;
    border-radius: 12px;
    height: 44px;
    border: 1px solid #d1d5db;
    transition: .2s;
}

/* FOCUS EFFECT */
.search-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.2);
}

/* DARK MODE */
body.dark-mode .search-input {
    background: #1f2937;
    border: 1px solid #374151;
    color: #e5e7eb;
}

body.dark-mode .search-input::placeholder {
    color: #9ca3af;
}

body.dark-mode .search-icon {
    color: #9ca3af;
}

body.dark-mode .search-input:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(129,140,248,.25);
}

</style>
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold text-primary">Dashboard</h1>

    <form method="POST" action="/admin/logout">
        @csrf
        <button class="btn btn-danger shadow">Se déconnecter</button>
    </form>
</div>

<div class="row g-4">

    <!-- 🔵 TOTAL USERS -->
    <div class="col-md-4">
        <div class="stat-card stat-blue shadow-lg">
            <h6>Total Utilisateurs</h6>
            <h2>{{ \App\Models\User::count() }}</h2>
        </div>
    </div>

    <!-- 🟡 KYC PENDING -->
    <div class="col-md-4">
        <div class="stat-card stat-yellow shadow-lg">
            <h6>KYC en attente</h6>
            <h2>{{ \App\Models\Kyc::where('status','pending')->count() }}</h2>
        </div>
    </div>

    <!-- 🟢 APPROVED -->
    <div class="col-md-4">
        <div class="stat-card stat-green shadow-lg">
            <h6>Profils validés</h6>
            <h2>{{ \App\Models\Kyc::where('status','approved')->count() }}</h2>
        </div>
    </div>

</div>


<hr class="my-5">

<div class="card shadow-lg p-4 rounded-4 mt-5 stat-card">
    <h4 class="fw-bold mb-4 text-primary">📈 Transactions mensuelles</h4>

    <div style="height: 300px; max-height: 300px;">
        <canvas id="transactionsChart"></canvas>
    </div>
</div>



<hr class="my-5">

<h3 class="fw-bold mb-3 text-secondary">_Utilisateurs_</h3>

<div class="d-flex justify-content-between align-items-center mb-3">

    <!-- SEARCH BOX WITH ICON -->
    <div class="search-container w-50 position-relative">
        <i class="bi bi-search search-icon"></i>

        <input type="text"
               id="userSearch"
               class="form-control search-input"
               placeholder="Rechercher un utilisateur..."
               onkeyup="filterUsers()">
    </div>

    <!-- EXPORT BUTTON -->
    <button class="btn btn-outline-primary shadow-sm" onclick="exportUsersCSV()">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
    </button>

</div>


<div class="table-card shadow-lg">
    <table class="table-modern" id="usersTable">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>KYC</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
                @php
                    $pending = $u->kyc()->where('status','pending')->count();
                    $approved = $u->kyc()->where('status','approved')->count();
                @endphp

                <tr>
                    <td>{{ $u->obp_id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->phone }}</td>
                    <td>
                        @if($approved > 0)
                            <span class="badge-modern badge-approved">Validé</span>
                        @elseif($pending > 0)
                            <span class="badge-modern badge-pending">En attente</span>
                        @else
                            <span class="badge-modern badge-none">Aucun</span>
                        @endif
                    </td>
                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- BEAUTIFUL PAGINATION -->
    <div class="d-flex justify-content-center mt-4">
        {{ $users->links('vendor.pagination.custom') }}

    </div>

</div>



<script>
function filterUsers() {
    let value = document.getElementById("userSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#usersTable tbody tr");

    rows.forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(value) ? "" : "none";
    });
}

function exportUsersCSV() {
    let rows = document.querySelectorAll("#usersTable tr");
    let csv = [];

    rows.forEach(row => {
        let cols = [...row.querySelectorAll("th,td")].map(c => c.innerText);
        csv.push(cols.join(","));
    });

    let file = new Blob([csv.join("\n")], { type: "text/csv" });
    let url = URL.createObjectURL(file);

    let a = document.createElement("a");
    a.href = url;
    a.download = "users.csv";
    a.click();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('transactionsChart');

let monthLabels = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];
let dataValues = @json($totals);

const isDark = document.body.classList.contains('dark-mode');

const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: "Montant total (XOF)",
            data: dataValues,
            fill: true,
            borderWidth: 4,
            borderColor: isDark ? "#818cf8" : "#4f46e5",
            backgroundColor: isDark
                ? "rgba(129,140,248,0.15)"
                : "rgba(79,70,229,0.15)",
            tension: 0.3,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: isDark ? "#a5b4fc" : "#6366f1",
            pointBorderWidth: 2,
        }]
    },
    options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: { legend: { display: false } },
    scales: {
        y: {
            beginAtZero: true,
            maxTicksLimit: 6,
            ticks: {
                color: isDark ? "#d1d5db" : "#374151",
                callback: value => value.toLocaleString("fr-FR")
            },
            grid: {
                color: isDark ? "rgba(255,255,255,0.1)" : "rgba(0,0,0,0.05)"
            }
        },
        x: {
            ticks: { color: isDark ? "#d1d5db" : "#374151" },
            grid: { display: false }
        }
    }
}

});

// UPDATE COLORS WHEN SWITCHING DARK MODE
function refreshChartTheme() {
    const dark = document.body.classList.contains("dark-mode");

    chart.data.datasets[0].borderColor = dark ? "#818cf8" : "#4f46e5";
    chart.data.datasets[0].pointBackgroundColor = dark ? "#a5b4fc" : "#6366f1";
    chart.data.datasets[0].backgroundColor = dark
        ? "rgba(129,140,248,0.15)"
        : "rgba(79,70,229,0.15)";

    chart.options.scales.x.ticks.color = dark ? "#e5e7eb" : "#374151";
    chart.options.scales.y.ticks.color = dark ? "#e5e7eb" : "#374151";
    chart.options.scales.y.grid.color = dark
        ? "rgba(255,255,255,0.1)"
        : "rgba(0,0,0,0.05)";

    chart.update();
}

document.getElementById("themeIcon").onclick = () => {
    toggleTheme();
    setTimeout(refreshChartTheme, 220);
};
</script>



@endsection
