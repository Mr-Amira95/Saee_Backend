<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        return view('admin.cms.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.cms.faqs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question.en' => 'required|string|max:255',
            'question.ar' => 'required|string|max:255',
            'answer.en'   => 'required|string',
            'answer.ar'   => 'required|string',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        Faq::create([
            'question'   => $validated['question'],
            'answer'     => $validated['answer'],
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.cms.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question.en' => 'required|string|max:255',
            'question.ar' => 'required|string|max:255',
            'answer.en'   => 'required|string',
            'answer.ar'   => 'required|string',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $faq->update([
            'question'   => $validated['question'],
            'answer'     => $validated['answer'],
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.cms.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }
}
