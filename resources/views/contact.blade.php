@extends('layouts.app')

@section('title', 'যোগাযোগ - আইডিয়া প্রকাশন')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6 md:p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">যোগাযোগ করুন</h1>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- ফর্ম -->
            <div>
                <form action="#" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shakil Masud</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ইমেইল</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিষয়</label>
                        <input type="text" name="subject" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বার্তা</label>
                        <textarea name="message" rows="4" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition">বার্তা পাঠান</button>
                </form>
            </div>

            <!-- যোগাযোগের তথ্য -->
            <div class="bg-gray-50 p-6 rounded-lg space-y-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">অফিসিয়াল ঠিকানা</h3>
                <p class="text-gray-600"><strong>ঠিকানা:</strong> আইডিয়া প্রকাশন, সেন্ট্রাল রোড, রংপুর / ঢাকা, বাংলাদেশ</p>
                <p class="text-gray-600"><strong>ফোন:</strong> +8801726976982</p>
                                <p class="text-gray-600"><strong>ইমেইল:</strong> ideapbd@gmail.com</p>
                <p class="text-gray-600"><strong>সময়সূচী:</strong> শনিবার - বৃহস্পতিবার (সকাল ৯:০০ - রাত ১০:০০)</p>
            </div>
        </div>
    </div>
</div>
@endsection