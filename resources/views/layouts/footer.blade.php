<footer class="bg-gray-50 text-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Newsletter / CTA -->
        <div class="bg-white rounded-lg shadow-sm p-6 md:flex md:items-center md:justify-between">
            <div class="md:flex-1">
                <h3 class="text-lg font-semibold text-gray-800">নতুন বই ও অফার পেতে সাবস্ক্রাইব করুন</h3>
                <p class="mt-1 text-sm text-gray-500">সরাসরি আপনার ইনবক্সে ছাড়, নতুন রিলিজ ও রেকমেন্ডেশন পেয়ে যান।</p>
            </div>

            <form action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST" class="mt-4 md:mt-0 md:ml-6 flex w-full md:w-auto">
                @csrf
                <label for="newsletter-email" class="sr-only">ইমেইল</label>
                <input id="newsletter-email" name="email" type="email" required placeholder="আপনার ইমেইল লিখুন" 
                    class="w-full md:w-72 rounded-l-md border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <button type="submit" class="ml-2 inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-md px-4 py-2 text-sm">সাবস্ক্রাইব</button>
            </form>
        </div>

        <!-- Links grid -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <h4 class="text-sm font-semibold text-gray-800">ক্যাটাগরি</h4>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">নতুন আসলো</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">বেস্টসেলার</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">শিক্ষাগত</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">শিশু ও কিশোর</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-800">গ্রাহক সেবা</h4>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">অর্ডার ট্র্যাকিং</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">সাম্প্রতিক অর্ডার</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">রিটার্ন ও রিফান্ড</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">প্রশ্ন ও উত্তর (FAQ)</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-800">আমাদের সম্পর্কে</h4>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">আমরা কে</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">যোগাযোগ</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">ক্যারিয়ার</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-indigo-600">ব্লগ</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-800">ফলো করুন</h4>
                <p class="mt-3 text-sm text-gray-500">সামাজিক যোগাযোগ মাধ্যমে আমাদের অনুসরণ করুন — নতুন বই ও ইভেন্ট আপডেট পাবেন।</p>
                <div class="mt-3 flex items-center gap-3">
                    <a href="#" class="text-gray-500 hover:text-indigo-600" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.99H7.898v-2.888h2.54V9.797c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.462h-1.26c-1.243 0-1.63.772-1.63 1.562v1.875h2.773l-.443 2.888h-2.33v6.99C18.343 21.128 22 16.991 22 12z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-indigo-600" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.162 5.656c-.66.293-1.368.49-2.113.579.76-.455 1.344-1.175 1.62-2.033-.712.422-1.5.729-2.338.894-.672-.716-1.63-1.162-2.69-1.162-2.036 0-3.688 1.652-3.688 3.688 0 .289.033.57.095.84-3.065-.154-5.787-1.62-7.611-3.847-.318.545-.5 1.18-.5 1.857 0 1.28.651 2.41 1.639 3.074-.605-.02-1.176-.186-1.674-.463v.047c0 1.787 1.27 3.277 2.953 3.617-.309.084-.635.129-.97.129-.237 0-.468-.023-.693-.067.468 1.462 1.827 2.526 3.437 2.557-1.259.988-2.845 1.577-4.567 1.577-.297 0-.59-.017-.877-.051 1.63 1.044 3.567 1.652 5.647 1.652 6.776 0 10.49-5.614 10.49-10.49 0-.16-.004-.319-.011-.477.723-.521 1.349-1.169 1.844-1.911-.662.294-1.374.49-2.123.579z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-indigo-600" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7.75 2h8.5A5.75 5.75 0 0122 7.75v8.5A5.75 5.75 0 0116.25 22h-8.5A5.75 5.75 0 012 16.25v-8.5A5.75 5.75 0 017.75 2zm0 1.5A4.25 4.25 0 003.5 7.75v8.5A4.25 4.25 0 007.75 20.5h8.5a4.25 4.25 0 004.25-4.25v-8.5A4.25 4.25 0 0016.25 3.5h-8.5zM12 7a5 5 0 110 10 5 5 0 010-10zm0 1.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7zM17.75 6a1 1 0 110 2 1 1 0 010-2z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom row: payments + legal -->
        <div class="mt-8 border-t border-gray-100 pt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <p class="text-sm text-gray-500">পেমেন্ট মেথড:</p>
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/payments/visa.svg') }}" alt="Visa" class="h-6" />
                    <img src="{{ asset('images/payments/mastercard.svg') }}" alt="Mastercard" class="h-6" />
                    <img src="{{ asset('images/payments/bkash.svg') }}" alt="bKash" class="h-6" />
                </div>
            </div>

            <div class="text-sm text-gray-500">
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-indigo-600">প্রাইভেসি পলিসি</a>
                    <a href="#" class="hover:text-indigo-600">শর্তাবলী</a>
                    <a href="#" class="hover:text-indigo-600">সাইট ম্যাপ</a>
                </div>
                <p class="mt-3">© {{ date('Y') }} আইডিয়া প্রকাশন — সর্বসত্ত্ব সংরক্ষিত।</p>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // Simple inline feedback for newsletter form submission when returned via query param
            const params = new URLSearchParams(window.location.search);
            if (params.get('subscribed') === '1'){
                const notice = document.createElement('div');
                notice.className = 'fixed bottom-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow';
                notice.innerText = 'সাবস্ক্রিপশন সফল হয়েছে — ধন্যবাদ!';
                document.body.appendChild(notice);
                setTimeout(()=> notice.remove(), 5000);
            }
        })();
    </script>
</footer>
