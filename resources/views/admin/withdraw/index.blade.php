@extends('admin.layout')

@section('content')

<h1 class="fw-bold text-primary mb-4">Demandes de retrait</h1>

<div class="table-card shadow-lg">

    <table class="table-modern">
        <thead>
            <tr>
                <th>ID</th>
                <th>Utilisateur</th>
                <th>Montant</th>
                <th>Méthode</th>
                <th>Destinataire</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        @foreach($withdrawals as $w)
            <tr>
                <td>{{ $w->id }}</td>
                <td>{{ $w->user->name }}</td>
                <td>{{ number_format($w->amount) }} XOF</td>
                <td>{{ ucfirst($w->method) }}</td>
                <td>{{ $w->recipient }}</td>

                <td>
                    @if($w->status == 'approved')
                        <span class="badge-modern badge-approved">Approuvé</span>
                    @elseif($w->status == 'pending')
                        <span class="badge-modern badge-pending">En attente</span>
                    @elseif($w->status == 'completed')
                        <span class="badge bg-info">Terminé</span>
                    @else
                        <span class="badge-modern badge-none">Rejeté</span>
                    @endif
                </td>

                <td>{{ $w->created_at->format('d/m/Y') }}</td>

                <td>
                    <a href="{{ route('admin.withdrawals.show', $w->id) }}" class="btn btn-primary btn-sm">
                        Voir
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-4">
        {{ $withdrawals->links('vendor.pagination.custom') }}
    </div>

</div>

@endsection
