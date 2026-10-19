@include('admin.accounting._modern')
<link rel="stylesheet" href="{{ asset('css/inventory-admin.css') }}?v={{ @filemtime(public_path('css/inventory-admin.css')) ?: 1 }}">
<script src="{{ asset('js/inventory-admin.js') }}?v={{ @filemtime(public_path('js/inventory-admin.js')) ?: 1 }}" defer></script>
