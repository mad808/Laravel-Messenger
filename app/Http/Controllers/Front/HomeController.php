<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $conversations = auth()->user()->conversations()
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }, 'participants'])
            ->latest('updated_at')
            ->get();

        return view('front.home.index', compact('conversations'));
    }
}