<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::latest()->paginate(20);
        return view('admin.faqs.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_en' => 'required|string|max:255',
            'question_tk' => 'required|string|max:255',
            'question_ru' => 'required|string|max:255',
            'answer_en'   => 'required|string|max:5000',
            'answer_tk'   => 'required|string|max:5000',
            'answer_ru'   => 'required|string|max:5000',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Faq::create($validated);

        return back()->with('success', __('FAQ created successfully.'));
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question_en' => 'required|string|max:255',
            'question_tk' => 'required|string|max:255',
            'question_ru' => 'required|string|max:255',
            'answer_en'   => 'required|string|max:5000',
            'answer_tk'   => 'required|string|max:5000',
            'answer_ru'   => 'required|string|max:5000',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $faq->update($validated);

        return back()->with('success', __('FAQ updated successfully.'));
    }

    public function toggle(Faq $faq)
    {
        $faq->update(['is_active' => !$faq->is_active]);

        return back()->with('success', __('FAQ status updated.'));
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('success', __('FAQ deleted.'));
    }
}