<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Models\CommunicationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationAdminController extends Controller
{
    public function index(): View
    {
        // Seed default templates if empty
        if (CommunicationTemplate::count() === 0) {
            $this->seedDefaults();
        }

        $templates = CommunicationTemplate::latest()->get();
        $logs = CommunicationLog::latest()->take(25)->get();

        $totalSentCount = CommunicationLog::where('status', 'sent')->count();
        $totalDeliveredCount = CommunicationLog::where('status', 'delivered')->count();
        $totalFailedCount = CommunicationLog::where('status', 'failed')->count();

        return view('admin.communication.index', compact(
            'templates',
            'logs',
            'totalSentCount',
            'totalDeliveredCount',
            'totalFailedCount'
        ));
    }

    public function updateTemplate(Request $request, CommunicationTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'subject'          => 'nullable|string|max:255',
            'content_template' => 'required|string',
            'is_active'        => 'nullable|boolean',
        ]);

        $template->update([
            'subject'          => $validated['subject'] ?? null,
            'content_template' => $validated['content_template'],
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.communication.index')->with('success', "টেমপ্লেট '{$template->name}' সফলভাবে আপডেট হয়েছে।");
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:communication_templates,id',
            'recipient'   => 'required|string',
        ]);

        $template = CommunicationTemplate::findOrFail($validated['template_id']);

        CommunicationLog::create([
            'channel'       => $template->channel,
            'recipient'     => $validated['recipient'],
            'trigger_event' => $template->trigger_event,
            'subject'       => $template->subject ?? 'Idea Prakashan Notification',
            'status'        => 'delivered',
        ]);

        return redirect()->route('admin.communication.index')->with('success', "টেস্ট বার্তা সফলভাবে {$validated['recipient']} ঠিকানায় পাঠানো হয়েছে।");
    }

    private function seedDefaults(): void
    {
        $defaults = [
            [
                'name'             => 'Order Confirmation (Worldwide Email)',
                'trigger_event'    => 'order_placed',
                'channel'          => 'email',
                'subject'          => 'Order Confirmation #{{order_number}} — Idea Prakashan',
                'content_template' => 'Dear {{customer_name}}, thank you for your order #{{order_number}} of amount {{total_amount}}. Track your delivery at {{tracking_url}}.',
                'is_active'        => true,
            ],
            [
                'name'             => 'WhatsApp Live Order & Tracking Update',
                'trigger_event'    => 'order_shipped_whatsapp',
                'channel'          => 'whatsapp',
                'subject'          => null,
                'content_template' => 'Hello {{customer_name}}, your book parcel #{{order_number}} is on the way! Courier tracking: {{courier_tracking_no}}.',
                'is_active'        => true,
            ],
            [
                'name'             => 'Abandoned Cart Recovery (24 Hours)',
                'trigger_event'    => 'abandoned_cart_recovery',
                'channel'          => 'email',
                'subject'          => 'You left books in your cart! Here is a 10% discount promo',
                'content_template' => 'Dear reader, you left {{book_title}} in your cart. Use promo code WORLD10 to get 10% off today: {{checkout_url}}',
                'is_active'        => true,
            ],
            [
                'name'             => 'Digital E-Book Instant License Delivery',
                'trigger_event'    => 'ebook_licensed',
                'channel'          => 'email',
                'subject'          => 'Your E-Book Access is Ready — {{ebook_title}}',
                'content_template' => 'Dear {{customer_name}}, your digital copy of {{ebook_title}} is now unlocked in your library: {{reader_url}}',
                'is_active'        => true,
            ],
        ];

        foreach ($defaults as $d) {
            CommunicationTemplate::create($d);
        }
    }
}
