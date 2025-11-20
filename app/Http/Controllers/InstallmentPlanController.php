<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPlan;
use App\Models\InstallmentPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallmentPlanController extends Controller
{
    //  Get available plans for a product fuck it for fun :)
    public function getPlans($id)
    {
        $plans = InstallmentPlan::where('product_id', $id)
            ->where('is_active', true)
            ->get();

        return response()->json($plans);
    }


    // Start installment purchase
    public function startInstallment(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'plan_id' => 'required|exists:installment_plans,id',
        ]);

        $user = $request->user();
        $plan = InstallmentPlan::find($request->plan_id);
        $wallet = $user->wallet;

        // get user balance from wallet 
        $walletBalance = floatval($wallet->balance);
        $monthlyAmount = floatval($plan->monthly_amount);

        Log::info('User balance: ' . $user->balance);
        // Vérifier solde
        if ($walletBalance < $monthlyAmount) {
            return response()->json([
                "success" => false,
                "message" => "Solde insuffisant pour payer la première tranche.",
                "debug" => [
                    "wallet_balance" => $walletBalance,
                    "monthly_amount" => $monthlyAmount,
                ]
            ], 422);
        }

        DB::transaction(function () use ($wallet, $user, $data, $plan, $monthlyAmount) {

            // Débiter le wallet depuis la table wallets
            $wallet->decrement('balance', $monthlyAmount);

            // Enregistrer l'achat à crédit
            InstallmentPurchase::create([
                'user_id' => $user->id,
                'product_id' => $data['product_id'],
                'plan_id' => $plan->id,
                'total_amount' => $plan->total_amount,
                'monthly_amount' => $monthlyAmount,
                'months' => $plan->months,
                'paid_months' => 1,
                'status' => 'ongoing'
            ]);

            // Notification
            $user->notifications()->create([
                "type" => "INSTALLMENT_STARTED",
                "title" => "Achat à crédit démarré",
                "message" => "Vous avez payé la première tranche du produit."
            ]);
        });

        return response()->json([
            "success" => true,
            "message" => "Première tranche payée avec succès."
        ]);
    }


    // Process monthly installments (cron)
    public function processMonthly()
    {
        $purchases = InstallmentPurchase::where('status', 'ongoing')->get();

        foreach ($purchases as $purchase) {

            $user = User::find($purchase->user_id);

            // Si déjà payé entièrement
            if ($purchase->paid_months >= $purchase->months) {
                $purchase->update(['status' => 'completed']);
                continue;
            }

            // Vérifier solde
            if ($user->balance < $purchase->monthly_amount) {

                $purchase->update(['status' => 'defaulted']);

                $user->notifications()->create([
                    "type" => "INSTALLMENT_FAILED",
                    "title" => "Paiement échoué",
                    "message" => "Votre tranche mensuelle n'a pas pu être débitée.",
                ]);

                continue;
            }

            // Débiter et avancer d'une tranche
            DB::transaction(function () use ($purchase, $user) {
                $user->decrement('balance', $purchase->monthly_amount);

                $purchase->increment('paid_months');

                // Notification
                $user->notifications()->create([
                    "type" => "INSTALLMENT_PAID",
                    "title" => "Tranche payée",
                    "message" => "Votre tranche mensuelle a été payée.",
                ]);
            });

            // Si terminé
            if ($purchase->paid_months == $purchase->months) {
                $purchase->update(['status' => 'completed']);

                $user->notifications()->create([
                    "type" => "INSTALLMENT_COMPLETED",
                    "title" => "Paiement terminé",
                    "message" => "Vous avez entièrement payé votre produit.",
                ]);
            }
        }

        return response()->json([
            "success" => true,
            "message" => "Paiements mensuels traités."
        ]);
    }
}
   


// I LOVE LARAVEL .. shout out to  Gobi :)