<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPlan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /* ---------------------------------------------------
        1. LISTE DES PRODUITS
    --------------------------------------------------- */
    public function index()
    {
        $products = Product::latest()->paginate(10);

        // 
        
        return view('admin.products.index', compact('products'));
    }


    /* ---------------------------------------------------
        2. PAGE CREATION
    --------------------------------------------------- */
    public function create()
    {
        return view('admin.products.create');
    }


    /* ---------------------------------------------------
        3. ENREGISTRER NOUVEAU PRODUIT
    --------------------------------------------------- */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock'       => 'required|integer|min:0',
            'tags'        => 'nullable|string',
            'image'       => 'image|max:2048',
            'is_active'   => 'required|boolean',

            // Installments
            'installments.*.months'         => 'nullable|integer|min:1',
            'installments.*.monthly_amount' => 'nullable|numeric|min:0',
            'installments.*.total_amount'   => 'nullable|numeric|min:0',
        ]);

        // Upload image if exists
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Convert tags to JSON
        $tags = $request->tags ? explode(',', $request->tags) : null;

        // Create product
        $product = Product::create([
            'name'        => $request->name,
            'category_id' => null, // optional for now
            'description' => $request->description,
            'price'       => $request->price,
            'currency'    => 'XOF',
            'image'       => $imagePath,
            'stock'       => $request->stock,
            'tags'        => $tags,
            'is_active'   => $request->is_active,
        ]);

        // Save installment plans
        if ($request->installments) {
            foreach ($request->installments as $i) {
                if ($i['months'] && $i['monthly_amount']) {
                    InstallmentPlan::create([
                        'product_id'     => $product->id,
                        'months'         => $i['months'],
                        'monthly_amount' => $i['monthly_amount'],
                        'total_amount'   => $i['total_amount'],
                        'is_active'      => true,
                    ]);
                }
            }
        }

        $user = Auth::user();

        admin_log(
            'product',
            "Produit #{$product->id} ajouté par {$user->name} (ID: {$user->id})",
            [
                'price' => $request->price,
                'stock' => $request->stock,
                'is_active' => $request->is_active
            ]
        );


        return redirect()->route('admin.products.index')
            ->with('success', 'Produit ajouté avec succès.');
    }



    /* ---------------------------------------------------
        4. PAGE EDITION
    --------------------------------------------------- */
    public function edit($id)
    {
        $product = Product::with('installments')->findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }


    /* ---------------------------------------------------
        5. UPDATE PRODUIT
    --------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $product = Product::with('installments')->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock'       => 'required|integer|min:0',
            'tags'        => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'required|boolean',

            'installments.*.months'         => 'nullable|integer|min:1',
            'installments.*.monthly_amount' => 'nullable|numeric|min:0',
            'installments.*.total_amount'   => 'nullable|numeric|min:0',
        ]);

        // Upload image (if new one)
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->name        = $request->name;
        $product->description = $request->description;
        $product->price       = $request->price;
        $product->stock       = $request->stock;
        $product->tags        = $request->tags ? explode(',', $request->tags) : null;
        $product->is_active   = $request->is_active;

        $product->save();

        // Delete old installments
        InstallmentPlan::where('product_id', $product->id)->delete();

        // Re-create installments
        if ($request->installments) {
            foreach ($request->installments as $i) {
                if ($i['months'] && $i['monthly_amount']) {
                    InstallmentPlan::create([
                        'product_id'     => $product->id,
                        'months'         => $i['months'],
                        'monthly_amount' => $i['monthly_amount'],
                        'total_amount'   => $i['total_amount'],
                        'is_active'      => true,
                    ]);
                }
            }
        }
        admin_log(
            'product',
            "Produit #{$product->id} mis à jour",
            ['price' => $request->price]
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }



    /* ---------------------------------------------------
        6. DELETE PRODUIT
    --------------------------------------------------- */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Delete product
        $product->delete();

        admin_log(
            'product',
            "Produit #{$product->id} supprimé",
            ['price' => $product->price]
        );


        return back()->with('success', 'Produit supprimé.');
    }


    public function show($id)
    {
        $product = Product::with('installments')->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }
}
