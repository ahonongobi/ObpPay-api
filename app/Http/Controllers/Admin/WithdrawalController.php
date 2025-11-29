<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LogicException;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('user')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view('admin.withdraw.index', compact('withdrawals'));
    }

    public function show($id)
    {
        $withdrawal = Withdrawal::with('user')->findOrFail($id);

        return view('admin.withdraw.show', compact('withdrawal'));
    }

    public function approve(Request $request, $id)
    {
        $withdrawal = Withdrawal::with('user')->findOrFail($id);

        // déjà traité ?
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $user = $withdrawal->user;

        // Débit wallet
        if ($user->wallet->balance < $withdrawal->amount) {
            return back()->with('error', 'Solde insuffisant pour approuver ce retrait.');
        }

        $user->wallet->balance -= $withdrawal->amount;
        $user->wallet->save();

        // Set status
        $withdrawal->status = "approved";
        $withdrawal->admin_notes = $request->notes;
        $withdrawal->save();

        admin_log(
            'withdrawal',
            "Retrait approuvé pour l’utilisateur #{$withdrawal->user_id}",
            ['amount' => $withdrawal->amount]
        );



        // FIREBASE NOTIFICATION
        // 🔵 Envoyer la notification Firebase
        if ($user->fcm_token) {
            try {
                $firebase = new \App\Services\FirebaseService();

                $firebase->sendToToken(
                    $user->fcm_token,
                    "Retrait approuvé 💸",
                    "Votre retrait de {$withdrawal->amount} XOF a été approuvé.",
                    [
                        "screen"  => "withdrawals",
                        "amount"  => $withdrawal->amount,
                        "status"  => "approved"
                    ]
                );
            } catch (\Exception $e) {
                Log::error("FCM WITHDRAW APPROVE ERROR: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Retrait approuvé avec succès.');
    }

    public function reject(Request $request, $id)
    {
        $withdrawal = Withdrawal::with('user')->findOrFail($id);
        $user = $withdrawal->user;
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $withdrawal->status = "rejected";
        $withdrawal->admin_notes = $request->notes;
        $withdrawal->save();


        // FIREBASE
        // Notification Firebase
        if ($user->fcm_token) {
            try {
                $firebase = new \App\Services\FirebaseService();

                $firebase->sendToToken(
                    $user->fcm_token,
                    "Retrait rejeté ❌",
                    "Votre demande de retrait a été rejetée.",
                    [
                        "screen" => "withdrawals",
                        "status" => "rejected"
                    ]
                );
            } catch (\Exception $e) {
                Log::error("FCM WITHDRAW REJECT ERROR: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Retrait rejeté.');
    }
}
