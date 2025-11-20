<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Liste des retraits (pour l'admin)
     */
    public function index(Request $request)
    {
        $status = $request->query('status'); // optional filter

        $query = Withdrawal::with('user')->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        return response()->json($query->paginate(30));
    }

    /**
     * Approuver et exécuter un retrait
     */
    public function approve(Request $request, $id)
    {
        $data = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        /** @var Withdrawal $withdraw */
        $withdraw = Withdrawal::with('user')->findOrFail($id);

        if (!$withdraw->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande n’est pas en attente.',
            ], 422);
        }

        $user   = $withdraw->user;
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => "Wallet introuvable pour l’utilisateur.",
            ], 422);
        }

        $total = $withdraw->amount + $withdraw->fees;

        if ($wallet->balance < $total) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant dans le wallet au moment de la validation.',
            ], 422);
        }

        DB::transaction(function () use ($withdraw, $wallet, $user, $total, $data) {

            // 1. Marquer comme approved (optionnel si tu veux step intermédiaire)
            $withdraw->status      = 'approved';
            $withdraw->admin_notes = $data['admin_notes'] ?? null;
            $withdraw->save();

            // 2. (TODO) Appeler API opérateur mobile / banque ici.
            // Si succès:
            //    -> on continue
            // Si échec: throw exception et rollback

            // 3. Débiter le wallet
            $wallet->decrement('balance', $total);

            // 4. Créer une transaction
            Transactions::create([
                'user_id'     => $user->id,
                'type'        => 'withdrawal',
                'amount'      => $withdraw->amount,
                'currency'    => $wallet->currency,
                'description' => "Retrait via {$withdraw->method} vers {$withdraw->recipient}",
                'status'      => 'completed',
                'meta'        => json_encode([
                    'withdraw_id' => $withdraw->id,
                    'fees'        => $withdraw->fees,
                ]),
            ]);

            // 5. Marquer retrait comme completed
            $withdraw->status = 'completed';
            $withdraw->save();

            // 6. Notification user
            $user->notifications()->create([
                'type'    => 'WITHDRAW_COMPLETED',
                'title'   => 'Retrait effectué',
                'message' => "Votre retrait de {$withdraw->amount} {$wallet->currency} a été exécuté.",
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Retrait approuvé et exécuté.',
        ]);
    }

    /**
     * Rejeter un retrait
     */
    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        /** @var Withdrawal $withdraw */
        $withdraw = Withdrawal::with('user')->findOrFail($id);

        if (!$withdraw->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande n’est pas en attente.',
            ], 422);
        }

        $withdraw->status      = 'rejected';
        $withdraw->admin_notes = $data['reason'];
        $withdraw->save();

        $wallet = optional($withdraw->user)->wallet;

        // Notif user
        if ($withdraw->user) {
            $withdraw->user->notifications()->create([
                'type'    => 'WITHDRAW_REJECTED',
                'title'   => 'Retrait refusé',
                'message' => "Votre demande de retrait de {$withdraw->amount} {$wallet?->currency} a été refusée : {$data['reason']}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande de retrait rejetée.',
        ]);
    }
}
