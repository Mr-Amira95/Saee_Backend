<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends Controller
{
    /**
     * Display the list of WhatsApp templates and sent logs.
     */
    public function index()
    {
        $templates = WhatsAppTemplate::orderBy('event')->get();
        $logs = WhatsAppLog::with('order.clientProfile')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.settings.whatsapp.index', compact('templates', 'logs'));
    }

    /**
     * Update the specified template body.
     */
    public function update(Request $request, WhatsAppTemplate $whatsappTemplate)
    {
        $validated = $request->validate([
            'template_body' => 'required|string',
        ]);

        $whatsappTemplate->update([
            'template_body' => $validated['template_body']
        ]);

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'WhatsApp template updated successfully.');
    }
}
