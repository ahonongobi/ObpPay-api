@extends('admin.layout')
<style>
    /* ------------------------- */
/* SEARCH BAR + BUTTON STYLE */
/* ------------------------- */

.product-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

/* Search Container */
.search-container {
    position: relative;
    width: 100%;
    max-width: 420px;
}

/* Icon inside input */
.search-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 1.2rem;
    pointer-events: none;
}

/* Input */
.search-input {
    padding-left: 45px !important;
    height: 46px;
    border-radius: 14px;
    border: 1px solid #d1d5db;
    background: #ffffff;
    font-size: 15px;
    transition: all .25s ease;
}

/* Hover + Focus */
.search-input:hover {
    border-color: #a5b4fc;
}

.search-input:focus {
    border-color: #6366f1;
    box-shadow: 0px 0px 0px 4px rgba(99,102,241,.25);
}

/* DARK MODE */
body.dark-mode .search-input {
    background: #1f2937;
    border: 1px solid #374151;
    color: #e5e7eb;
}

body.dark-mode .search-icon {
    color: #d1d5db;
}

body.dark-mode .search-input::placeholder {
    color: #9ca3af;
}

body.dark-mode .search-input:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 4px rgba(129,140,248,.3);
}

/* Add product button */
.add-product-btn {
    height: 46px;
    padding: 0 20px;
    border-radius: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.add-product-btn i {
    font-size: 1.2rem;
}

/* DARK MODE BUTTON */
body.dark-mode .add-product-btn {
    background: #4f46e5 !important;
    border: none;
    color: #f3f4f6;
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
<h1 class="fw-bold text-primary mb-4">Gestion des produits</h1>

<div class="product-toolbar">

    <!-- Search -->
    <div class="search-container position-relative">
        <i class="bi bi-search search-icon"></i>
        <input type="text"
               id="productSearch"
               class="form-control search-input"
               placeholder="Rechercher un produit..."
               onkeyup="filterProducts()">
    </div>

    <!-- Add product button -->
    <a href="{{ route('admin.marketplace.index') }}"
       class="btn btn-primary shadow-sm add-product-btn">
        <i class="bi bi-plus-circle"></i>
        Ajouter un produit
    </a>

</div>



<div class="table-card shadow-lg mt-3">
    <table class="table-modern" id="productsTable">
        <thead>
            <tr>
                <th>Image</th>
                <th>Produit</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($products as $p)
            <tr>
                <td>
                    <img src="{{ $p->image ? url($p->image) : 'https://via.placeholder.com/60' }}"
                         class="rounded"
                         width="60">
                </td>

                <td>
                    <strong>{{ $p->name }}</strong><br>
                    <small class="text-muted">{{ $p->category->name ?? '—' }}</small>
                </td>

                <td><strong>{{ number_format($p->price) }} XOF</strong></td>

                <td>{{ $p->stock }}</td>

                <td>
                    @if($p->is_active)
                        <span class="badge-modern badge-approved">Actif</span>
                    @else
                        <span class="badge-modern badge-none">Inactif</span>
                    @endif
                </td>

                <td class="d-flex gap-2">
  
                        <a href="{{ route('admin.products.show', $p->id) }}" 
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    <a href="{{ route('admin.products.edit', $p->id) }}" 
                        class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil-square"></i>
                    </a>

                    <form action="{{ route('admin.products.delete', $p->id) }}" method="POST" 
                          onsubmit="return confirm('Supprimer ce produit ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-4">
        {{ $products->links('vendor.pagination.custom') }}
    </div>
</div>


<script>
function filterProducts() {
    let value = document.getElementById("productSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#productsTable tbody tr");

    rows.forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(value) ? "" : "none";
    });
}
</script>

@endsection
