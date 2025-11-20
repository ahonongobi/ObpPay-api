<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function payNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);
        $wallet = $user->wallet;

        // (optionnel) vérifier si déjà acheté
        $already = Purchase::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($already) {
            return response()->json([
                "success" => false,
                "message" => "Vous avez déjà acheté ce produit.",
            ], 409);
        }

        // Vérifier solde wallet
        if ($wallet->balance < $product->price) {
            return response()->json([
                "success" => false,
                "message" => "Solde insuffisant pour effectuer cet achat.",
            ], 422);
        }

        DB::transaction(function () use ($user, $wallet, $product) {

            // 🔹 1. Débiter le wallet
            $wallet->decrement('balance', $product->price);

            // 🔹 2. Enregistrer l’achat
            Purchase::create([
                'user_id'    => $user->id,
                'product_id' => $product->id,
                'amount'     => $product->price,
                'status'     => 'completed',
            ]);

            // 🔹 3. Enregistrer la transaction (même structure que deposit)
            Transactions::create([
                'user_id'     => $user->id,
                'type'        => 'purchase',
                'amount'      => $product->price,
                'currency'    => $wallet->currency,
                'description' => 'Achat du produit : ' . $product->name,
                'status'      => 'completed',
                'meta'        => [
                    'product_id' => $product->id,
                    'mode'       => 'full_payment',
                ],
            ]);

            // 🔹 4. Notification
            $user->notifications()->create([
                "type"    => "PURCHASE_COMPLETED",
                "title"   => "Achat effectué",
                "message" => "Votre achat de « {$product->name} » a été effectué avec succès.",
            ]);
        });

        return response()->json([
            "success" => true,
            "message" => "Paiement effectué avec succès.",
        ]);
    }
}
