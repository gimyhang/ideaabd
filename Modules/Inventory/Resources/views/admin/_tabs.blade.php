@php
    $tabs = [
        'admin.inventory.index'     => 'Stock',
        'admin.inventory.locations' => 'Locations',
        'admin.inventory.transfers' => 'Transfers',
        'admin.inventory.wastages'  => 'Wastage',
        'admin.inventory.alerts'    => 'Alerts',
        'admin.inventory.logs'      => 'Movements',
    ];
@endphp
<nav class="iv-tabs" aria-label="Inventory">
    @foreach ($tabs as $route => $label)
        <a href="{{ route($route) }}" class="iv-tab {{ request()->routeIs($route) || request()->routeIs($route . '.*') ? 'is-active' : '' }}">{{ $label }}</a>
    @endforeach
</nav>
