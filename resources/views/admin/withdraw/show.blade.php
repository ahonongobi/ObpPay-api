@extends('admin.layout')

@section('content')

<style>
/* CARD */
.withdraw-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 25px;
    border: 1px solid #e5e7eb;
}

body.dark-mode .withdraw-card {
    background: #1f2937;
    color: #e5e7eb;
    border: 1px solid rgba(255,255,255,0.1);
}

/* Badge text fix */
body.dark-mode .badge-modern {
    color: #111827 !important;
}

/* TITLES & LABELS */
.withdraw-card p {
    font-size: 1.05rem;
}

.withdraw-card strong {
    color: #374151;
}

body.dark-mode .withdraw-card strong {
    color: #d1d5db;
}

/* HR line */
.withdraw-card hr {
    border-color: #d1d5db;
}
body.dark-mode .withdraw-card hr {
    border-color: rgba(255,255,255,0.2);
}
</style>

<a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary mb-3">
    ← Retour
</a>

<div class="withdraw-card shadow-lg">

    <h3 class="fw-bold text-primary mb-4">Détails du retrait</h3>

    <p><strong>Utilisateur :</strong> {{ $withdrawal->user->name }}</p>
    <p><strong>Téléphone :</strong> {{ $withdrawal->user->phone }}</p>

    <p><strong>Montant :</strong>
        <span class="fw-bold">{{ number_format($withdrawal->amount) }} XOF</span>
    </p>

    <p><strong>Méthode :</strong> {{ ucfirst($withdrawal->method) }}</p>
    <p><strong>Destinataire :</strong> {{ $withdrawal->recipient }}</p>

    <p><strong>Status :</strong>
        @if($withdrawal->status == 'approved')
            <span class="badge-modern badge-approved">Approuvé</span>
        @elseif($withdrawal->status == 'pending')
            <span class="badge-modern badge-pending">En attente</span>
        @else
            <span class="badge-modern badge-none">Rejeté</span>
        @endif
    </p>

    <p><strong>Notes Admin :</strong> {{ $withdrawal->admin_notes ?? '—' }}</p>

    <p><strong>Demandé le :</strong> {{ $withdrawal->created_at->format('d/m/Y H:i') }}</p>

    <hr class="my-4">

    @if($withdrawal->status === 'pending')
        <h5 class="fw-bold text-secondary mb-3">Décision</h5>

        <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" class="d-inline">
            @csrf
            <button class="btn btn-success px-4">Approuver</button>
        </form>

        <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal->id) }}" class="d-inline">
            @csrf
            <button class="btn btn-danger px-4">Rejeter</button>
        </form>
    @else
        <p class="text-muted">Aucune action disponible – retrait déjà traité.</p>
    @endif

</div>

@endsection
