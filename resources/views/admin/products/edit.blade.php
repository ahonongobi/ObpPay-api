@extends('admin.layout')

@section('content')

<style>
.edit-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 25px;
    border: 1px solid #e5e7eb;
}

body.dark-mode .edit-card {
    background: #1f2937;
    color: #e5e7eb;
    border: 1px solid rgba(255,255,255,.1);
}

.form-control, .form-select {
    border-radius: 12px;
}

body.dark-mode .form-control,
body.dark-mode .form-select {
    background: #111827;
    border: 1px solid #374151;
    color: #e5e7eb;
}

/* Installments table */
.installment-table input {
    background: #ffffff;
}
body.dark-mode .installment-table input {
    background: #111827;
    color: #e5e7eb;
}
</style>

<a href="{{ route('admin.products.index') }}" class="btn btn-secondary mb-3">
    ← Retour
</a>

<div class="edit-card shadow-lg">

    <h3 class="fw-bold text-primary mb-3">Modifier le produit</h3>

    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <!-- PRODUCT INFO -->
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control"
                       value="{{ $product->name }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Prix (XOF)</label>
                <input type="number" step="0.01" name="price" class="form-control"
                       value="{{ $product->price }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control"
                       value="{{ $product->stock }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Catégorie</label>
                <input type="text" name="category_id" class="form-control"
                       value="{{ $product->category_id }}">
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ $product->description }}</textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Tags (séparés par des virgules)</label>
                <input type="text" name="tags" class="form-control"
                       value="{{ $product->tags ? implode(',', $product->tags) : '' }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Image (laisser vide pour garder l’actuelle)</label>
                <input type="file" name="image" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ $product->is_active ? 'selected':'' }}>Actif</option>
                    <option value="0" {{ !$product->is_active ? 'selected':'' }}>Inactif</option>
                </select>
            </div>

        </div>

        <hr class="my-4">

        <!-- INSTALLMENT PLANS -->
        <h4 class="fw-bold text-secondary mb-3">Plans de paiement</h4>

        <table class="table-modern installment-table w-100">
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Mensualité (XOF)</th>
                    <th>Total (XOF)</th>
                </tr>
            </thead>

            <tbody id="installmentRows">
                @foreach($product->installments as $index => $plan)
                <tr>
                    <td>
                        <input type="number" class="form-control"
                               name="installments[{{ $index }}][months]"
                               value="{{ $plan->months }}" required>
                    </td>

                    <td>
                        <input type="number" class="form-control" step="0.01"
                               name="installments[{{ $index }}][monthly_amount]"
                               value="{{ $plan->monthly_amount }}" required>
                    </td>

                    <td>
                        <input type="number" class="form-control" step="0.01"
                               name="installments[{{ $index }}][total_amount]"
                               value="{{ $plan->total_amount }}" required>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ADD NEW ROW BUTTON -->
        <button type="button" class="btn btn-outline-primary mt-3"
                onclick="addInstallmentRow()">
            + Ajouter un plan
        </button>

        <!-- SUBMIT BUTTON -->
        <button class="btn btn-primary mt-4 px-4">Enregistrer</button>

    </form>
</div>

<script>
// Add new installment row
let installmentIndex = {{ $product->installments->count() }};

function addInstallmentRow() {
    const tbody = document.getElementById('installmentRows');

    tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>
                <input type="number" class="form-control"
                       name="installments[${installmentIndex}][months]"
                       required>
            </td>

            <td>
                <input type="number" step="0.01" class="form-control"
                       name="installments[${installmentIndex}][monthly_amount]"
                       required>
            </td>

            <td>
                <input type="number" step="0.01" class="form-control"
                       name="installments[${installmentIndex}][total_amount]"
                       required>
            </td>
        </tr>
    `);

    installmentIndex++;
}
</script>

@endsection
