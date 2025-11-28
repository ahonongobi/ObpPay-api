@extends('admin.layout')

@section('content')

<style>
/* ---- CARD ---- */
.product-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 25px;
    border: 1px solid #e5e7eb;
}
body.dark-mode .product-card {
    background: #1f2937;
    border: 1px solid rgba(255,255,255,0.1);
    color: #f3f4f6;
}
/* INPUTS */
body.dark-mode .form-control,
body.dark-mode .form-select {
    background: #111827 !important;
    color: white !important;
    border: 1px solid #374151;
}
body.dark-mode .form-label {
    color: #e5e7eb;
}

/* IMAGE PREVIEW */
.image-preview {
    width: 160px;
    height: 160px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}
body.dark-mode .image-preview {
    border: 2px solid #374151;
}

/* INSTALLMENT ROW */
.plan-row {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}
body.dark-mode .plan-row {
    background: #1f2937;
    border: 1px solid #374151;
}
</style>

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


<h1 class="fw-bold text-primary mb-4">Ajouter un produit</h1>

<div class="product-card shadow-lg">

<form action="{{ route('admin.marketplace.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">

        <!-- PRODUCT NAME -->
        <div class="col-md-6">
            <label class="form-label">Nom du produit</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <!-- PRICE -->
        <div class="col-md-3">
            <label class="form-label">Prix</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>

        <!-- STOCK -->
        <div class="col-md-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" required>
        </div>

        <!-- CATEGORY -->
        <div class="col-md-6">
            <label class="form-label">Catégorie</label>
            <select name="category_id" class="form-select">
                <option value="">— Aucune —</option>

                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- TAGS -->
        <div class="col-md-6">
            <label class="form-label">Tags</label>
            <input type="text" name="tags" class="form-control" placeholder="bio, promo, local">
        </div>

        <!-- DESCRIPTION -->
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>

        <!-- IMAGE -->
        <div class="col-md-6">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewProductImage(this)">
        </div>

        <div class="col-md-6 d-flex align-items-end">
            <img id="preview" class="image-preview d-none" />
        </div>

        <!-- STATUS -->
        <div class="col-12 mt-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" checked>
                <label class="form-check-label">Produit actif</label>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- INSTALLMENT PLANS -->
    <h4 class="fw-bold mb-3 text-secondary">Plans d'échelonnement</h4>

    <div id="plansContainer" class="d-flex flex-column gap-3"></div>

    <button type="button" class="btn btn-outline-primary mt-3" onclick="addPlan()">
        <i class="bi bi-plus-circle"></i> Ajouter un plan
    </button>

    <div class="mt-4">
        <button class="btn btn-primary px-4">
            <i class="bi bi-check-circle"></i> Enregistrer
        </button>
    </div>

</form>

</div>

<!-- JS for image preview and dynamic plans -->
<script>
function previewProductImage(input) {
    let img = document.getElementById('preview');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('d-none');
    }
}

let planIndex = 0;

function addPlan() {
    let container = document.getElementById('plansContainer');

    let html = `
    <div class="plan-row shadow-sm" id="plan-${planIndex}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Durée (mois)</label>
                <input type="number" name="plans[${planIndex}][months]" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Montant mensuel</label>
                <input type="number" step="0.01" name="plans[${planIndex}][monthly_amount]" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Montant total</label>
                <input type="number" step="0.01" name="plans[${planIndex}][total_amount]" class="form-control" required>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="plans[${planIndex}][is_active]" checked>
                    <label class="form-check-label">Actif</label>
                </div>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="removePlan(${planIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    planIndex++;
}

function removePlan(index) {
    document.getElementById("plan-" + index).remove();
}
</script>

@endsection
