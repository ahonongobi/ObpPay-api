<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{

    /**
     * Envoyer une demande de retrait
     */
    public function request(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validate([
            'amount'    => 'required|numeric|min:1000', // min 1000 XOF par ex.
            'method'    => 'required|string|max:50',
            'recipient' => 'required|string|max:191',
        ]);

        // Récupérer le wallet de l'utilisateur
        $wallet = $user->wallet; // suppose relation user->wallet

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => "Aucun wallet trouvé pour cet utilisateur.",
            ], 422);
        }

        // Exemple de frais simple: 1% + 500 XOF
        $feesPercent = 0.01;
        $fixedFee    = 500;

        $fees   = round($data['amount'] * $feesPercent + $fixedFee, 2);
        $total  = $data['amount'] + $fees;

        // Vérifier solde
        if ($wallet->balance < $total) {
            return response()->json([
                'success' => false,
                'message' => "Solde insuffisant pour ce retrait (montant + frais).",
            ], 422);
        }

        // On NE débite PAS encore : juste enregistrer la demande en pending
        $withdraw = Withdrawal::create([
            'user_id'   => $user->id,
            'amount'    => $data['amount'],
            'fees'      => $fees,
            'method'    => $data['method'],
            'recipient' => $data['recipient'],
            'status'    => 'pending',
        ]);

        // Notif simple
        $user->notifications()->create([
            'type'    => 'WITHDRAW_REQUEST_CREATED',
            'title'   => 'Demande de retrait envoyée',
            'message' => "Votre demande de retrait de {$data['amount']} {$wallet->currency} est en attente de validation.",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande de retrait enregistrée.',
            'data'    => $withdraw,
        ], 201);
    }

    /**
     * Historique des retraits de l'utilisateur connecté
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $withdraws = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($withdraws);
    }
}
