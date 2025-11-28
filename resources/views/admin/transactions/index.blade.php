@extends('admin.layout')
<style>
    /* ===== FILTER CARD ===== */
.filter-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    transition: 0.2s ease-in-out;
}

body.dark-mode .filter-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.08);
}

/* ===== LABEL ===== */
.filter-label {
    font-weight: 600;
    color: #374151;
}

body.dark-mode .filter-label {
    color: #e5e7eb;
}

/* ===== SELECT BEAUTIFICATION ===== */
.filter-select {
    border-radius: 12px;
    padding: 7px 12px;
    border: 1px solid #d1d5db;
    background: #ffffff;
    color: #111827;
    transition: 0.2s;
    min-width: 150px;
}

.filter-select:hover {
    border-color: #6366f1;
    box-shadow: 0 0 4px rgba(79,70,229,0.3);
}

body.dark-mode .filter-select {
    background: #111827;
    border-color: #374151;
    color: #f3f4f6;
}

body.dark-mode .filter-select:hover {
    border-color: #818cf8;
    box-shadow: 0 0 4px rgba(129,140,248,0.4);
}


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

/* INPUT STYLING */
.search-input {
    padding-left: 40px !important;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    height: 44px;
    transition: 0.2s;
}

/* Hover + Focus */
.search-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
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
    box-shadow: 0 0 0 3px rgba(129,140,248,0.25);
}

</style>
@section('content')

<h1 class="fw-bold text-primary mb-4">Transactions</h1>

<!-- FILTER CARD -->
<div class="filter-card mb-4 p-4 shadow-sm rounded-4 d-flex flex-wrap align-items-center gap-3">

    <form method="GET" class="d-flex flex-wrap align-items-center gap-3">

        <!-- TYPE -->
        <div class="filter-group d-flex align-items-center gap-2">
            <span class="filter-label"><i class="bi bi-funnel me-1"></i>Type</span>

            <select name="type" class="form-select filter-select" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="deposit" {{ request('type')=='deposit' ? 'selected':'' }}>Dépôt</option>
                <option value="withdraw" {{ request('type')=='withdraw' ? 'selected':'' }}>Retrait</option>
                <option value="transfer_in" {{ request('type')=='transfer_in' ? 'selected':'' }}>Transfert IN</option>
                <option value="transfer_out" {{ request('type')=='transfer_out' ? 'selected':'' }}>Transfert OUT</option>
                <option value="purchase" {{ request('type')=='purchase' ? 'selected':'' }}>Achat</option>
            </select>
        </div>

        <!-- STATUS -->
        <div class="filter-group d-flex align-items-center gap-2">
            <span class="filter-label"><i class="bi bi-check2-circle me-1"></i>Status</span>

            <select name="status" class="form-select filter-select" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="completed" {{ request('status')=='completed' ? 'selected':'' }}>Terminé</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>En attente</option>
                <option value="failed" {{ request('status')=='failed' ? 'selected':'' }}>Échoué</option>
            </select>
        </div>

    </form>
</div>


<div class="table-card shadow-lg">

<div class="d-flex justify-content-between align-items-center mb-3">

    <!-- SEARCH BOX WITH ICON -->
    <div class="search-container w-50 position-relative">
        <i class="bi bi-search search-icon"></i>

        <input type="text" id="searchInput"
               class="form-control search-input"
               placeholder="Rechercher une transaction...">
    </div>

    <!-- EXPORT BUTTON -->
    <button onclick="exportCSV()" class="btn btn-success shadow-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
    </button>
</div>


    <table class="table-modern" id="transactionsTable">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Utilisateur</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            @foreach($transactions as $t)
            <tr>
                <td>{{ $t->user->obp_id }}</td>
                <td>{{ $t->user->name }}</td>

                <td>
                    <span class="badge-modern
                        @if($t->type==='deposit') badge-approved
                        @elseif($t->type==='withdraw') badge-pending
                        @else badge-none @endif">
                        {{ ucfirst($t->type) }}
                    </span>
                </td>

                <td><strong>{{ number_format($t->amount, 2) }} {{ $t->currency }}</strong></td>

                <td>
                    @if($t->status === 'completed')
                        <span class="badge bg-success">OK</span>
                    @elseif($t->status === 'pending')
                        <span class="badge bg-warning text-dark">En attente</span>
                    @else
                        <span class="badge bg-danger">Échoué</span>
                    @endif
                </td>

                <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        {{ $transactions->links('vendor.pagination.custom')}}
    </div>
</div>

@endsection

@section('scripts')
<script>
// SEARCH
document.getElementById('searchInput').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#transactionsTable tbody tr');

    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter)
            ? '' : 'none';
    });
});

// CSV EXPORT
function exportCSV() {
    let csv = [];
    let rows = document.querySelectorAll("table tr");

    rows.forEach(row => {
        let cols = row.querySelectorAll("td, th");
        let rowData = [];
        cols.forEach(col => rowData.push('"' + col.innerText + '"'));
        csv.push(rowData.join(","));
    });

    let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
    let tempLink = document.createElement("a");
    tempLink.download = "transactions.csv";
    tempLink.href = window.URL.createObjectURL(csvFile);
    tempLink.click();
}
</script>
@endsection
