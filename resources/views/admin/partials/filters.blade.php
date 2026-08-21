{{--
    Search + optional select filter bar for admin list pages.

    @param string      $action       form target
    @param string|null $placeholder  search box hint
    @param array       $selects      [ ['name'=>'role', 'label'=>'All Roles', 'options'=>['admin'=>'Admin']] ]
--}}
<form method="GET" action="{{ $action }}" class="adm-card p-3 mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-lg">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-magnifying-glass text-muted"></i></span>
                <input type="search" name="search" class="form-control border-start-0 ps-0"
                       placeholder="{{ $placeholder ?? 'Search records...' }}" value="{{ request('search') }}" aria-label="Search">
            </div>
        </div>

        @foreach ($selects ?? [] as $select)
            <div class="col-lg-3 col-md-6">
                <select name="{{ $select['name'] }}" class="form-select">
                    <option value="">{{ $select['label'] }}</option>
                    @foreach ($select['options'] as $value => $text)
                        <option value="{{ $value }}" @selected(request($select['name']) === (string) $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div class="col-lg-auto col-md-6 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i> Filter</button>
            @if (request()->hasAny(array_merge(['search'], array_column($selects ?? [], 'name'))))
                <a href="{{ $action }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
            @endif
        </div>
    </div>
</form>
