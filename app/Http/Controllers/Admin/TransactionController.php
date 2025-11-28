<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transactions;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $query = Transactions::with('user');

        if (request()->has('type') && request('type') != '') {
            $query->where('type', request('type'));
        }

        if (request()->has('status') && request('status') != '') {
            $query->where('status', request('status'));
        }

        $transactions = $query->latest()->paginate(10);

        return view('admin.transactions.index', compact('transactions'));
    }
}
