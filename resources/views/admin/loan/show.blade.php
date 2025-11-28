@extends('admin.layout')
<style>
/* ---------- CARD ADAPTATION ---------- */
.stat-card {
    background: #ffffff;
    border: 1px solid #eee;
    border-radius: 16px;
}

/* DARK MODE */
body.dark-mode .stat-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.1);
    color: #f3f4f6 !important;
}

/* ---------- TITLES ---------- */
.stat-card h3, 
.stat-card h5 {
    color: #111827;
}

body.dark-mode .stat-card h3,
body.dark-mode .stat-card h5 {
    color: #e5e7eb !important;
}

/* ---------- STRONG TEXT ---------- */
.stat-card strong {
    color: #111827;
}

body.dark-mode .stat-card strong {
    color: #e5e7eb !important;
}

/* ---------- NORMAL PARAGRAPHS ---------- */
.stat-card p {
    color: #333;
}

body.dark-mode .stat-card p {
    color: #d1d5db !important;
}

/* ---------- BADGE COLORS (you already use these) ---------- */
body.dark-mode .badge-approved { background:#198754; }
body.dark-mode .badge-pending { background:#ffca2c; color:#332a00; }
body.dark-mode .badge-none { background:#6c757d; }

/* ---------- BUTTONS ---------- */
body.dark-mode .btn-success { background:#198754; border-color:#198754; }
body.dark-mode .btn-danger  { background:#dc3545; border-color:#dc3545; }
body.dark-mode .btn-secondary { background:#4b5563; border-color:#4b5563; }

</style>

@section('content')

<a href="{{ route('admin.loans.index') }}" class="btn btn-secondary mb-3">
    ← Retour
</a>

<div class="card p-4 shadow-lg stat-card">

    <h3 class="fw-bold mb-3">Détails de la demande d’aide</h3>

    <p><strong>Utilisateur :</strong> {{ $loan->user->name }}</p>
    <p><strong>Téléphone :</strong> {{ $loan->user->phone }}</p>
    <p><strong>Éligibilité :</strong>

    @if($eligibility == true)
        <span class="badge-modern badge-approved ms-2">
            Oui ({{ $loan->user->score }} Points)
        </span>
    @else
        <span class="badge-modern badge-none ms-2">
            Non ({{ $loan->user->score }} Points)
        </span>
    @endif

</p>


    <p><strong>Catégorie :</strong> {{ $loan->category }}</p>

    @if($loan->custom_category)
        <p><strong>Catégorie personnalisée :</strong> {{ $loan->custom_category }}</p>
    @endif

    <p><strong>Montant :</strong> {{ number_format($loan->amount) }} XOF</p>

    <p><strong>Status :</strong> 
        @if($loan->status == 'approved')
            <span class="badge-modern badge-approved">Approuvé</span>
        @elseif($loan->status == 'pending')
            <span class="badge-modern badge-pending">En attente</span>
        @else
            <span class="badge-modern badge-none">Rejeté</span>
        @endif
    </p>

    <p><strong>Notes :</strong> {{ $loan->notes ?? '—' }}</p>

    <hr>

    <h5 class="fw-bold mt-3">Actions</h5>

    @if($loan->status === 'pending')

        <form method="POST" 
              action="{{ route('admin.loans.approve', $loan->id) }}"
              class="d-inline">
            @csrf
            <button class="btn btn-success">Approuver</button>
        </form>

        <form method="POST"
              action="{{ route('admin.loans.reject', $loan->id) }}"
              class="d-inline">
            @csrf
            <button class="btn btn-danger">Rejeter</button>
        </form>

    @else
        <p class="text-muted mt-2">Aucune action disponible.</p>
    @endif

</div>

@endsection
