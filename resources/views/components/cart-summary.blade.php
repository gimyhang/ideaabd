<aside class="bg-white rounded-lg shadow-sm p-4">
    <h4 class="text-sm font-semibold text-gray-800">কার্ট সারসংকলন</h4>
    <div class="mt-3 space-y-3">
        @if(!empty($cart) && count($cart->items))
            @foreach($cart->items as $item)
                <div class="flex items-center gap-3">
                    <img src="{{ $item->image ?? asset('images/placeholder/book-card.jpg') }}" alt="" class="w-12 h-16 object-cover rounded" />
                    <div class="flex-1 text-sm">
                        <div class="font-medium">{{ $item->title }}</div>
                        <div class="text-xs text-gray-500">{{ $item->quantity }} × {{ $item->price }}</div>
                    </div>
                    <div class="text-sm font-semibold">{{ $item->total }}</div>
                </div>
            @endforeach

            <div class="mt-4 border-t pt-4 text-sm">
                <div class="flex justify-between text-gray-600"><span>সাবটোটাল</span><span>{{ $cart->subtotal }}</span></div>
                <div class="flex justify-between text-gray-600 mt-1"><span>শিপিং</span><span>{{ $cart->shipping ?? '৳ 0.00' }}</span></div>
                <div class="flex justify-between text-indigo-600 font-bold mt-2"><span>মোট</span><span>{{ $cart->total }}</span></div>
                <a href="{{ route('checkout') ?? '#' }}" class="block mt-3 bg-indigo-600 text-white text-center py-2 rounded">চেকআউট</a>
            </div>
        @else
            <p class="text-sm text-gray-500">আপনার কার্ট খালি।</p>
        @endif
    </div>
</aside>
