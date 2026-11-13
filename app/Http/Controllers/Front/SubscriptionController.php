<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\CryptoPayment;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = Plan::all();
        return view('front.subscriptions.index', compact('plans'));
    }

    public function payWithCrypto(Request $request, Plan $plan)
    {
        $request->validate([
            'tx_hash' => 'required|string|unique:crypto_payments,tx_hash',
        ]);

        if (!$plan->accepts_crypto) {
            return back()->with('error', 'This plan does not accept crypto payments.');
        }

        CryptoPayment::create([
            'user_id' => auth()->id(),
            'plan_id' => $plan->id,
            'tx_hash' => $request->tx_hash,
            'network' => 'BEP-20',
            'currency' => 'USDT',
            'amount' => $plan->price,
            'wallet_address' => $plan->bep20_wallet_address,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Payment submitted. Waiting for admin approval.');
    }
}