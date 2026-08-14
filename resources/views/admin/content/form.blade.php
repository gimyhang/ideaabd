{{--
    Generic create/edit form for every admin-managed content type.

    Driven entirely by App\Support\ContentTypes, so adding a field there adds it
    here with no change to this file.

    @param array                                     $spec       content type definition
    @param \Illuminate\Database\Eloquent\Model|null  $record     null when creating
    @param array<string, array<int, string>>         $lookups    select options keyed by table
    @param array<int, string>                        $creditees  users an entry can be credited to
--}}
@extends('layouts.admin')

@php
    $editing = $record !== null;
    $heading = $editing ? "{$spec['label']} সম্পাদনা" : "নতুন {$spec['label']}";
    $action  = $editing
        ? route('admin.content.update', ['type' => $spec['key'], 'id' => $record->getKey()])
        : route('admin.content.store', ['type' => $spec['key']]);

    /** Current value for a field: old input first, then the record, then blank. */
    $val = fn (string $name, $fallback = null) => old($name, $editing ? ($record->{$name} ?? $fallback) : $fallback);
@endphp

@section('title', $heading)
@section('heading', $heading)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($spec['listRoute']) }}" class="text-decoration-none">{{ $spec['label'] }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'সম্পাদনা' : 'নতুন' }}</li>
@endsection

@section('actions')
    <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
    </a>
@endsection

@section('content')

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="row g-3">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="col-lg-8">
        <div class="adm-card p-3 p-md-4">
            <h2 class="h6 fw-bold mb-3"><i class="fas fa-{{ $spec['icon'] }} me-1 text-muted"></i> তথ্য</h2>

            <div class="row g-3">
                @foreach ($spec['fields'] as $name => $field)
                    @php $current = $val($name); @endphp

                    <div class="col-md-{{ $field['col'] ?? 12 }}">

                        {{-- ══ CHECKBOX ══════════════════════════════════════════ --}}
                        @if ($field['type'] === 'checkbox')
                            <div class="form-check mt-md-4">
                                <input type="hidden" name="{{ $name }}" value="0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="f-{{ $name }}" name="{{ $name }}" value="1"
                                       @checked(old($name, $editing ? (bool) $record->{$name} : true))>
                                <label class="form-check-label" for="f-{{ $name }}">{{ $field['label'] }}</label>
                            </div>

                        {{-- ══ AUTHOR ROLE GROUP (লেখক / অনুবাদক / সম্পাদক) ══ --}}
                        @elseif ($field['type'] === 'author_role_group')
                            @php
                                $curRole       = old('author_role',  $editing ? ($record->author_role  ?? 'author') : 'author');
                                $curAuthorId   = old('author_link_id', $editing ? ($record->author_link_id ?? '') : '');
                                $curAuthorName = old('author_name',  $editing ? ($record->author_name  ?? '') : '');
                                // যদি author_link_id থাকে তাহলে "directory" মোড, নইলে "custom"
                                $curMode       = old('author_input_mode', ($curAuthorId ? 'directory' : 'custom'));
                                $authorOptions = $lookups['authors'] ?? [];
                            @endphp

                            <label class="form-label small fw-semibold">{{ $field['label'] }}</label>

                            {{-- ভূমিকা নির্বাচন --}}
                            <div class="d-flex flex-wrap gap-3 mb-3 p-2 bg-light rounded border">
                                @foreach (['author' => 'লেখক', 'translator' => 'অনুবাদক', 'editor' => 'সম্পাদক'] as $roleVal => $roleLabel)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               id="author-role-{{ $roleVal }}"
                                               name="author_role" value="{{ $roleVal }}"
                                               @checked($curRole === $roleVal)>
                                        <label class="form-check-label fw-semibold" for="author-role-{{ $roleVal }}">
                                            <i class="fas fa-{{ $roleVal === 'author' ? 'pen-nib' : ($roleVal === 'translator' ? 'language' : 'user-edit') }} me-1 text-muted small"></i>
                                            {{ $roleLabel }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- ইনপুট মোড স্যুইচ --}}
                            <div class="btn-group btn-group-sm mb-2 w-100" role="group" id="author-mode-tabs">
                                <input type="radio" class="btn-check" name="author_input_mode"
                                       id="author-mode-directory" value="directory"
                                       @checked($curMode === 'directory')>
                                <label class="btn btn-outline-primary" for="author-mode-directory">
                                    <i class="fas fa-book-open me-1"></i> লেখক ডিরেক্টরি থেকে বাছাই
                                </label>

                                <input type="radio" class="btn-check" name="author_input_mode"
                                       id="author-mode-custom" value="custom"
                                       @checked($curMode !== 'directory')>
                                <label class="btn btn-outline-secondary" for="author-mode-custom">
                                    <i class="fas fa-keyboard me-1"></i> নিজে লিখুন
                                </label>
                            </div>

                            {{-- Directory Mode: লেখক ড্রপডাউন --}}
                            <div id="author-directory-panel" style="{{ $curMode === 'directory' ? '' : 'display:none' }}">
                                <select name="author_link_id" id="f-author_link_id"
                                        class="form-select @error('author_link_id') is-invalid @enderror">
                                    <option value="">— লেখক নির্বাচন করুন —</option>
                                    @foreach ($authorOptions as $aId => $aName)
                                        <option value="{{ $aId }}" @selected((string)$curAuthorId === (string)$aId)>
                                            {{ $aName }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (empty($authorOptions))
                                    <div class="form-text text-warning mt-1">
                                        <i class="fas fa-triangle-exclamation"></i>
                                        লেখক ডিরেক্টরি এখনো খালি — আগে লেখক যোগ করুন।
                                    </div>
                                @else
                                    <div class="form-text">
                                        লেখকের প্রোফাইল পেজের সাথে লিংক হবে।
                                        @if (Route::has('admin.content.create'))
                                            <a href="{{ route('admin.content.create', 'authors') }}" target="_blank" class="ms-1">
                                                <i class="fas fa-plus-circle"></i> নতুন লেখক যোগ করুন
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                @error('author_link_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            {{-- Custom Mode: ফ্রি-টেক্সট --}}
                            <div id="author-custom-panel" style="{{ $curMode !== 'directory' ? '' : 'display:none' }}">
                                <input type="text" name="author_name" id="f-author_name"
                                       value="{{ $curAuthorName }}"
                                       placeholder="নাম লিখুন (যেমন: মোঃ রফিকুল ইসলাম)"
                                       class="form-control @error('author_name') is-invalid @enderror">
                                <div class="form-text">লেখকের নাম সরাসরি লিখুন — ডিরেক্টরির সাথে কোনো লিংক থাকবে না।</div>
                                @error('author_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            @error('author_role')<div class="invalid-feedback d-block mt-1">{{ $message }}</div>@enderror

                        {{-- ══ সব সাধারণ ফিল্ড ══════════════════════════════ --}}
                        @else
                            <label for="f-{{ $name }}" class="form-label small fw-semibold">
                                {{ $field['label'] }}
                                @if (str_contains($field['rules'], 'required'))
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @switch($field['type'])
                                @case('textarea')
                                    <textarea id="f-{{ $name }}" name="{{ $name }}" rows="3"
                                              class="form-control @error($name) is-invalid @enderror">{{ $current }}</textarea>
                                    @break

                                @case('editor')
                                    <textarea id="f-{{ $name }}" name="{{ $name }}" rows="8"
                                              class="form-control @error($name) is-invalid @enderror">{{ $current }}</textarea>
                                    <div class="form-text">সাধারণ HTML (p, strong, ul, a, img) ব্যবহার করা যাবে।</div>
                                    @break

                                @case('select')
                                    @php
                                        $options = $field['options'] ?? ($lookups[$field['lookup'] ?? ''] ?? []);
                                    @endphp
                                    <select id="f-{{ $name }}" name="{{ $name }}"
                                            class="form-select @error($name) is-invalid @enderror">
                                        <option value="">— নির্বাচন করুন —</option>
                                        @foreach ($options as $optValue => $optLabel)
                                            <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (empty($options) && ! empty($field['lookup']))
                                        <div class="form-text text-warning">
                                            <i class="fas fa-triangle-exclamation"></i>
                                            "{{ $field['lookup'] }}" তালিকা এখনো খালি।
                                        </div>
                                    @endif
                                    @if($name === 'category_id')
                                        <div class="mt-2 p-2 bg-light rounded border border-primary-subtle">
                                            <label for="f-sub_category_name" class="form-label small fw-semibold text-primary mb-1"><i class="fas fa-plus-circle me-1"></i>সাব-ক্যাটাগরি যুক্ত করুন</label>
                                            <input type="text" id="f-sub_category_name" name="sub_category_name" class="form-control form-control-sm" placeholder="নতুন বা বিদ্যমান সাব-ক্যাটাগরির নাম লিখুন">
                                            <div class="form-text" style="font-size: 11px;">উপরের মেইন ক্যাটাগরি নির্বাচন করে এখানে সাব-ক্যাটাগরির নাম লিখুন।</div>
                                        </div>
                                    @endif
                                    @if($name === 'publisher_id')
                                        <div class="mt-2 p-2 bg-light rounded border border-info-subtle">
                                            <label for="f-new_publisher_name" class="form-label small fw-semibold text-info mb-1"><i class="fas fa-plus-circle me-1"></i>অথবা নতুন প্রকাশক তৈরি করুন</label>
                                            <input type="text" id="f-new_publisher_name" name="new_publisher_name" class="form-control form-control-sm" placeholder="নতুন প্রকাশকের নাম লিখুন">
                                            <div class="form-text" style="font-size: 11px;">উপরের তালিকা থেকে না পেলে এখানে নাম লিখুন।</div>
                                        </div>
                                    @endif
                                    @break

                                @case('file')
                                    <input type="file" id="f-{{ $name }}" name="{{ $name }}"
                                           accept="{{ $field['accept'] ?? '' }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @if ($editing && $record->{$name})
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            @if (($field['accept'] ?? '') === 'image/*')
                                                <img src="{{ $record->{$name} }}" alt="" style="height:44px" class="rounded border">
                                            @endif
                                            <a href="{{ $record->{$name} }}" target="_blank" rel="noopener"
                                               class="small text-decoration-none text-truncate" style="max-width:260px">
                                                বর্তমান ফাইল দেখুন
                                            </a>
                                            <div class="form-check ms-auto">
                                                <input class="form-check-input" type="checkbox"
                                                       id="rm-{{ $name }}" name="remove_{{ $name }}" value="1">
                                                <label class="form-check-label small text-danger" for="rm-{{ $name }}">সরান</label>
                                            </div>
                                        </div>
                                    @endif
                                    @break

                                @case('number')
                                    <input type="number" step="{{ $field['step'] ?? '1' }}" min="0"
                                           id="f-{{ $name }}" name="{{ $name }}" value="{{ $current }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @break

                                @case('date')
                                    <input type="date" id="f-{{ $name }}" name="{{ $name }}"
                                           value="{{ $current instanceof \DateTimeInterface ? $current->format('Y-m-d') : $current }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @break

                                @default
                                    <input type="text" id="f-{{ $name }}" name="{{ $name }}" value="{{ $current }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @if ($name === 'subtitle')
                                        <div class="form-text">বইয়ের উপশিরোনাম বা বর্ণনামূলক সাব-টাইটেল (ঐচ্ছিক)।</div>
                                    @endif
                            @endswitch

                            @error($name)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif

                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Posting on behalf of someone --}}
        <div class="adm-card p-3 p-md-4 mb-3">
            <h2 class="h6 fw-bold mb-1"><i class="fas fa-user-pen me-1 text-muted"></i> কার পক্ষে</h2>
            <p class="text-muted small mb-3">
                যিনি অনলাইনে রেজিস্ট্রেশন করতে পারেন না, তাঁর নাম এখানে লিখুন — এন্ট্রিটি
                তাঁর নামে জমা থাকবে।
            </p>

            <div class="mb-3">
                <label for="f-submitted_by" class="form-label small fw-semibold">রেজিস্টার্ড ব্যবহারকারী</label>
                <select id="f-submitted_by" name="submitted_by" class="form-select @error('submitted_by') is-invalid @enderror">
                    <option value="">— আমি নিজে (অ্যাডমিন) —</option>
                    @foreach ($creditees as $userId => $userLabel)
                        <option value="{{ $userId }}" @selected((string) $val('submitted_by') === (string) $userId)>
                            {{ $userLabel }}
                        </option>
                    @endforeach
                </select>
                @error('submitted_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="f-owner_name" class="form-label small fw-semibold">অফলাইন ব্যক্তির নাম</label>
                <input type="text" id="f-owner_name" name="owner_name" value="{{ $val('owner_name') }}"
                       placeholder="যেমন: মোঃ করিম উদ্দিন"
                       class="form-control @error('owner_name') is-invalid @enderror">
                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div>
                <label for="f-owner_phone" class="form-label small fw-semibold">ফোন</label>
                <input type="text" id="f-owner_phone" name="owner_phone" value="{{ $val('owner_phone') }}"
                       class="form-control @error('owner_phone') is-invalid @enderror">
                @error('owner_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Moderation --}}
        <div class="adm-card p-3 p-md-4 mb-3">
            <h2 class="h6 fw-bold mb-3"><i class="fas fa-circle-check me-1 text-muted"></i> অনুমোদন</h2>

            <label for="f-mod_status" class="form-label small fw-semibold">অবস্থা</label>
            <select id="f-mod_status" name="mod_status" class="form-select">
                @foreach (['pending' => 'অপেক্ষমাণ', 'approved' => 'অনুমোদিত', 'rejected' => 'বাতিল'] as $value => $text)
                    <option value="{{ $value }}" @selected($val('mod_status', 'approved') === $value)>{{ $text }}</option>
                @endforeach
            </select>

            @if ($editing && $record->rejection_reason)
                <div class="alert alert-warning small mt-3 mb-0">
                    <strong>বাতিলের কারণ:</strong> {{ $record->rejection_reason }}
                </div>
            @endif

            <hr class="my-3">

            <label for="f-slug" class="form-label small fw-semibold">Slug</label>
            <input type="text" id="f-slug" name="slug" value="{{ $val('slug') }}"
                   placeholder="খালি রাখলে নাম থেকে তৈরি হবে"
                   class="form-control @error('slug') is-invalid @enderror">
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-grid gap-2">
            <button class="btn btn-primary">
                <i class="fas fa-floppy-disk me-1"></i> {{ $editing ? 'হালনাগাদ করুন' : 'সংরক্ষণ করুন' }}
            </button>
            <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary">বাতিল</a>
        </div>
    </div>
</form>

{{-- মোড-সুইচ জাভাস্ক্রিপ্ট --}}
@push('scripts')
<script>
(function () {
    const dirPanel  = document.getElementById('author-directory-panel');
    const custPanel = document.getElementById('author-custom-panel');
    const radios    = document.querySelectorAll('input[name="author_input_mode"]');

    if (!dirPanel || !custPanel || !radios.length) return;

    function applyMode(mode) {
        const isDir = mode === 'directory';
        dirPanel.style.display  = isDir ? '' : 'none';
        custPanel.style.display = isDir ? 'none' : '';

        // ইনঅ্যাক্টিভ প্যানেলের ইনপুট গুলো disabled করা হচ্ছে
        // যাতে ফর্ম সাবমিটে অপ্রয়োজনীয় ডেটা না যায়
        const dirInput  = document.getElementById('f-author_link_id');
        const custInput = document.getElementById('f-author_name');
        if (dirInput)  dirInput.disabled  = !isDir;
        if (custInput) custInput.disabled = isDir;
    }

    radios.forEach(r => r.addEventListener('change', () => applyMode(r.value)));

    // পেজ লোডে বর্তমান মোড প্রয়োগ
    const checked = document.querySelector('input[name="author_input_mode"]:checked');
    if (checked) applyMode(checked.value);
})();
</script>
@endpush

@endsection
