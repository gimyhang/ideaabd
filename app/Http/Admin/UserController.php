<?php

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * ব্যবহারকারীদের তালিকা (অ্যাডমিন প্যানেল)
     */
    public function index(Request $request)
    {
        $users = User::latest()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        return response()->json($users);
    }

    /**
     * নতুন ব্যবহারকারী তৈরি (অ্যাডমিন প্যানেল থেকে)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', Password::defaults()],
            'role'     => ['nullable', 'string', Rule::in(['user', 'admin', 'editor'])],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'] ?? 'user',
        ]);

        // সিকিউরিটি অডিট লগ
        Log::channel('audit')->info('New User Created via Admin', [
            'created_user_id' => $user->id,
            'created_by'      => auth()->id(),
            'ip_address'      => $request->ip(),
        ]);

        return response()->json([
            'message' => 'ব্যবহারকারী সফলভাবে তৈরি হয়েছে।',
            'data'    => $user
        ], 201);
    }

    /**
     * নির্দিষ্ট ব্যবহারকারীর বিবরণ
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * ব্যবহারকারীর প্রোফাইল আপডেট
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'role'  => ['nullable', 'string', Rule::in(['user', 'admin', 'editor'])],
        ]);

        $user->update($validated);

        Log::channel('audit')->info('User Profile Updated', [
            'updated_user_id' => $user->id,
            'updated_by'      => auth()->id(),
            'ip_address'      => $request->ip(),
        ]);

        return response()->json([
            'message' => 'প্রোফাইল সফলভাবে আপডেট হয়েছে।',
            'data'    => $user
        ]);
    }

    /**
     * পাসওয়ার্ড আপডেট
     */
    public function updatePassword(Request $request, User $user)
    {
        // নিজের পাসওয়ার্ড পরিবর্তন বনাম অ্যাডমিনের রিসেট সিকিউরিটি
        $rules = [
            'new_password' => ['required', Password::defaults(), 'confirmed'],
        ];

        // ইউজার নিজে নিজের পাসওয়ার্ড পাল্টালে কারেন্ট পাসওয়ার্ড ভ্যালিডেশন বাধ্যতামূলক
        if (auth()->id() === $user->id) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        Log::channel('audit')->info('User Password Changed', [
            'user_id'    => $user->id,
            'changed_by' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।'
        ]);
    }

    /**
     * ব্যবহারকারী ডিলিট
     */
    public function destroy(Request $request, User $user)
    {
        // নিজের একাউন্ট নিজে ডিলিট করা রোধ করতে
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'আপনি নিজের অ্যাকাউন্ট অ্যাডমিন প্যানেল থেকে মুছে ফেলতে পারবেন না।'
            ], 403);
        }

        $deletedUserId = $user->id;
        $user->delete();

        Log::channel('audit')->warning('User Deleted', [
            'deleted_user_id' => $deletedUserId,
            'deleted_by'      => auth()->id(),
            'ip_address'      => $request->ip(),
        ]);

        return response()->json([
            'message' => 'ব্যবহারকারী মুছে ফেলা হয়েছে।'
        ]);
    }
}