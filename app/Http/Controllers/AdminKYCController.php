<?php

namespace App\Http\Controllers;

use App\Models\Kyc;
use Illuminate\Http\Request;

class AdminKYCController extends Controller
{
    public function index()
    {
        // Un seul KYC par utilisateur (le plus récent)
        $kycs = Kyc::with('user')
            ->selectRaw('MAX(id) as id, user_id')
            ->groupBy('user_id')
            ->get();

        return view('admin.kyc.index', compact('kycs'));
    }

    public function show($id)
    {
        // On récupère une ligne KYC
        $kyc = Kyc::findOrFail($id);

        // On récupère TOUTES les preuves KYC de CE user
        $allKycDocs = Kyc::where('user_id', $kyc->user_id)->get();

        // Formatage selon ton JS "type + path"
        $docs = $allKycDocs->map(function ($item) {
            return [
                'type' => match ($item->type) {
                    'id_card' => 'Carte d’identité',
                    'passport' => 'Passeport',
                    'selfie' => 'Selfie',
                    default => 'Document'
                },
                'path' => $item->file_path,
            ];
        });

        return response()->json([
            'id' => $kyc->id,
            'status' => $kyc->status,
            'user' => $kyc->user,
            'docs' => $docs,
        ]);
    }


    public function approve($id)
    {
        $kyc = Kyc::findOrFail($id);
        $kyc->status = 'approved';
        $kyc->save();

        return response()->json(['success' => true]);
    }

    public function reject($id)
    {
        $kyc = Kyc::findOrFail($id);
        $kyc->status = 'rejected';
        $kyc->save();

        return response()->json(['success' => true]);
    }
}
