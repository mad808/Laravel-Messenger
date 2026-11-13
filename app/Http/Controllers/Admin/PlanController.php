<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('price')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:0',
            'bep20_wallet_address' => 'nullable|string|max:255',
            'max_file_size_mb' => 'required|integer|min:1',
            'max_story_duration_sec' => 'required|integer|min:1',
        ]);

        $validated['bep20_wallet_address'] = $request->filled('bep20_wallet_address')
            ? trim((string) $request->bep20_wallet_address)
            : null;

        $validated['accepts_crypto']        = $request->boolean('accepts_crypto');
        $validated['can_hide_profile']      = $request->boolean('can_hide_profile');
        $validated['can_block_screenshots'] = $request->boolean('can_block_screenshots');
        $validated['has_anonymous_mode']    = $request->boolean('has_anonymous_mode');
        $validated['can_set_profile_music'] = $request->boolean('can_set_profile_music');

        if ($validated['accepts_crypto'] && empty($validated['bep20_wallet_address'])) {
            return back()
                ->withInput()
                ->withErrors(['bep20_wallet_address' => __('BEP-20 wallet address is required when crypto payments are enabled.')]);
        }

        $plan->update($validated);

        return back()->with('success', 'Plan updated successfully.');
    }
}