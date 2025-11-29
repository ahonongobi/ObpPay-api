@extends('admin.layout')

@section('content')

<style>
/* CARD STYLING */
.product-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 25px;
    border: 1px solid #e5e7eb;
}

body.dark-mode .product-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,.1);
    color: #e5e7eb;
}

/* IMAGE */
.product-image {
    width: 100%;
    max-width: 320px;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,.15);
}

/* BADGES */
.badge-active { background: #10b981; }
.badge-inactive { background: #6b7280; }

/* INSTALLMENT BOX */
.installment-box {
    background: #f9fafb;
    border-radius: 14px;
    padding: 15px;
    border: 1px solid #d1d5db;
}

body.dark-mode .installment-box {
    background: #111827;
    border: 1px solid #374151;
    color: #e5e7eb;
}
</style>

<a href="{{ route('admin.products.index') }}" class="btn btn-secondary mb-3">
    ← Retour
</a>

<div class="product-card shadow-lg">

    <div class="row">
        <!-- IMAGE -->
        <div class="col-md-4 text-center">
            <img src="{{ asset($product->image) }}" class="product-image mb-3">
        </div>

        <!-- PRODUCT DETAILS -->
        <div class="col-md-8">

            <h2 class="fw-bold text-primary">{{ $product->name }}</h2>

            <p class="text-muted mb-1">
                <strong>Catégorie :</strong>
                {{ $product->category_id ?? '—' }}
            </p>

            <p class="text-muted mb-1">
                <strong>Prix :</strong>
                {{ number_format($product->price) }} {{ $product->currency }}
            </p>

            <p class="text-muted mb-1">
                <strong>Stock :</strong> {{ $product->stock }}
            </p>

            <p class="mb-3">
                <strong>Status :</strong>
                @if($product->is_active)
                    <span class="badge badge-active">Actif</span>
                @else
                    <span class="badge badge-inactive">Inactif</span>
                @endif
            </p>

            <p><strong>Description :</strong><br>
                {{ $product->description ?? 'Aucune description' }}
            </p>

            <p>
                <strong>Tags :</strong>  
                @if($product->tags)
                    @foreach(json_decode($product->tags) as $t)
                        <span class="badge bg-info me-1">{{ $t }}</span>
                    @endforeach
                @else
                    <span class="text-muted">—</span>
                @endif
            </p>

            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary mt-3">
                <i class="bi bi-pencil"></i> Modifier
            </a>
        </div>
    </div>

    <hr class="my-4">

    <!-- INSTALLMENTS -->
    <h4 class="fw-bold text-secondary mb-3">Plans de paiement</h4>

    @if($product->installments->count() == 0)
        <p class="text-muted">Aucun plan défini.</p>
    @else
        <div class="row">
            @foreach($product->installments as $plan)
                <div class="col-md-4">
                    <div class="installment-box mb-3 shadow-sm">
                        <h6 class="fw-bold">Durée : {{ $plan->months }} mois</h6>
                        <p class="mb-1">Mensualité :  
                            <strong>{{ number_format($plan->monthly_amount) }} XOF</strong>
                        </p>
                        <p>Total :  
                            <strong>{{ number_format($plan->total_amount) }} XOF</strong>
                        </p>
                        <p>Status :
                            @if($plan->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

@endsection
