<div id="cart-drawer" class="fixed inset-y-0 right-0 z-50 w-96 max-w-full transform translate-x-full transition-transform duration-300" aria-hidden="true">
    <div class="h-full flex flex-col bg-white shadow-2xl">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="text-sm font-bold">আপনার কার্ট</h3>
            <button id="close-cart-drawer" class="p-2 rounded-md text-gray-600 hover:bg-gray-100" aria-label="Close cart">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="p-4 overflow-y-auto flex-1">
            @include('components.cart-summary')
        </div>

        <div class="p-4 border-t">
            @php
                $checkoutUrl = Route::has('checkout') ? route('checkout') : '/';
            @endphp
            <a href="{{ $checkoutUrl }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-bold">চেকআউট</a>
        </div>
    </div>

    <script>
        (function(){
            const drawer = document.getElementById('cart-drawer');
            const closeBtn = document.getElementById('close-cart-drawer');

            function openDrawer(){
                if (!drawer) return;
                drawer.classList.remove('translate-x-full');
                drawer.setAttribute('aria-hidden', 'false');
            }
            function closeDrawer(){
                if (!drawer) return;
                drawer.classList.add('translate-x-full');
                drawer.setAttribute('aria-hidden', 'true');
            }

            document.addEventListener('openCartDrawer', function(){
                openDrawer();
            });

            if (closeBtn){
                closeBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    closeDrawer();
                });
            }

            // close when clicking outside the drawer panel
            document.addEventListener('click', function(e){
                if (!drawer) return;
                const isOpen = !drawer.classList.contains('translate-x-full');
                if (!isOpen) return;
                const rect = drawer.getBoundingClientRect();
                const clickedInside = e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom;
                if (!clickedInside){
                    closeDrawer();
                }
            });
        })();
    </script>
</div>
