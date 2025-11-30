<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPlan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index()
    {

        // get all catgories and show in marketplace index page
        $categories = \App\Models\Categories::all();
        return view('admin.marketplace.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable',
            'tags' => 'nullable|string',
            'image' => 'image',
        ]);

        // Save product
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = "/storage/" . $path;   // IMPORTANT
        }

        // TAGS
        if (!empty($validated['tags'])) {
            $validated['tags'] = json_encode(array_map('trim', explode(',', $validated['tags'])));
        }


        $validated['is_active'] = $request->has('is_active');

        $product = Product::create($validated);

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


        // Save installment plans
        if ($request->plans) {
            foreach ($request->plans as $plan) {
                InstallmentPlan::create([
                    'product_id' => $product->id,
                    'months' => $plan['months'],
                    'monthly_amount' => $plan['monthly_amount'],
                    'total_amount' => $plan['total_amount'],
                    'is_active' => isset($plan['is_active']),
                ]);
            }
        }


        return back()->with('success', 'Produit et plans créés avec succès');
        //return redirect()->route('admin.products.index')
         //   ->with('success', 'Produit et plans créés avec succès');
    }
}
