@extends('admin.layout')

@section('content')

<style>
/* ---- FORM CARD ---- */
.settings-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 25px;
    border: 1px solid #e5e7eb;
}

body.dark-mode .settings-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.1);
    color: #e5e7eb;
}

/* ---- INPUTS ---- */
.settings-card input,
.settings-card select {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #d1d5db;
}

.settings-card input:focus,
.settings-card select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.25);
}

/* DARK MODE INPUTS */
body.dark-mode .settings-card input,
body.dark-mode .settings-card select {
    background: #111827;
    color: #e5e7eb;
    border: 1px solid #374151;
}

body.dark-mode .settings-card input::placeholder {
    color: #9ca3af;
}

/* ---- TABLE ---- */
.table-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 20px;
    border: 1px solid #e5e7eb;
}

body.dark-mode .table-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.05);
}

.table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

.table-modern thead th {
    color: #6b7280;
}

body.dark-mode .table-modern thead th {
    color: #9ca3af;
}

.table-modern tbody tr {
    background: #f8f9fa;
    border-radius: 12px;
}

.table-modern tbody tr:hover {
    background: #eef1f4;
}

/* DARK */
body.dark-mode .table-modern tbody tr {
    background: rgba(255,255,255,0.04);
}

body.dark-mode .table-modern tbody tr:hover {
    background: rgba(255,255,255,0.08);
}

.table-modern td {
    padding: 14px;
}

/* BADGES */
.badge-superadmin {
    background: #0ea5e9;
}

.badge-admin {
    background: #6366f1;
}

/* ACTION BUTTON */
.delete-btn {
    padding: 6px 12px;
    border-radius: 10px;
}
</style>


<h1 class="fw-bold text-primary mb-4">Gestion des administrateurs</h1>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
        <i class="bi bi-check2-circle fs-4 me-2"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-3">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif


<!-- ----------------------- -->
<!-- ADD ADMIN FORM SECTION  -->
<!-- ----------------------- -->
<div class="settings-card shadow-lg mb-5">
    <h4 class="fw-bold text-secondary mb-3">Ajouter un nouvel admin</h4>

    <form method="POST" action="{{ route('admin.settings.admins.store') }}">
        @csrf

        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Rôle</label>
                <select name="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>

        </div>

        <button class="btn btn-primary mt-4 px-4">Créer l'admin</button>
    </form>
</div>



<!-- ----------------------- -->
<!-- LIST OF ADMINS SECTION  -->
<!-- ----------------------- -->
<div class="table-card shadow-lg">
    <table class="table-modern">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Rôle</th>
                <th>Date de création</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach($admins as $a)
            <tr>
                <td>{{ $a->id }}</td>
                <td>{{ $a->name }}</td>
                <td>{{ $a->phone }}</td>

                <td>
                    @if($a->role === 'superadmin')
                        <span class="badge badge-superadmin">Super Admin</span>
                    @else
                        <span class="badge badge-admin">Admin</span>
                    @endif
                </td>

                <td>{{ $a->created_at->format('d/m/Y') }}</td>

                <td>
                   @if(auth()->id() !== $a->id)
    <!-- Bouton ouvrir modal -->
    <button 
        class="btn btn-danger delete-btn" 
        data-bs-toggle="modal" 
        data-bs-target="#confirmDeleteModal{{ $a->id }}">
        Supprimer
    </button>

        @else
            <span class="text-muted">—</span>
        @endif

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-4">
        {{ $admins->links('vendor.pagination.custom') }}
    </div>

    <!-- MODAL -->
<div class="modal fade" id="confirmDeleteModal{{ $a->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content settings-card">

            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirmation</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Voulez-vous vraiment supprimer l’administrateur 
                <strong>{{ $a->name }}</strong> ?
                <br>Cette action est irréversible.
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>

                <form action="{{ route('admin.settings.admins.delete',$a->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Supprimer</button>
                </form>
            </div>

        </div>
    </div>
</div>

</div>

@endsection
