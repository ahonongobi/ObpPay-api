<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $q = Product::query()->where('is_active', true);

        // Filtre catégorie
        if ($request->category_id) {
            $q->where('category_id', $request->category_id);
        }

        // Recherche texte
        if ($request->search) {
            $q->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json(
            $q->orderBy('id', 'desc')->get()
        );
    }

    public function byCategory($id)
    {
        $products = Product::where('category_id', $id)
            ->where('is_active', true)
            ->get();

        return response()->json($products);
    }
}
