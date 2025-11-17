<?php

namespace App\Http\Controllers;

use App\Models\Loanrequests;
use Illuminate\Http\Request;

class LoanrequestsController extends Controller
{
    public function eligibility(Request $request)
    {

        // get user score from user table and check if its geather than 50, then eligible

        $user_score = $request->user()->score;
        if ($user_score > 50) {
            return response()->json([
                'eligibility' => 1,
                'message' => 'Votre score utilisateur vous rend éligible pour un prêt.',
            ]);
        }
        else {
            return response()->json([
                'eligibility' => 0,
                'message' => 'Votre score utilisateur est insuffisant pour un prêt.',
            ]);
        }

        // eligible si solde >= 50 000
        //$score = min(1, $wallet->balance / 50000);

        //return response()->json([
         //   'eligibility' => $score, // 0..1
        //    'message' => $score >= 0.5 ? 'Vous êtes éligible pour un prêt substantiel.'
         //       : 'Éligibilité faible.',
        //]);
    }

    public function requestLoan(Request $request)
    {
        $data = $request->validate([
            'category'        => 'required|string',
            'custom_category' => 'nullable|string',
            'amount'          => 'required|numeric|min:1',
            'notes'           => 'nullable|string',
        ]);

        $loan = Loanrequests::create([
            'user_id'        => $request->user()->id,
            'category'       => $data['category'],
            'custom_category' => $data['custom_category'] ?? null,
            'amount'         => $data['amount'],
            'status'         => 'pending',
            'notes'          => $data['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Demande de prêt envoyée.',
            'loan'    => $loan,
        ], 201);
    }
}
