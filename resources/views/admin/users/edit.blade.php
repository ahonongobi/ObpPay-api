@extends('admin.layout')

@section('content')

<h1 class="fw-bold text-primary mb-4">Modifier l’utilisateur</h1>

<div class="card shadow-lg p-4 rounded-4 edit-card">

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">

            <!-- NOM -->
            <div class="col-md-6">
                <label class="form-label fw-bold">Nom complet</label>
                <input type="text" name="name" class="form-control" 
                       value="{{ $user->name }}" required>
            </div>

            <!-- PHONE -->
            <div class="col-md-6">
                <label class="form-label fw-bold">Téléphone</label>
                <input type="text" name="phone" class="form-control" 
                       value="{{ $user->phone }}" required>
            </div>

            <!-- EMAIL -->
            <div class="col-md-6">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ $user->email }}">
            </div>

            <!-- STATUS -->
            <div class="col-md-6">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>
                        Actif
                    </option>
                    <option value="blocked" {{ $user->status == 'blocked' ? 'selected' : '' }}>
                        Bloqué
                    </option>
                </select>
            </div>

            <!-- BALANCE -->
            <div class="col-md-6">
            <label class="form-label fw-bold">Balance (XOF)</label>
            <input type="number" name="balance" step="0.01" class="form-control"
                value="{{ $user->wallet->balance ?? 0 }}" required>
             </div>


        </div>

        <!-- BUTTONS -->
        <div class="d-flex justify-content-end mt-4 gap-3">
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary">
                Annuler
            </a>
            <button type="submit" class="btn btn-primary px-4">
                Mettre à jour
            </button>
        </div>
    </form>

</div>

@endsection

@section('scripts')
<style>
/* DARK MODE SUPPORT */
body.dark-mode .edit-card {
    background: #1f2937 !important;
    color: #e5e7eb;
    border: 1px solid rgba(255,255,255,0.05);
}

body.dark-mode .form-control,
body.dark-mode .form-select {
    background: #111827 !important;
    color: #e5e7eb !important;
    border: 1px solid #374151 !important;
}

body.dark-mode label {
    color: #e5e7eb !important;
}
</style>
@endsection
