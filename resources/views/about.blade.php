@extends('layouts.app')

@section('title', 'আমাদের সম্পর্কে - আইডিয়া প্রকাশন')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6 md:p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 border-b pb-2">আমাদের সম্পর্কে</h1>
        
        <p class="text-gray-600 leading-relaxed mb-6">
            স্বাগতম <strong>আইডিয়া প্রকাশন</strong>-এ। আমরা মানসম্মত মুদ্রিত বই এবং সুরক্ষিত ডিজিটাল ই-বুক পাঠকদের দোরগোড়ায় পৌঁছে দিতে প্রতিশ্রুতিবদ্ধ একটি আধুনিক মাল্টি-ভেন্ডর পাবলিশিং প্ল্যাটফর্ম।
        </p>

        <div class="grid md:grid-cols-2 gap-6 my-8">
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <h3 class="font-bold text-lg text-blue-900 mb-2">আমাদের লক্ষ্য</h3>
                <p class="text-sm text-gray-700">লেখক, প্রকাশনী ও পাঠকদের মধ্যে এক অনন্য সংযোগ তৈরি করা এবং মানসম্মত বই ও ই-বুক পঠন অভিজ্ঞতা নিশ্চিত করা।</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <h3 class="font-bold text-lg text-green-900 mb-2">আমাদের সুবিধা</h3>
                <p class="text-sm text-gray-700">ছাপা বইয়ের সাথে ইন-ব্রাউজার সিকিউর ই-বুক রিডার, আধুনিক রয়্যালটি ট্র্যাকিং এবং দ্রুত ডেলিভারি সুবিধা।</p>
            </div>
        </div>

        <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-3">কেন আইডিয়া প্রকাশন?</h2>
        <ul class="list-disc list-inside space-y-2 text-gray-600">
            <li>একই প্ল্যাটফর্মে মুদ্রিত বই এবং সুরক্ষিত ই-বুক।</li>
            <li>সহজ পেমেন্ট সিস্টেম (বিকাশ, নগদ, কার্ড)।</li>
            <li>পাঠকদের জন্য পার্সোনালাইজড লাইব্রেরি ও ডিভাইস ম্যানেজমেন্ট।</li>
        </ul>
    </div>
</div>
@endsection