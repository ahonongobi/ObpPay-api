<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPlan;
use App\Models\Product;
use Illuminate\Http\Request;

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
            'image' => 'nullable|image',
        ]);

        // Save product
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if ($validated['tags'] ?? false) {
            $validated['tags'] = json_encode(array_map('trim', explode(',', $validated['tags'])));
        }

        $validated['is_active'] = $request->has('is_active');

        $product = Product::create($validated);

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
