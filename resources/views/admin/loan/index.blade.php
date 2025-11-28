@extends('admin.layout')

@section('content')

<h1 class="fw-bold text-primary mb-4">Demandes d’aide / Prêts</h1>

<div class="table-card shadow-lg">
    <table class="table-modern">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Utilisateur</th>
                <th>Catégorie</th>
                <th>Montant</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->id }}</td>
                <td>{{ $loan->user->name }}</td>
                <td>{{ $loan->category }}</td>
                <td>{{ number_format($loan->amount, 0) }} XOF</td>

                <td>
                    @if($loan->status == 'approved')
                        <span class="badge-modern badge-approved">Approuvé</span>
                    @elseif($loan->status == 'pending')
                        <span class="badge-modern badge-pending">En attente</span>
                    @else
                        <span class="badge-modern badge-none">Rejeté</span>
                    @endif
                </td>

                <td>{{ $loan->created_at->format('d/m/Y') }}</td>

                <td>
                    <a href="{{ route('admin.loans.show', $loan->id) }}" 
                       class="btn btn-sm btn-primary">
                       Voir
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-4">
        {{ $loans->links('vendor.pagination.custom') }}
    </div>
</div>

@endsection
