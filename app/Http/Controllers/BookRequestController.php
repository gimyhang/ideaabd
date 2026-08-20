<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class BookRequestController extends Controller
{
    /**
     * Store request submitted from frontend by customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name'   => 'nullable|string|max:255',
            'customer_phone'  => 'required|string|max:50',
            'customer_email'  => 'nullable|email|max:255',
            'book_title'      => 'required|string|max:255',
            'author_name'     => 'nullable|string|max:255',
            'edition'         => 'nullable|string|max:100',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        BookRequest::create($validated);

        return back()->with('success', 'আপনার বইয়ের রিকোয়েস্টটি সফলভাবে জমা নেওয়া হয়েছে! আমরা দ্রুত বইটি সংগ্রহের চেষ্টা করব।');
    }

    /**
     * Store new request manually from admin dashboard.
     */
    public function storeAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name'   => 'nullable|string|max:255',
            'customer_phone'  => 'required|string|max:50',
            'customer_email'  => 'nullable|email|max:255',
            'book_title'      => 'required|string|max:255',
            'author_name'     => 'nullable|string|max:255',
            'edition'         => 'nullable|string|max:100',
            'additional_info' => 'nullable|string|max:1000',
            'admin_notes'     => 'nullable|string|max:1000',
            'status'          => 'required|in:pending,processing,available,closed',
        ]);

        BookRequest::create($validated);

        return back()->with('success', 'নতুন বইয়ের রিকোয়েস্ট সফলভাবে এন্ট্রি করা হয়েছে!');
    }

    /**
     * Display all book requests with live search, filters, analytics & pagination.
     */
    public function index(Request $request): View
    {
        $search   = trim((string)$request->input('search', ''));
        $status   = trim((string)$request->input('status', ''));
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $perPage  = in_array((int)$request->input('per_page'), [10, 25, 50, 100], true) ? (int)$request->input('per_page') : 25;

        $query = BookRequest::query()
            ->search($search)
            ->status($status)
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest();

        $requests = $query->paginate($perPage)->withQueryString();

        // Calculate real-time metrics
        $stats = [
            'total'      => BookRequest::count(),
            'pending'    => BookRequest::where('status', 'pending')->count(),
            'processing' => BookRequest::where('status', 'processing')->count(),
            'available'  => BookRequest::where('status', 'available')->count(),
            'closed'     => BookRequest::where('status', 'closed')->count(),
        ];

        return view('admin.book-requests', compact('requests', 'stats', 'search', 'status', 'dateFrom', 'dateTo', 'perPage'));
    }

    /**
     * Update request status (supports both AJAX and form submit).
     */
    public function updateStatus(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,processing,available,closed']);
        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->update(['status' => $request->status]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => "স্ট্যাটাস সফলভাবে '{$bookRequest->status_label}' করা হয়েছে।",
                'status'       => $bookRequest->status,
                'status_label' => $bookRequest->status_label,
                'badge_class'  => $bookRequest->status_badge_class,
                'icon'         => $bookRequest->status_icon,
            ]);
        }

        return back()->with('success', "রিকোয়েস্ট #{$bookRequest->id}-এর স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।");
    }

    /**
     * Update admin internal notes for the request.
     */
    public function updateNotes(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate(['admin_notes' => 'nullable|string|max:2000']);
        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->update(['admin_notes' => $request->admin_notes]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'অ্যাডমিন নোট সফলভাবে সংরক্ষিত হয়েছে।',
                'notes'   => $bookRequest->admin_notes,
            ]);
        }

        return back()->with('success', 'অ্যাডমিন নোট সংরক্ষিত হয়েছে।');
    }

    /**
     * Delete a single book request.
     */
    public function destroy($id): RedirectResponse
    {
        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->delete();

        return back()->with('success', 'বইয়ের রিকোয়েস্টটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Bulk action (Bulk delete or bulk status update).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $ids = $request->input('selected_ids', []);
        $action = $request->input('bulk_action');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'অনুগ্রহ করে কমপক্ষে একটি রিকোয়েস্ট নির্বাচন করুন।');
        }

        if ($action === 'delete') {
            BookRequest::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . 'টি রিকোয়েস্ট সফলভাবে মুছে ফেলা হয়েছে।');
        }

        if (in_array($action, ['pending', 'processing', 'available', 'closed'], true)) {
            BookRequest::whereIn('id', $ids)->update(['status' => $action]);
            return back()->with('success', count($ids) . "টি রিকোয়েস্টের স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।");
        }

        return back()->with('error', 'সঠিক অ্যাকশন নির্বাচন করুন।');
    }
}
