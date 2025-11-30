<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loanrequests;
use App\Models\Wallet;
use FirebaseService;
use Illuminate\Http\Request;

class LoanRequestController extends Controller
{
    public function index()
    {

        // going to the index page for laon that user have requested
        $loans = Loanrequests::with('user')->orderBy('id', 'desc')->paginate(10);
        return view('admin.loan.index', compact('loans'));
    }

    public function show($id)
    {

        // here we show details of a particular loan request
        $loan = Loanrequests::with('user')->findOrFail($id);

        // Eligibility = score > 50 (for now)
        $eligibility = $loan->user->score >= 50;

        return view('admin.loan.show', compact('loan', 'eligibility'));
    }

    public function approve($id, FirebaseService $firebase)
    {

        // her we approve loan or aid request
        $loan = Loanrequests::with('user')->findOrFail($id);
        $loan->status = "approved";
        $loan->save();

        // credit user balance
        $wallet = Wallet::firstOrCreate(["user_id" => $loan->user_id]);
        $wallet->balance += $loan->amount;
        $wallet->save();



        // FIREBASE notification
        if ($loan->user->fcm_token) {
            $firebase->sendToToken(
                $loan->user->fcm_token,
                "Aide approuvée",
                "Votre demande d’aide de {$loan->amount} XOF a été approuvée.",
                [
                    "screen" => "loan",
                    "loan_id" => $loan->id
                ]
            );
        }

        return back()->with('success', 'Demande d’aide approuvée et montant crédité au portefeuille de l’utilisateur.');
    }

    public function reject($id, FirebaseService $firebase)
    {
        // here we reject loan or aid request
        $loan = Loanrequests::with('user')->findOrFail($id);
        $loan->status = "rejected";
        $loan->save();


        admin_log(
            'loan',
            "Prêt #{$loan->id} rejeté",
            ['amount' => $loan->amount]
        );

        if ($loan->user->fcm_token) {
            $firebase->sendToToken(
                $loan->user->fcm_token,
                "Aide refusée",
                "Votre demande d’aide a été refusée.",
                ["screen" => "loan"]
            );
        }

        return back()->with('success', 'Demande d’aide refusée.');
    }
}
