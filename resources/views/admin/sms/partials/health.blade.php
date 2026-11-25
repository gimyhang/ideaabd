{{-- Gateway health: one line when all is well, a clear fix when something is broken --}}
@php
    $h = $health ?? ['state' => 'unknown', 'title' => '', 'fix' => null, 'failed24' => 0, 'kind' => null, 'last_sent' => null];
    $tone = ['ok' => 'ok', 'warn' => 'warn', 'error' => 'err', 'unknown' => 'warn'][$h['state']] ?? 'warn';
    $icon = ['ok' => 'fa-circle-check', 'warn' => 'fa-triangle-exclamation', 'err' => 'fa-plug-circle-xmark'][$tone];
    $needsSettings = in_array($h['kind'], ['key', 'sender', 'account'], true);
@endphp
<div class="sms-health sms-health--{{ $tone }} mb-4" id="smsHealth" role="{{ $tone === 'err' ? 'alert' : 'status' }}">
    <div class="sms-health__ic"><i class="fa-solid {{ $icon }}"></i></div>
    <div class="sms-health__body">
        <div class="sms-health__title">
            {{ $h['title'] }}
            @if($h['failed24'] > 0)
                <span class="sms-health__chip">গত ২৪ ঘণ্টায় {{ number_format($h['failed24']) }}টি ব্যর্থ</span>
            @endif
        </div>
        @if($h['fix'])
            <div class="sms-health__fix">{{ $h['fix'] }}</div>
        @elseif($h['last_sent'])
            <div class="sms-health__fix">শেষ সফল SMS: {{ \Illuminate\Support\Carbon::parse($h['last_sent'])->diffForHumans() }} · সেন্ডার {{ $credentials['sender_id'] ?? '—' }} · key {{ $credentials['api_key_masked'] ?: '—' }}</div>
        @endif
        @if($tone === 'err' && $h['kind'] === 'key')
            <div class="sms-health__note"><i class="fa-solid fa-circle-info me-1"></i> key ঠিক না হওয়া পর্যন্ত অর্ডার, OTP ও নোটিফিকেশন SMS যাবে না (ইমেইল চলবে)।</div>
        @endif
    </div>
    <div class="sms-health__act">
        @if($needsSettings && $tone === 'err')
            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold" data-sms-goto-settings><i class="fa-solid fa-key me-1"></i> key ঠিক করুন</button>
        @elseif($h['kind'] === 'balance')
            <a href="https://bulksmsbd.net" target="_blank" rel="noopener" class="btn btn-sm btn-warning rounded-pill px-3 fw-semibold"><i class="fa-solid fa-bolt me-1"></i> রিচার্জ</a>
        @endif
        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="refreshSmsBalance(true)" title="আবার যাচাই"><i class="fa-solid fa-rotate-right" id="refreshIconHealth"></i></button>
    </div>
</div>
