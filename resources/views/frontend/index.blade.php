<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>আইডিয়া প্রকাশন - ক্যাটালগ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">📚 আইডিয়া প্রকাশন ক্যাটালগ</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($books as $book)
                <div class="border p-4 rounded-lg bg-gray-50">
                    <h3 class="font-bold text-lg text-indigo-700">{{ $book->title }}</h3>
                    <p class="text-sm text-gray-600">মূল্য: ৳{{ $book->price }}</p>
                </div>
            @empty
                <p class="text-gray-500">ডাটাবেজে কোনো বই নেই। প্রথমে ডাটাবেজ মাইগ্রেশন ও সিড সম্পন্ন করুন।</p>
            @endforelse
        </div>
    </div>
</body>
</html>