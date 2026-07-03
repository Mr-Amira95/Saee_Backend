<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactFormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactSubmissionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:business,contact',
            'name' => 'required|string|max:255',
            'company' => 'required_if:type,business|nullable|string|max:255',
            'monthly_volume' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string',
        ]);

        ContactFormSubmission::create($validated);

        return response()->json(['success' => true], 201);
    }
}
