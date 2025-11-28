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

<h1 class="fw-bold text-primary mb-4">Gestion des utilisateurs</h1>

<!-- SEARCH + EXPORT -->
<div class="d-flex justify-content-between align-items-center mb-3">

    <!-- SEARCH BOX -->
    <div class="search-container w-50 position-relative">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="userSearch" class="form-control search-input"
               placeholder="Rechercher un utilisateur..." onkeyup="filterUsers()">
    </div>

    <!-- EXPORT CSV -->
    <button onclick="exportUsersCSV()" class="btn btn-outline-primary shadow-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
    </button>
</div>

<!-- TABLE -->
<div class="table-card shadow-lg">

    <table class="table-modern w-100" id="usersTable">
        <thead>
            <tr>
                <th>#OBP ID</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>KYC</th>
                <th>Date</th>
                <th>Actions</th>
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

                    <td>
                        <a href="{{ route('admin.users.show', $u->id) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i>
                        </a>

                        <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <button class="btn btn-sm btn-danger" onclick="deleteUser({{ $u->id }})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

    <!-- PAGINATION -->
    <div class="mt-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>

</div>

@endsection


@section('scripts')
<script>
/* -------------------------
       CLIENT SEARCH
------------------------- */
function filterUsers() {
    let input = document.getElementById("userSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#usersTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

/* -------------------------
         EXPORT CSV
------------------------- */
function exportUsersCSV() {
    let rows = Array.from(document.querySelectorAll("#usersTable tr"));
    let csv = rows.map(row =>
        Array.from(row.querySelectorAll("th,td"))
             .map(cell => `"${cell.innerText}"`)
             .join(",")
    ).join("\n");

    let blob = new Blob([csv], { type: "text/csv" });
    let url = window.URL.createObjectURL(blob);

    let a = document.createElement("a");
    a.href = url;
    a.download = "utilisateurs.csv";
    a.click();
}

/* -------------------------
          DELETE USER
------------------------- */
function deleteUser(id) {
    if (!confirm("Supprimer cet utilisateur ?")) return;

    fetch(`/admin/users/${id}`, {
        method: 'DELETE',
        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"}
    }).then(() => location.reload());
}
</script>
@endsection
