<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerTestimonial;
use Illuminate\Http\Request;

class CustomerTestimonialController extends Controller
{
    public function index()
    {
        $testimonials = CustomerTestimonial::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.customer-testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.cms.customer-testimonials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feedback.en' => 'required|string',
            'feedback.ar' => 'required|string',
            'client'      => 'required|string|max:255',
            'sort_order'  => 'required|integer|min:0',
            'status'      => 'required|in:active,inactive',
        ]);

        CustomerTestimonial::create([
            'feedback'   => $validated['feedback'],
            'client'     => $validated['client'],
            'sort_order' => $validated['sort_order'],
            'status'     => $validated['status'],
        ]);

        return redirect()->route('admin.cms.customer-testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function edit(CustomerTestimonial $customerTestimonial)
    {
        return view('admin.cms.customer-testimonials.edit', ['testimonial' => $customerTestimonial]);
    }

    public function update(Request $request, CustomerTestimonial $customerTestimonial)
    {
        $validated = $request->validate([
            'feedback.en' => 'required|string',
            'feedback.ar' => 'required|string',
            'client'      => 'required|string|max:255',
            'sort_order'  => 'required|integer|min:0',
            'status'      => 'required|in:active,inactive',
        ]);

        $customerTestimonial->update([
            'feedback'   => $validated['feedback'],
            'client'     => $validated['client'],
            'sort_order' => $validated['sort_order'],
            'status'     => $validated['status'],
        ]);

        return redirect()->route('admin.cms.customer-testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(CustomerTestimonial $customerTestimonial)
    {
        $customerTestimonial->delete();

        return redirect()->route('admin.cms.customer-testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }
}
