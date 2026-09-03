<footer class="border-t border-slate-200 bg-slate-950 text-slate-300">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-4">
        <!-- Col 1: Brand & Bio -->
        <div class="space-y-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-sky-600/15 group-hover:scale-105 transition transform">
                    I
                </div>
                <div>
                    <span class="text-xl font-black text-slate-900 tracking-tight">IdeaABD</span>
                    <span class="block text-[10px] text-sky-600 font-bold uppercase tracking-wider">আইডিয়া প্রকাশন</span>
                </div>
            </a>
            <p class="text-slate-500 leading-relaxed text-xs">
                IdeaABD — বাংলাদেশের জন্য আধুনিক অনলাইন বই ও সাহিত্য প্ল্যাটফর্ম। আপনার পছন্দের বই ঘরে পৌঁছে দিতে সহজ অর্ডার এবং নিয়মিত সাহিত্যমূলক কন্টেন্ট।
            </p>
        </div>

        <!-- Col 2: Quick Links -->
        <div class="space-y-3">
            <h4 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-l-2 border-sky-500 pl-2">
                প্রয়োজনীয় লিংক
            </h4>
            <ul class="space-y-2.5 text-slate-600 font-medium">
                <li><a href="/catalog" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>বইয়ের ক্যাটালগ</span></a></li>
                <li><a href="/magazine" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>ই-ম্যাগাজিন</span></a></li>
                <li><a href="/writers" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>লেখকবৃন্দ</span></a></li>
                <li><a href="/catalog?is_featured=1" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>বিশেষ ফিচার্ড বই</span></a></li>
            </ul>
        </div>

        <!-- Col 3: Customer Care -->
        <div class="space-y-3">
            <h4 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-l-2 border-indigo-500 pl-2">
                গ্রাহক সহায়তা
            </h4>
            <ul class="space-y-2.5 text-slate-600 font-medium">
                <li><a href="/orders" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>অর্ডার ট্র্যাকিং</span></a></li>
                <li><a href="/profile" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>আমার প্রোফাইল</span></a></li>
                <li><a href="/wishlist" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>পছন্দের তালিকা</span></a></li>
                <li><a href="/contact" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>যোগাযোগ ও হেল্পলাইন</span></a></li>
            </ul>
        </div>

        <!-- Col 4: Contact Info -->
        <div class="space-y-3">
            <h4 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-l-2 border-emerald-500 pl-2">
                যোগাযোগ
            </h4>
            <div class="space-y-2 text-slate-600 text-xs">
                <p class="flex items-center gap-2">
                    <span>📍</span>
                    <span>ঢাকা, বাংলাদেশ</span>
                </p>
                <p class="flex items-center gap-2">
                    <span>✉️</span>
                    <span class="font-mono text-slate-800">ideapbd@gmail.com</span>
                </p>
                <p class="flex items-center gap-2">
                    <span>📞</span>
                    <span class="font-mono text-slate-800">+8801726976982</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Copyright Strip -->
    <div class="border-t border-slate-800 bg-slate-900/80 py-6 text-center text-[11px] text-slate-400">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 sm:flex-row">
            <p>© {{ date('Y') }} IdeaABD (আইডিয়া প্রকাশন)। সর্বস্বত্ব সংরক্ষিত। <span class="text-slate-500">| ডিজাইনার: <a href="{{ route('authors.show', 'sakil-masud') }}" class="text-cyan-400 hover:underline">মাসুদ রানা সাকিল</a></span></p>
            <div class="flex items-center gap-4 text-slate-500 text-[11px]">
                <a href="/privacy" class="hover:text-slate-800">গোপনীয়তা নীতি</a>
                <span>•</span>
                <a href="/terms" class="hover:text-slate-800">ব্যবহারের শর্তাবলী</a>
            </div>
        </div>
    </div>
</footer>
