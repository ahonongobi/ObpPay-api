@extends('admin.layout')
<style>

/* -------------------------------------------
   BASE CARD STYLE
------------------------------------------- */
.stat-card {
    background: #ffffff;
    border: 1px solid #eee;
    border-radius: 16px;
    transition: 0.25s ease-in-out;
}

/* Hover effect */
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

/* -------------------------------------------
   DARK MODE - CARDS
------------------------------------------- */
body.dark-mode .stat-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.1);
    color: #e5e7eb !important;
    box-shadow: 0 8px 18px rgba(0,0,0,0.35);
}

/* -------------------------------------------
   TEXT MUTED FIX (Bootstrap .text-muted)
------------------------------------------- */

/* Light mode muted */
.text-muted {
    color: #6c757d !important;
}

/* Dark mode muted */
body.dark-mode .text-muted {
    color: #9ca3af !important;
}

/* -------------------------------------------
   TITLES / HEADERS COLORS
------------------------------------------- */
body.dark-mode h1,
body.dark-mode h2,
body.dark-mode h3,
body.dark-mode h4,
body.dark-mode h5 {
    color: #f3f4f6 !important;
}

/* -------------------------------------------
   BADGES FIX (for contrast in dark)
------------------------------------------- */
body.dark-mode .badge.bg-warning {
    background-color: #facc15 !important;
    color: #332800 !important;
}

body.dark-mode .badge.bg-secondary {
    background-color: #6b7280 !important;
}

body.dark-mode .badge.bg-success {
    background-color: #16a34a !important;
}

body.dark-mode .badge.bg-danger {
    background-color: #dc2626 !important;
}

/* -------------------------------------------
   MODAL FIX (dark mode)
------------------------------------------- */
body.dark-mode .modal-content {
    background: #1f2937;
    color: #f3f4f6;
    border: 1px solid rgba(255,255,255,0.1);
}

body.dark-mode .modal-header {
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

body.dark-mode .modal-footer {
    border-top: 1px solid rgba(255,255,255,0.15);
}

body.dark-mode .btn-close {
    filter: invert(1); /* makes the "X" visible in dark */
}

</style>


@section('content')


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


<h1 class="fw-bold text-primary mb-4">
    Profil utilisateur : {{ $user->name }}
</h1>

<!-- USER PROFILE CARD -->
<div class="card shadow-lg border-0 rounded-4 p-4 mb-4 user-card">

    <div class="d-flex align-items-center gap-4">
        

        <div class="d-flex align-items-center gap-4">
    <div class="profile-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
        <i class="bi bi-person-fill fs-1"></i>
    </div>

    <div>
        <h3 class="fw-bold">{{ $user->name }}</h3>

        <p class="mb-1"><i class="bi bi-phone"></i> {{ $user->phone }}</p>

        <p class="mb-1">
            <i class="bi bi-envelope"></i>
            {{ $user->email ?? 'Aucun email' }}
        </p>

        <p class="mb-1">
            <i class="bi bi-wallet2"></i>
            Balance :
            <strong class="text-success">
                {{ number_format(optional($user->wallet)->balance ?? 0, 0, ',', ' ') }} XOF

            </strong>
        </p>

        {{-- KYC STATUS --}}
            <p class="mb-1">
                <i class="bi bi-shield-check"></i>
                KYC :
                @if($kycStatus === 'approved')
                    <span class="badge bg-success">Validé</span>
                @elseif($kycStatus === 'pending')
                    <span class="badge bg-warning text-dark">En attente</span>
                @else
                    <span class="badge bg-secondary">Aucun</span>
                @endif
            </p>

        <p>
            <i class="bi bi-calendar"></i> 
            Inscrit le : {{ $user->created_at->format('d/m/Y') }}
        </p>
    </div>
</div>

    </div>

    <div class="mt-4 d-flex gap-3">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Modifier</a>
        <button onclick="deleteUser({{ $user->id }})" class="btn btn-danger">
            <i class="bi bi-trash"></i> Supprimer
        </button>
    </div>
</div>


{{-- STATISTIQUES FINANCIÈRES --}}
<h4 class="fw-bold text-primary mt-4 mb-3">Statistiques financières</h4>

<div class="row g-4">

    {{-- Total transactions --}}
    <div class="col-md-4">
        <div class="card stat-card shadow rounded-4 p-4">
            <h6 class="text-muted">Total transactions</h6>
            <h3 class="fw-bold">{{ $stats['total_transactions'] }}</h3>
        </div>
    </div>

    {{-- Total Money Flow --}}
    <div class="col-md-4">
        <div class="card stat-card shadow rounded-4 p-4">
            <h6 class="text-muted">Flux total</h6>
            <h3 class="fw-bold text-primary">{{ number_format($stats['total_amount'],0,',',' ') }} XOF</h3>
        </div>
    </div>

    {{-- Purchases --}}
    <div class="col-md-4">
        <div class="card stat-card shadow rounded-4 p-4">
            <h6 class="text-muted">Achats</h6>
            <h3 class="fw-bold text-danger">{{ number_format($stats['purchases'],0,',',' ') }} XOF</h3>
        </div>
    </div>

    {{-- Deposits --}}
    <div class="col-md-4">
        <div class="card stat-card shadow rounded-4 p-4">
            <h6 class="text-muted">Dépôts</h6>
            <h3 class="fw-bold text-success">{{ number_format($stats['deposits'],0,',',' ') }} XOF</h3>
        </div>
    </div>

    {{-- Withdrawals --}}
    <div class="col-md-4">
        <div class="card stat-card shadow rounded-4 p-4">
            <h6 class="text-muted">Retraits</h6>
            <h3 class="fw-bold text-warning">{{ number_format($stats['withdraws'],0,',',' ') }} XOF</h3>
        </div>
    </div>

    {{-- Transfers --}}
    <div class="col-md-4">
        <div class="card stat-card shadow rounded-4 p-4">
            <h6 class="text-muted">Transferts</h6>
            <p class="mb-1 text-muted">Entrants : <strong>{{ number_format($stats['transfer_in'],0,',',' ') }} XOF</strong></p>
            <p class="text-muted">Sortants : <strong class="text-danger">{{ number_format($stats['transfer_out'],0,',',' ') }} XOF</strong></p>
        </div>
    </div>
</div>

<!-- KYC SECTION -->
<h4 class="fw-bold text-primary mt-4">Documents KYC</h4>

<div class="table-card shadow-lg mt-3">
    <table class="table-modern w-100">
        <thead>
            <tr>
                <th>Type</th>
                <th>Status</th>
                <th>Document</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            @forelse($user->kyc as $doc)
                <tr>
                    <td class="text-capitalize">{{ $doc->type }}</td>

                    <td>
                        @if($doc->status === 'approved')
                            <span class="badge-modern badge-approved">Validé</span>
                        @elseif($doc->status === 'pending')
                            <span class="badge-modern badge-pending">En attente</span>
                        @else
                            <span class="badge-modern badge-none">Rejeté</span>
                        @endif
                    </td>

                    <td>
                        <img src="/storage/{{ $doc->file_path }}" 
                             class="rounded shadow-sm"
                             style="width: 100px; height: auto;">
                    </td>

                    <td>{{ $doc->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Aucun document KYC</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


<!-- TRANSACTIONS SECTION -->
<h4 class="fw-bold text-primary mt-5">Transactions récentes</h4>

<div class="table-card shadow-lg mt-3">
    <table class="table-modern w-100">
        <thead>
            <tr>
                <th>Type</th>
                <th>Montant</th>
                <th>Status</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            @forelse($user->transactions()->latest()->take(10)->get() as $t)
                <tr>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $t->type) }}</td>

                    <td><strong>{{ number_format($t->amount, 0, ',', ' ') }} {{ $t->currency }}</strong></td>

                    <td>
                        @if($t->status === 'completed')
                            <span class="badge bg-success">Terminé</span>
                        @elseif($t->status === 'pending')
                            <span class="badge bg-warning text-dark">En attente</span>
                        @else
                            <span class="badge bg-danger">Échoué</span>
                        @endif
                    </td>

                    <td>{{ $t->description ?? '-' }}</td>
                    <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Aucune transaction récente
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


@endsection


@section('scripts')
<script>
function deleteUser(id) {
    if (!confirm("Supprimer cet utilisateur ?")) return;

    fetch(`/admin/users/${id}`, {
        method: 'DELETE',
        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"}
    }).then(() => location.href = "/admin/users");
}
</script>

<style>
/* Profile circle */
.profile-icon {
    width: 90px;
    height: 90px;
}

/* DARK MODE FIXES */
body.dark-mode .user-card {
    background: #1f2937 !important;
    color: #e5e7eb !important;
}

body.dark-mode .user-card p {
    color: #cbd5e1 !important;
}

</style>
@endsection
