<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        $wallet = $request->user()->wallet;
        return response()->json([
            'balance'  => $wallet->balance,
            'currency' => $wallet->currency,
            'obp_id'   => $request->user()->obp_id,
        ]);
    }

    public function transactions(Request $request)
    {
        $tx = $request->user()
            ->transactions()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json($tx);
    }

    public function deposit(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'source' => 'nullable|string',
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        DB::transaction(function () use ($wallet, $user, $data) {
            $wallet->increment('balance', $data['amount']);

            Transactions::create([
                'user_id'    => $user->id,
                'type'       => 'deposit',
                'amount'     => $data['amount'],
                'currency'   => $wallet->currency,
                'description' => $data['source'] ?? 'Recharge',
                'status'     => 'completed',
                'meta'       => null,
            ]);
        });

        return response()->json([
            'message' => 'Dépôt effectué.',
            'balance' => $wallet->fresh()->balance,
        ]);
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'to_obp_id' => 'required|string|exists:users,obp_id',
            'amount'    => 'required|numeric|min:0.01',
        ]);

        $fromUser = $request->user();
        $toUser   = \App\Models\User::where('obp_id', $data['to_obp_id'])->firstOrFail();

        if ($fromUser->id === $toUser->id) {
            return response()->json(['message' => 'Transfert vers vous-même interdit.'], 422);
        }

        $fromWallet = $fromUser->wallet;
        $toWallet   = $toUser->wallet;

        if ($fromWallet->balance < $data['amount']) {
            return response()->json(['message' => 'Solde insuffisant.'], 422);
        }

        DB::transaction(function () use ($fromWallet, $toWallet, $fromUser, $toUser, $data) {
            $fromWallet->decrement('balance', $data['amount']);
            $toWallet->increment('balance', $data['amount']);

            Transactions::create([
                'user_id'  => $fromUser->id,
                'type'     => 'transfer_out',
                'amount'   => $data['amount'],
                'currency' => $fromWallet->currency,
                'description' => 'Transfert vers ' . $toUser->obp_id,
                'status'   => 'completed',
                'meta'     => ['to' => $toUser->obp_id],
            ]);

            Transactions::create([
                'user_id'  => $toUser->id,
                'type'     => 'transfer_in',
                'amount'   => $data['amount'],
                'currency' => $toWallet->currency,
                'description' => 'Transfert reçu de ' . $fromUser->obp_id,
                'status'   => 'completed',
                'meta'     => ['from' => $fromUser->obp_id],
            ]);
        });

        return response()->json(['message' => 'Transfert effectué.']);
    }
}
