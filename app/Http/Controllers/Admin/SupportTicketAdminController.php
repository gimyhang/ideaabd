<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $tickets = SupportTicket::with(['user', 'messages'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        $openTicketsCount = SupportTicket::where('status', 'open')->count();
        $inProgressCount = SupportTicket::where('status', 'in_progress')->count();
        $resolvedCount = SupportTicket::where('status', 'resolved')->count();

        return view('admin.tickets.index', compact(
            'tickets',
            'openTicketsCount',
            'inProgressCount',
            'resolvedCount',
            'status'
        ));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'messages.user']);
        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'status'  => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        TicketMessage::create([
            'ticket_id'      => $ticket->id,
            'user_id'        => auth()->id(),
            'message'        => $validated['message'],
            'is_admin_reply' => true,
        ]);

        if (!empty($validated['status'])) {
            $ticket->update(['status' => $validated['status']]);
        }

        return redirect()->route('admin.tickets.show', $ticket->id)->with('success', 'গ্রাহকের টিকিটে উত্তর পাঠানো হয়েছে।');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'টিকিট স্ট্যাটাস পরিবর্তন করা হয়েছে।');
    }
}
