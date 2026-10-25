{{-- Number protection: rules, locked numbers, manual block --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="sms-panel h-100">
            <div class="sms-panel-head"><h5 class="mb-0 fw-bold">সুরক্ষার নিয়ম</h5></div>
            <ol class="sms-rules">
                <li>
                    <strong>ভুয়া নম্বরে মেসেজ যায় না।</strong>
                    <span>সঠিক বাংলাদেশি মোবাইল (013–019) ছাড়া এবং <code>01711000001</code>, <code>01712345678</code>-এর মতো নম্বর বাদ পড়ে।</span>
                </li>
                <li>
                    <strong>প্রতি নম্বরে দিনে সর্বোচ্চ {{ $guard['limit'] }}টি মেসেজ।</strong>
                    <span>
                        @if($guard['scope'] === 'all')
                            ওটিপি ও সব স্বয়ংক্রিয় মেসেজ ধরে, যেকোনো ২৪ ঘণ্টায়।
                        @else
                            শুধু ওটিপি ধরে, যেকোনো ২৪ ঘণ্টায়। ওয়েলকাম মেসেজ প্রতি নম্বরে একবারই যায়। একটি অর্ডারের জন্য একটি মেসেজ (একই অর্ডার আবার কনফার্ম হলে দ্বিতীয়বার যায় না); নতুন অর্ডারে নতুন মেসেজ, কোনো দৈনিক সীমা নেই। অর্ডারের ওটিপি ওটিপির নিয়মে চলে। বাকি স্বয়ংক্রিয় মেসেজে দিনে {{ $guard['notify'] }}টি, কোনো লক ছাড়া।
                        @endif
                        অ্যাডমিনের ক্যাম্পেইন এই সীমার বাইরে।
                    </span>
                </li>
                <li>
                    <strong>{{ $guard['limit'] + 1 }}য় চেষ্টায় {{ $guard['hours'] }} ঘণ্টা বন্ধ।</strong>
                    <span>নম্বরটি {{ $guard['hours'] }} ঘণ্টার জন্য সীমিত হয় এবং ব্যবহারকারীকে কখন আবার চেষ্টা করা যাবে তা জানানো হয়।</span>
                </li>
                <li>
                    <strong>{{ $guard['days'] }} দিন একই কাজ করলে ওটিপি ব্লক।</strong>
                    <span>আলাদা {{ $guard['days'] }} দিনে সীমা ছাড়ালে নম্বরে ওটিপি স্থায়ীভাবে বন্ধ হয়, যতক্ষণ না আপনি মুক্ত করেন।</span>
                </li>
            </ol>
            <div class="px-4 pb-3 small text-muted">সীমা বদলাতে <code>.env</code>-এ <code>SMS_DAILY_LIMIT</code>, <code>SMS_RESTRICT_HOURS</code>, <code>SMS_BLOCK_AFTER_DAYS</code> দিন। সব স্বয়ংক্রিয় মেসেজে সীমা চাইলে <code>SMS_LIMIT_SCOPE=all</code>।</div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="sms-panel h-100">
            <div class="sms-panel-head"><h5 class="mb-0 fw-bold">নম্বর ব্লক করুন</h5></div>
            <form action="{{ route('admin.sms.restrictions.block') }}" method="POST" class="p-4">
                @csrf
                <label class="form-label small fw-semibold">মোবাইল নম্বর</label>
                <input type="text" name="phone" class="form-control mb-3" placeholder="01712345678" required maxlength="20">
                <label class="form-label small fw-semibold">কারণ (ঐচ্ছিক)</label>
                <input type="text" name="reason" class="form-control mb-3" maxlength="150" placeholder="যেমন: বারবার ওটিপি চাওয়া হচ্ছে">
                <button type="submit" class="btn btn-danger rounded-pill px-4">
                    <i class="fa-solid fa-ban me-1"></i> ওটিপি ব্লক করুন
                </button>
            </form>
        </div>
    </div>
</div>

<div class="sms-panel">
    <div class="sms-panel-head">
        <div>
            <h5 class="mb-0 fw-bold">সীমিত ও ব্লক করা নম্বর</h5>
            <div class="small text-muted">মুক্ত করলে নম্বরটির গণনা শূন্য থেকে শুরু হয়।</div>
        </div>
        <span class="badge rounded-pill text-bg-light border">{{ $restrictions->count() }}</span>
    </div>

    <div class="table-responsive">
        <table class="table sms-table align-middle mb-0">
            <thead>
                <tr><th>নম্বর</th><th>অবস্থা</th><th>কারণ</th><th>লঙ্ঘনের দিন</th><th>শেষ চেষ্টা</th><th class="text-end">অ্যাকশন</th></tr>
            </thead>
            <tbody>
                @forelse($restrictions as $r)
                    <tr>
                        <td class="font-monospace">{{ str_starts_with($r->phone, '880') ? '0' . substr($r->phone, 3) : '+' . $r->phone }}</td>
                        <td>
                            @if($r->isBlocked())
                                <span class="badge rounded-pill text-bg-danger">ওটিপি ব্লক</span>
                            @else
                                <span class="badge rounded-pill text-bg-warning">{{ $r->restricted_until->diffForHumans(null, true) }} বাকি</span>
                            @endif
                        </td>
                        <td class="small">{{ $r->reason ?: '—' }}</td>
                        <td class="small text-muted">{{ count($r->violation_days ?? []) }} দিন</td>
                        <td class="small text-muted text-nowrap">{{ $r->last_attempt_at?->format('d M, h:i A') ?? '—' }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.sms.restrictions.release') }}" method="POST" onsubmit="return confirm('নম্বরটি মুক্ত করবেন?');" class="d-inline">
                                @csrf
                                <input type="hidden" name="phone" value="{{ $r->phone }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">মুক্ত করুন</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="fa-solid fa-shield-check fs-3 d-block mb-2 opacity-50"></i>কোনো সীমিত বা ব্লক করা নম্বর নেই।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
