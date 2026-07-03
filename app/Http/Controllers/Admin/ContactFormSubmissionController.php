<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactFormSubmission;
use Illuminate\Http\Request;

class ContactFormSubmissionController extends Controller
{
    public function index()
    {
        $submissions = ContactFormSubmission::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.cms.contact-submissions.index', compact('submissions'));
    }

    public function show(ContactFormSubmission $contactFormSubmission)
    {
        $submission = $contactFormSubmission;

        return view('admin.cms.contact-submissions.show', compact('submission'));
    }

    public function updateStatus(Request $request, ContactFormSubmission $contactFormSubmission)
    {
        $data = $request->validate([
            'status' => 'required|in:new,contacted,closed',
        ]);

        $contactFormSubmission->update(['status' => $data['status']]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(ContactFormSubmission $contactFormSubmission)
    {
        $contactFormSubmission->delete();

        return redirect()->route('admin.cms.contact-submissions.index')
            ->with('success', 'Submission deleted.');
    }
}
