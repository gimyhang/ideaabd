@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-2">লেখক হিসেবে যোগ দিন</h1>
            <p class="text-slate-600 mb-8">আমাদের প্ল্যাটফর্মে আপনার লেখা শেয়ার করুন এবং হাজার হাজার পাঠকের কাছে পৌঁছান</p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <ul class="text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('author.store-registration') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block font-semibold mb-2">নাম *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-semibold mb-2">ইমেইল *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-semibold mb-2">জীবনী *</label>
                    <textarea name="bio" required rows="5" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('bio') }}</textarea>
                </div>

                <div>
                    <label class="block font-semibold mb-2">ফোন</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-semibold mb-2">ওয়েবসাইট</label>
                    <input type="url" name="website" value="{{ old('website') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <button type="submit" class="w-full btn-primary py-3 font-bold">নিবন্ধন সম্পন্ন করুন</button>
            </form>

            <p class="text-center text-slate-600 mt-8">ইতিমধ্যে লেখক? <a href="{{ route('author.index') }}" class="text-brand-600 hover:text-brand-700 font-semibold">সব লেখক দেখুন</a></p>
        </div>
    </div>
</div>
@endsection
