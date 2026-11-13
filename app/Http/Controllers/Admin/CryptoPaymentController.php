<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use App\Models\Subscription;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Support\Facades\DB;

class CryptoPaymentController extends Controller
{
    public function index()
    {
        $payments = CryptoPayment::with('user', 'plan')->latest()->paginate(20);
        return view('admin.payments.index', compact('payments'));
    }

    public function approve(CryptoPayment $payment)
    {
        $result = DB::transaction(function () use ($payment) {
            $locked = CryptoPayment::with(['user', 'plan'])
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'pending') {
                return false;
            }

            $locked->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            $plan = $locked->plan;
            $user = $locked->user;

            if ($user && $plan) {
                $existingActive = Subscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->latest('id')
                    ->first();

                $baseDate = ($existingActive && $existingActive->plan_id === $plan->id && $existingActive->ends_at?->isFuture())
                    ? $existingActive->ends_at->copy()
                    : now();

                Subscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->update(['status' => 'expired']);

                $endsAt = $plan->duration_days > 0
                    ? $baseDate->addDays($plan->duration_days)
                    : null;

                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => $endsAt,
                    'status' => 'active',
                ]);

                if (in_array($plan->slug, ['free', 'plus', 'pro'], true)) {
                    $user->update(['plan' => $plan->slug]);
                }

                $user->notify(new AdminBroadcastNotification(
                    __('Payment Approved'),
                    __('Your :plan subscription is now active.', ['plan' => $plan->name])
                ));
            }

            return true;
        });

        if (!$result) {
            return back()->with('error', 'Payment is already processed.');
        }

        return back()->with('success', 'Payment approved and subscription activated.');
    }

    public function reject(CryptoPayment $payment)
    {
        $result = DB::transaction(function () use ($payment) {
            $locked = CryptoPayment::with(['user', 'plan'])
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'pending') {
                return false;
            }

            $locked->update(['status' => 'failed']);

            if ($locked->user) {
                $locked->user->notify(new AdminBroadcastNotification(
                    __('Payment Rejected'),
                    __('Your crypto payment for :plan could not be verified.', ['plan' => $locked->plan?->name ?? '-'])
                ));
            }

            return true;
        });

        if (!$result) {
            return back()->with('error', 'Payment is already processed.');
        }

        return back()->with('success', 'Payment rejected.');
    }
}