<?php

namespace App\Http\Controllers\SubAdmin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        $bills = Bill::where('seller_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total'   => Bill::where('seller_id', auth()->id())->count(),
            'paid'    => Bill::where('seller_id', auth()->id())->where('payment_status', 'paid')->count(),
            'unpaid'  => Bill::where('seller_id', auth()->id())->where('payment_status', 'unpaid')->count(),
            'revenue' => Bill::where('seller_id', auth()->id())->where('payment_status', 'paid')->sum('total'),
        ];

        return view('subadmin.billing.index', compact('bills', 'stats'));
    }

    public function create()
    {
        return view('subadmin.billing.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'nullable|string|max:20',
            'customer_email'   => 'nullable|email',
            'items'            => 'required|array|min:1',
            'items.*.title'    => 'required|string',
            'items.*.qty'      => 'required|integer|min:1',
            'items.*.price'    => 'required|numeric|min:0',
            'discount'         => 'nullable|numeric|min:0',
            'payment_method'   => 'required|in:cash,bkash,nagad,card',
            'payment_status'   => 'required|in:unpaid,paid,partial',
            'notes'            => 'nullable|string',
        ]);

        $subtotal = collect($data['items'])
            ->sum(fn($i) => $i['qty'] * $i['price']);

        $bill = Bill::create([
            'seller_id'      => auth()->id(),
            'customer_name'  => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'items'          => $data['items'],
            'subtotal'       => $subtotal,
            'discount'       => $data['discount'] ?? 0,
            'tax'            => 0,
            'total'          => $subtotal - ($data['discount'] ?? 0),
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_status'],
            'notes'          => $data['notes'] ?? null,
        ]);

        return redirect()->route('subadmin.bills.show', $bill)
            ->with('success', "বিল #{$bill->bill_no} তৈরি হয়েছে।");
    }

    public function show(Bill $bill)
    {
        // Sub-admin can only view own bills; admin sees all
        if (!auth()->user()->isAdmin()) {
            abort_unless($bill->seller_id === auth()->id(), 403);
        }
        return view('subadmin.billing.show', compact('bill'));
    }

    public function sellerAccounts()
    {
        // Sub-admin: see all seller stats
        $sellers = User::where('role', User::ROLE_SELLER)
            ->withCount('bills')
            ->with(['bills' => fn($q) => $q->select('seller_id', DB::raw('SUM(total) as revenue'))->groupBy('seller_id')])
            ->paginate(20);

        return view('subadmin.seller-accounts', compact('sellers'));
    }
}
