@extends('layouts.app')

@php
    $summary = $summary ?? [];
    $type = $summary['type'] ?? 'buyer';
    $typeLabel = $summary['type_label'] ?? 'Account';
    $approved = ($summary['reg_status'] ?? null) === 'approved' || $type === 'buyer';
    $signedIn = auth()->check();
    $rows = array_filter([
        'Name'   => $summary['name'] ?? auth()->user()?->name,
        'Phone'  => $summary['phone'] ?? auth()->user()?->phone,
        'Email'  => $summary['email'] ?? auth()->user()?->email,
        'Role'   => $typeLabel,
        'Date'   => $summary['created_at'] ?? null,
    ]);
@endphp

@section('title', $approved ? 'Welcome' : 'Pending')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth-modern.css') }}?v={{ @filemtime(public_path('css/auth-modern.css')) ?: 1 }}">
@endpush

@section('content')
<div class="am-wrap">
    <div class="am-card">
        <div class="am-hero {{ $approved ? 'is-ok' : 'is-wait' }}">
            <span class="am-badge" aria-hidden="true">
                <i class="fa-solid {{ $approved ? 'fa-check' : 'fa-hourglass-half' }}"></i>
            </span>
            <h1>{{ $approved ? 'Welcome' : 'Pending' }}</h1>
            <p>{{ $approved ? 'Your account is ready.' : 'An admin will review your request.' }}</p>
        </div>

        <div class="am-body">
            @if($rows)
                <dl class="am-list">
                    @foreach($rows as $label => $value)
                        <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                    @endforeach
                </dl>
            @endif

            @unless($approved)
                <ol class="am-steps">
                    <li><b>Review</b><span>Admin checks your details</span></li>
                    <li><b>Notify</b><span>SMS and email on approval</span></li>
                    <li><b>Access</b><span>Sign in to your portal</span></li>
                </ol>
            @endunless

            <div class="am-actions">
                @if($signedIn && $approved)
                    <a href="{{ route('user.portal') }}" class="am-btn am-btn--primary">Dashboard</a>
                @elseif(!$signedIn)
                    <a href="{{ route('login') }}" class="am-btn am-btn--primary">Sign in</a>
                @endif
                <a href="{{ route('home') }}" class="am-btn">Home</a>
                @if($signedIn)
                    <form method="POST" action="{{ route('logout') }}" class="am-inline">@csrf
                        <button type="submit" class="am-btn am-btn--ghost">Sign out</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
