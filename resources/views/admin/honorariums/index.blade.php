@extends('layouts.admin')

@section('title', 'আইডিয়াপত্র লেখক সম্মানি — IdeaPatra Author Honorariums')
@section('heading', 'আইডিয়াপত্র লেখক সম্মানি ও পাঠক শুভেচ্ছা লেজার')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.blog') }}">Ideapatra / Blog</a></li>
    <li class="breadcrumb-item active">লেখক সম্মানি (Honorariums)</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-4" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-danger">
                <small class="text-muted d-block font-sans">সর্বমোট সংগৃহীত সম্মানি (১০০%)</small>
                <h4 class="fw-bold text-danger mb-1 font-monospace">৳{{ number_format($totalCollected, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">আইডিয়াপত্র লেখা পড়ে পাঠকদের মোট উপহার</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-success">
                <small class="text-muted d-block font-sans">লেখকদের ওয়ালেটে জমাকৃত (৭০%)</small>
                <h4 class="fw-bold text-success mb-1 font-monospace">৳{{ number_format($totalAuthorEarned, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">লেখক সম্মানির ৭০% উত্তোলনযোগ্য ব্যালেন্স</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-info">
                <small class="text-muted d-block font-sans">সাইট মেইনটেনেন্স বিল (৩০%)</small>
                <h4 class="fw-bold text-info mb-1 font-monospace">৳{{ number_format($totalPlatformFee, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">প্ল্যাটফর্ম রক্ষণাবেক্ষণ ও সার্ভার ব্যয়</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-primary">
                <small class="text-muted d-block font-sans">মোট সম্মানি সংখ্যা ও চলতি মাস</small>
                <h4 class="fw-bold text-primary mb-1 font-monospace">{{ $totalCount }} টি</h4>
                <small class="text-muted" style="font-size: 11px;">
                    চলতি মাস: <strong>৳{{ number_format($thisMonthSum, 2) }}</strong> (মেইনটেনেন্স: ৳{{ number_format($thisMonthPlatformFee ?? 0, 2) }})
                </small>
            </div>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <form method="GET" action="{{ route('admin.author-honorariums.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="পাঠক, লেখক, TrxID বা লেখা..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <select name="author_id" class="form-select form-select-sm">
                    <option value="">সকল লেখক</option>
                    @foreach($authors as $a)
                        <option value="{{ $a->id }}" {{ request('author_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="method" class="form-select form-select-sm">
                    <option value="">সকল পেমেন্ট মাধ্যম</option>
                    <option value="bkash" {{ request('method') === 'bkash' ? 'selected' : '' }}>বিকাশ (bKash)</option>
                    <option value="nagad" {{ request('method') === 'nagad' ? 'selected' : '' }}>নগদ (Nagad)</option>
                    <option value="rocket" {{ request('method') === 'rocket' ? 'selected' : '' }}>রকেট (Rocket)</option>
                    <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>কার্ড / অনলাইন</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>সফল (Completed)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>পেন্ডিং (Pending)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>বাতিল (Rejected)</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>রিফান্ড (Refunded)</option>
                </select>
            </div>

            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">
                    <i class="fas fa-filter me-1"></i> ফিল্টার
                </button>
                @if(request()->hasAny(['search', 'author_id', 'method', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('admin.author-honorariums.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="ফিল্টার রিসেট">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Main Ledger Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-50">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-receipt text-danger"></i>
                <span>পাঠক সম্মানি লেনদেন লগ (Author Honorariums Ledger)</span>
                <span class="badge bg-danger text-white rounded-pill">Total: {{ $honorariums->total() }}</span>
            </h6>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.90rem;">
                <thead class="table-light text-secondary text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3">TrxID ও তারিখ</th>
                        <th class="py-3">লেখক (Author)</th>
                        <th class="py-3">আইডিয়াপত্র লেখা</th>
                        <th class="py-3">পাঠক ও বার্তা</th>
                        <th class="py-3">পদ্ধতি ও প্রেরক নম্বর</th>
                        <th class="py-3 text-end">মোট সম্মানি</th>
                        <th class="py-3 text-end">লেখক (৭০%)</th>
                        <th class="py-3 text-end">মেইনটেনেন্স (৩০%)</th>
                        <th class="py-3 text-center">স্ট্যাটাস</th>
                        <th class="pe-3 py-3 text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($honorariums as $h)
                        <tr>
                            {{-- TrxID & Date --}}
                            <td class="ps-3 text-nowrap">
                                <span class="fw-bold font-monospace text-dark d-block">
                                    {{ $h->trx_id ?: ('IDP-TIP-' . $h->id) }}
                                </span>
                                <small class="text-muted">{{ $h->created_at->format('d M, Y • h:i A') }}</small>
                            </td>

                            {{-- Author --}}
                            <td>
                                @if($h->author)
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="fw-bold text-dark">{{ $h->author->name }}</span>
                                        <button type="button" class="btn btn-xs btn-outline-warning rounded-circle p-0 d-inline-flex align-items-center justify-content-center" 
                                                style="width: 20px; height: 20px; font-size: 8.5px;" 
                                                onclick="openAuthorPasswordResetModal({{ $h->author->id }}, '{{ addslashes($h->author->name) }}', '{{ addslashes($h->author->email ?: ($h->author->phone ?: '')) }}')" 
                                                title="লেখকের পাসওয়ার্ড রিসেট (1-Click Password Reset)">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block">{{ $h->author->phone ?: $h->author->email }}</small>
                                @else
                                    <span class="text-muted fst-italic">অজানা লেখক</span>
                                @endif
                            </td>

                            {{-- Post --}}
                            <td style="max-width: 200px;">
                                @if($h->post)
                                    <a href="{{ route('blog.show', $h->post->slug ?: $h->post->id) }}" target="_blank" class="text-decoration-none text-dark fw-semibold line-clamp-2" title="{{ $h->post->title }}">
                                        {{ $h->post->title }}
                                    </a>
                                @else
                                    <span class="text-muted">আইডিয়াপত্র পোস্ট #{{ $h->blog_post_id }}</span>
                                @endif
                            </td>

                            {{-- Donor & Message --}}
                            <td style="max-width: 220px;">
                                <div class="fw-bold text-dark">{{ $h->display_name }}</div>
                                @if($h->donor_phone || $h->donor_email)
                                    <small class="text-muted d-block">{{ $h->donor_phone ?: $h->donor_email }}</small>
                                @endif
                                @if($h->message)
                                    <div class="small text-secondary bg-light p-1.5 rounded-2 mt-1 border-start border-2 border-danger fst-italic" style="font-size: 11px;">
                                        "{{ \Illuminate\Support\Str::limit($h->message, 80) }}"
                                    </div>
                                @endif
                            </td>

                            {{-- Method & Sender Account --}}
                            <td class="text-nowrap">
                                <span class="badge {{ $h->method_badge_class }} rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 10px;">
                                    {{ $h->payment_method }}
                                </span>
                                @if($h->sender_account_number)
                                    <div class="font-monospace text-muted mt-1" style="font-size: 11px;">
                                        প্রেরক: {{ $h->sender_account_number }}
                                    </div>
                                @endif
                            </td>

                            {{-- Total Amount --}}
                            <td class="text-end text-nowrap">
                                <span class="fw-bold text-dark fs-6 font-monospace">৳{{ number_format($h->amount, 2) }}</span>
                            </td>

                            {{-- Author 70% --}}
                            <td class="text-end text-nowrap">
                                <span class="fw-bold text-success font-monospace">৳{{ number_format($h->author_amount, 2) }}</span>
                            </td>

                            {{-- Site Maintenance 30% --}}
                            <td class="text-end text-nowrap">
                                <span class="fw-bold text-info font-monospace">৳{{ number_format($h->platform_fee, 2) }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="text-center text-nowrap">
                                <span class="badge {{ $h->status_badge_class }} rounded-pill px-2.5 py-1" style="font-size: 10.5px;">
                                    {{ ucfirst($h->payment_status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 text-end text-nowrap">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        স্ট্যাটাস পরিবর্তন
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <form action="{{ route('admin.author-honorariums.status', $h->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="dropdown-item small text-success">
                                                    <i class="fas fa-check-circle me-1.5"></i> সফল (Completed)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.author-honorariums.status', $h->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="dropdown-item small text-warning">
                                                    <i class="fas fa-clock me-1.5"></i> পেন্ডিং (Pending)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.author-honorariums.status', $h->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="dropdown-item small text-danger">
                                                    <i class="fas fa-times-circle me-1.5"></i> বাতিল (Rejected)
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.author-honorariums.destroy', $h->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই সম্মানি রেকর্ডটি মুছে ফেলতে চান?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item small text-danger">
                                                    <i class="fas fa-trash-can me-1.5"></i> রেকর্ড মুছুন
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-hand-holding-heart fs-2 opacity-25 d-block mb-2"></i>
                                কোনো লেখক সম্মানি রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($honorariums->hasPages())
            <div class="p-3 border-top bg-light">
                {{ $honorariums->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal: Author Fast Password Reset --}}
<div class="modal fade" id="resetAuthorPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning text-dark border-0 p-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-white bg-opacity-50 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-key text-dark"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">লেখকের পাসওয়ার্ড রিসেট</h6>
                        <small class="text-dark-50 fw-semibold" id="resetModalAuthorName">লেখক নাম</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="authorPasswordResetForm" onsubmit="submitAuthorPasswordReset(event)">
                    @csrf
                    <input type="hidden" id="resetModalAuthorId" name="author_id">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">লগইন আইডি / ইমেইল / ফোন</label>
                        <input type="text" id="resetModalIdentity" class="form-control form-control-sm bg-white" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1 d-flex align-items-center justify-content-between">
                            <span>নতুন পাসওয়ার্ড লিখুন অথবা স্বয়ংক্রিয় তৈরি করুন:</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold small" onclick="generateRandomAuthorPassword()">
                                <i class="fas fa-dice me-1"></i>স্বয়ংক্রিয় পাসওয়ার্ড
                            </button>
                        </label>
                        <div class="input-group">
                            <input type="text" name="password" id="resetModalNewPassword" class="form-control fw-bold font-monospace bg-white" placeholder="যেমন: Idea@3842" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyResetPasswordToClipboard()" title="পাসওয়ার্ড কপি">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">সর্বনিম্ন ৬ অক্ষর (যেমন: 123456 বা Idea@1234)</small>
                    </div>

                    <div id="resetResultCard" class="d-none p-3 bg-white border border-success-subtle rounded-3 shadow-xs mb-3">
                        <div class="d-flex align-items-center gap-2 text-success fw-bold small mb-2">
                            <i class="fas fa-circle-check"></i>
                            <span>পাসওয়ার্ড সফলভাবে রিসেট হয়েছে!</span>
                        </div>
                        <div class="small text-muted mb-2">
                            <div><strong>লগইন আইডি:</strong> <span id="resLoginId" class="text-dark font-monospace fw-semibold"></span></div>
                            <div><strong>নতুন পাসওয়ার্ড:</strong> <span id="resPassword" class="text-danger fw-bold font-monospace"></span></div>
                            <div><strong>লগইন লিংক:</strong> <a href="{{ route('login') }}" target="_blank" class="text-primary text-decoration-none">{{ route('login') }}</a></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="copyFullCredentials()">
                                <i class="far fa-copy me-1"></i>তথ্য কপি করুন
                            </button>
                            <a href="#" id="resWhatsappBtn" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 d-none">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp এ পাঠান
                            </a>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <button type="submit" class="btn btn-sm btn-warning fw-bold rounded-pill px-4" id="btnSubmitPasswordReset">
                            <i class="fas fa-save me-1"></i>পাসওয়ার্ড সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentResetPayload = null;

function openAuthorPasswordResetModal(authorId, authorName, identity) {
    document.getElementById('resetModalAuthorId').value = authorId;
    document.getElementById('resetModalAuthorName').textContent = authorName || 'লেখক';
    document.getElementById('resetModalIdentity').value = identity || 'অটো-জেনারেটেড আইডি';
    
    generateRandomAuthorPassword();
    document.getElementById('resetResultCard').classList.add('d-none');
    
    const modal = new bootstrap.Modal(document.getElementById('resetAuthorPasswordModal'));
    modal.show();
}

function generateRandomAuthorPassword() {
    let rand = 'Idea@' + Math.floor(1000 + Math.random() * 9000);
    document.getElementById('resetModalNewPassword').value = rand;
}

function copyResetPasswordToClipboard() {
    const pwd = document.getElementById('resetModalNewPassword').value;
    if (pwd && navigator.clipboard) {
        navigator.clipboard.writeText(pwd).then(() => alert('পাসওয়ার্ড ক্লিপবোর্ডে কপি করা হয়েছে!'));
    }
}

function copyFullCredentials() {
    if (!currentResetPayload) return;
    const text = `আইডিয়া প্রকাশন — লেখক পোর্টাল লগইন তথ্য:\nলগইন আইডি: ${currentResetPayload.login_identity}\nপাসওয়ার্ড: ${currentResetPayload.new_password}\nলগইন লিংক: ${currentResetPayload.login_url}`;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => alert('লগইন তথ্য কপি করা হয়েছে!'));
    }
}

function submitAuthorPasswordReset(e) {
    e.preventDefault();
    const authorId = document.getElementById('resetModalAuthorId').value;
    const password = document.getElementById('resetModalNewPassword').value;
    const btn = document.getElementById('btnSubmitPasswordReset');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>সংরক্ষণ হচ্ছে...';

    fetch(`/admin/authors/${authorId}/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            password: password
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
            currentResetPayload = data;
            document.getElementById('resLoginId').textContent = data.login_identity;
            document.getElementById('resPassword').textContent = data.new_password;
            
            const waBtn = document.getElementById('resWhatsappBtn');
            if (data.whatsapp_url) {
                waBtn.href = data.whatsapp_url;
                waBtn.classList.remove('d-none');
            } else {
                waBtn.classList.add('d-none');
            }

            document.getElementById('resetResultCard').classList.remove('d-none');
        } else {
            alert(data.message || 'পাসওয়ার্ড রিসেট ব্যর্থ হয়েছে।');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error(err);
        alert('সার্ভার এরর: পাসওয়ার্ড রিসেট করা যায়নি।');
    });
}
</script>
@endsection
