<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Public order tracking. Ordering needs no verification; this is where it happens:
 * the order number (sent by SMS to the buyer) plus the phone used on the order must match.
 */
class OrderTrackingController extends Controller
{
    public function show()
    {
        return view('frontend.pages.track-order');
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:40'],
            'phone'        => ['required', 'string', 'max:25'],
        ], [
            'order_number.required' => 'অর্ডার নম্বর দিন।',
            'phone.required'        => 'মোবাইল নম্বর দিন।',
        ]);

        $phone = self::normalizePhone($data['phone']);
        $number = ltrim(trim($data['order_number']), '#');

        $order = Order::where('order_number', $number)->first();

        // same answer for "no such order" and "wrong phone": nobody can probe which order numbers exist
        if (!$order || self::normalizePhone((string) $order->customer_phone) !== $phone) {
            throw ValidationException::withMessages(['order_number' => 'এই অর্ডার নম্বর ও মোবাইল নম্বরের মিল পাওয়া যায়নি। এসএমএসে পাওয়া নম্বরটি আবার দেখুন।']);
        }

        $user = $request->user();
        $needsVerification = $user && $user->isCustomer() && (!$user->isPhoneVerified() || !$user->isEmailVerified());

        return view('frontend.pages.track-order', [
            'order' => $order,
            'query' => ['order_number' => $number, 'phone' => $data['phone']],
            'needsVerification' => $needsVerification,
        ]);
    }

    public static function normalizePhone(string $value): string
    {
        $v = strtr($value, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $v = preg_replace('/[\s\-().]+/', '', $v);

        return preg_replace('/^(\+?88)(?=01)/', '', $v);
    }
}
