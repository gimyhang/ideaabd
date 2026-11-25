@extends('layouts.admin')

@section('title', 'Visitors')
@section('heading', 'ভিজিটর ও নিরাপত্তা')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Visitors</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-visitors.css') }}?v={{ @filemtime(public_path('css/admin-visitors.css')) ?: 1 }}">
@endpush

@section('actions')
    <div class="vr-top">
        <span class="vr-live" title="গত ৫ মিনিটে সক্রিয় ভিজিটর"><span class="vr-live__dot"></span> লাইভ <b id="vrLiveCount">—</b></span>
        <div class="vr-seg" role="tablist" aria-label="সময়কাল">
            @foreach($periods as $pk => $pl)
                <a href="{{ route('admin.visitor-reports', ['period' => $pk]) }}" class="{{ $period === $pk ? 'is-on' : '' }}" data-period="{{ $pk }}" role="tab" aria-selected="{{ $period === $pk ? 'true' : 'false' }}">{{ $pl }}</a>
            @endforeach
        </div>
    </div>
@endsection

@section('content')
@php
    $s = $security;
    $durations = ['60' => '১ ঘণ্টা', '1440' => '২৪ ঘণ্টা', '10080' => '৭ দিন', 'forever' => 'স্থায়ী'];
@endphp
<div class="vrx" id="vrRoot"
     data-data-url="{{ route('admin.visitor-reports.data') }}"
     data-live-url="{{ route('admin.visitor-reports.live') }}"
     data-ip-url="{{ route('admin.visitor-reports.ip') }}"
     data-block-url="{{ route('admin.visitor-reports.block') }}"
     data-unblock-url="{{ route('admin.visitor-reports.unblock') }}">

    {{-- KPIs (filled by admin-visitors.js) --}}
    <div class="vr-kpis mb-3" id="vrKpis" aria-live="polite">
        @foreach([['views', 'পেজ ভিউ', 'fa-eye'], ['uniques', 'ইউনিক ভিজিটর', 'fa-user'], ['ppv', 'প্রতিজন পেজ', 'fa-layer-group'], ['mobile', 'মোবাইল থেকে', 'fa-mobile-screen']] as [$k, $l, $i])
            <div class="vr-kpi" data-kpi="{{ $k }}">
                <div class="vr-kpi__top"><span class="vr-kpi__ic"><i class="fa-solid {{ $i }}"></i></span><span class="vr-kpi__lb">{{ $l }}</span></div>
                <div class="vr-kpi__v">—</div>
                <div class="vr-kpi__d"></div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
            <div class="vr-card h-100">
                <div class="vr-card__head">
                    <h6><i class="fa-solid fa-chart-area"></i> ট্রাফিক</h6>
                    <div class="vr-legend"><span><i style="background: var(--vr-a)"></i> ভিউ</span><span><i style="background: var(--vr-b)"></i> ইউনিক</span></div>
                </div>
                <div class="vr-chart"><canvas id="vrTrend" aria-label="ট্রাফিক চার্ট" role="img"></canvas></div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="vr-card h-100">
                <div class="vr-card__head"><h6><i class="fa-solid fa-display"></i> ডিভাইস</h6></div>
                <div class="vr-donut"><canvas id="vrDevices" aria-label="ডিভাইস চার্ট" role="img"></canvas><div class="vr-donut__c"><b id="vrMobilePct">—</b><span>মোবাইল</span></div></div>
                <div class="vr-bars mt-2" id="vrDeviceList"></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
            <div class="vr-card h-100">
                <div class="vr-card__head"><h6><i class="fa-solid fa-fire"></i> কখন বেশি আসে</h6><small class="text-muted">দিন × ঘণ্টা</small></div>
                <div class="vr-heat" id="vrHeat"></div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="vr-card h-100">
                <div class="vr-card__head"><h6><i class="fa-solid fa-arrows-split-up-and-left"></i> কোথা থেকে আসে</h6></div>
                <div class="vr-bars" id="vrChannels"></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="vr-card h-100">
                <div class="vr-card__head"><h6><i class="fa-solid fa-earth-asia"></i> দেশ</h6></div>
                <div class="vr-bars" id="vrCountries"></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="vr-card h-100">
                <div class="vr-card__head">
                    <h6><i class="fa-solid fa-microchip"></i> প্রযুক্তি</h6>
                    <div class="vr-mini-seg" id="vrTechSeg"><button type="button" class="is-on" data-tech="browsers">ব্রাউজার</button><button type="button" data-tech="os">OS</button><button type="button" data-tech="models">মডেল</button></div>
                </div>
                <div class="vr-bars" id="vrTech"></div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="vr-card h-100">
                <div class="vr-card__head"><h6><i class="fa-solid fa-book"></i> বেশি দেখা বই</h6></div>
                <ol class="vr-rank" id="vrBooks"></ol>
            </div>
        </div>
    </div>

    <div class="vr-card mb-4">
        <div class="vr-card__head"><h6><i class="fa-solid fa-file-lines"></i> বেশি দেখা পেজ</h6><small class="text-muted" id="vrBuilt"></small></div>
        <div class="table-responsive"><table class="table vr-table mb-0"><thead><tr><th>পেজ</th><th class="text-end">ভিউ</th><th class="text-end">ইউনিক</th><th style="width: 30%"></th></tr></thead><tbody id="vrPages"></tbody></table></div>
    </div>

    {{-- ================= SECURITY (admin only) ================= --}}
    <section class="vr-sec mb-4" id="security" aria-labelledby="vrSecTitle">
        <div class="vr-sec__head">
            <div>
                <h5 id="vrSecTitle"><i class="fa-solid fa-shield-halved"></i> নিরাপত্তা</h5>
                <div class="small text-muted">
                    সুরক্ষা {{ $s['guard_on'] ? 'চালু' : 'বন্ধ' }} · এক IP থেকে মিনিটে {{ $s['limits']['per_min'] }}টির বেশি অনুরোধ আটকায় ·
                    ভুয়া পাথে {{ $s['limits']['strikes'] }} বার ঢোকার চেষ্টা করলে {{ $s['limits']['block_min'] }} মিনিট ব্লক · আপনার IP <code>{{ $myIp }}</code>
                </div>
            </div>
            <span class="vr-guard {{ $s['guard_on'] ? 'is-on' : '' }}"><i class="fa-solid {{ $s['guard_on'] ? 'fa-lock' : 'fa-lock-open' }}"></i> {{ $s['guard_on'] ? 'সুরক্ষিত' : 'সুরক্ষা বন্ধ' }}</span>
        </div>

        <div class="vr-sec__kpis">
            <div><span>সন্দেহজনক IP (২৪ ঘণ্টা)</span><b class="{{ count($s['suspects']) ? 'text-danger' : '' }}">{{ number_format(count($s['suspects'])) }}</b></div>
            <div><span>ব্লক করা IP</span><b>{{ number_format($s['blocked']->count()) }}</b><small>{{ $s['auto'] }}টি স্বয়ংক্রিয়</small></div>
            <div><span>বট / স্ক্রিপ্টের ভিউ (২৪ ঘণ্টা)</span><b>{{ number_format($s['bot_hits']) }}</b></div>
            <div><span>আলাদা IP (২৪ ঘণ্টা)</span><b>{{ number_format($s['ips_24h']) }}</b></div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-7">
                <div class="vr-card h-100">
                    <div class="vr-card__head"><h6><i class="fa-solid fa-user-secret"></i> সন্দেহজনক IP</h6><small class="text-muted">সার্চ ইঞ্জিন ও লগিন করা সদস্য বাদে</small></div>
                    @if(empty($s['suspects']))
                        <div class="vr-empty"><i class="fa-solid fa-circle-check text-success"></i> গত ২৪ ঘণ্টায় সন্দেহজনক কিছু নেই।</div>
                    @else
                        <div class="table-responsive">
                            <table class="table vr-table mb-0">
                                <thead><tr><th>IP</th><th>কেন</th><th class="text-end">ভিউ</th><th class="text-end"></th></tr></thead>
                                <tbody>
                                @foreach($s['suspects'] as $x)
                                    <tr>
                                        <td><button type="button" class="vr-ip" data-ip="{{ $x['ip'] }}">{{ $x['flag'] }} {{ $x['ip'] }}</button><div class="small text-muted">{{ $x['place'] }} · {{ $x['last'] }}</div></td>
                                        <td>@foreach($x['why'] as $w)<span class="vr-why">{{ $w }}</span>@endforeach</td>
                                        <td class="text-end fw-semibold">{{ number_format($x['hits']) }}</td>
                                        <td class="text-end text-nowrap">
                                            @if($x['blocked'])
                                                <span class="vr-tag vr-tag--blocked"><i class="fa-solid fa-ban"></i> ব্লক</span>
                                            @else
                                                <form method="POST" action="{{ route('admin.visitor-reports.block') }}" class="d-inline-flex gap-1" data-confirm="{{ $x['ip'] }} ব্লক করবেন? এই IP থেকে সাইট খোলা যাবে না।" data-confirm-icon="error">
                                                    @csrf
                                                    <input type="hidden" name="ip" value="{{ $x['ip'] }}">
                                                    <input type="hidden" name="reason" value="{{ implode(', ', $x['why']) }}">
                                                    <select name="duration" class="form-select form-select-sm vr-dur" aria-label="কতক্ষণ">
                                                        @foreach($durations as $dv => $dl)<option value="{{ $dv }}" @selected($dv === '1440')>{{ $dl }}</option>@endforeach
                                                    </select>
                                                    <button class="btn btn-sm btn-danger rounded-pill px-2.5"><i class="fa-solid fa-ban"></i><span class="d-none d-md-inline ms-1">ব্লক</span></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-12 col-xl-5">
                <div class="vr-card h-100">
                    <div class="vr-card__head"><h6><i class="fa-solid fa-ban"></i> ব্লক তালিকা</h6></div>
                    <form method="POST" action="{{ route('admin.visitor-reports.block') }}" class="vr-addblock" data-confirm="এই IP ব্লক করবেন?" data-confirm-icon="error">
                        @csrf
                        <input type="text" name="ip" class="form-control form-control-sm" placeholder="IP ঠিকানা, যেমন 103.12.34.56" required pattern="[0-9a-fA-F:.]{3,45}" aria-label="IP">
                        <select name="duration" class="form-select form-select-sm" aria-label="কতক্ষণ">
                            @foreach($durations as $dv => $dl)<option value="{{ $dv }}" @selected($dv === 'forever')>{{ $dl }}</option>@endforeach
                        </select>
                        <input type="text" name="reason" class="form-control form-control-sm" maxlength="200" placeholder="কারণ (ঐচ্ছিক)" aria-label="কারণ">
                        <button class="btn btn-sm btn-dark rounded-pill px-3"><i class="fa-solid fa-plus me-1"></i>ব্লক</button>
                    </form>
                    @if($s['blocked']->isEmpty())
                        <div class="vr-empty">কোনো IP ব্লক করা নেই।</div>
                    @else
                        <ul class="vr-blist">
                            @foreach($s['blocked'] as $b)
                                <li>
                                    <div class="min-w-0">
                                        <button type="button" class="vr-ip" data-ip="{{ $b->ip }}">{{ $b->ip }}</button>
                                        <span class="vr-tag {{ $b->source === 'auto' ? 'vr-tag--auto' : 'vr-tag--manual' }}">{{ $b->source === 'auto' ? 'স্বয়ংক্রিয়' : 'অ্যাডমিন' }}</span>
                                        <div class="small text-muted text-truncate">{{ $b->reason }} · {{ $b->expires_at ? \Illuminate\Support\Carbon::parse($b->expires_at)->diffForHumans(null, true) . ' বাকি' : 'স্থায়ী' }}</div>
                                    </div>
                                    <form method="POST" action="{{ route('admin.visitor-reports.unblock') }}" data-confirm="{{ $b->ip }} আনব্লক করবেন?">
                                        @csrf <input type="hidden" name="ip" value="{{ $b->ip }}">
                                        <button class="btn btn-sm btn-light border rounded-pill px-2.5" title="আনব্লক"><i class="fa-solid fa-lock-open"></i></button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ================= LIVE FEED ================= --}}
    <div class="vr-card mb-4">
        <div class="vr-card__head flex-wrap">
            <h6><i class="fa-solid fa-satellite-dish"></i> লাইভ ভিজিট <span class="vr-auto" id="vrAuto" title="প্রতি ১৫ সেকেন্ডে হালনাগাদ">স্বয়ংক্রিয়</span></h6>
            <div class="vr-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="vrFeedSearch" placeholder="IP, পেজ, শহর, উৎস…" aria-label="খুঁজুন"></div>
        </div>
        <div class="table-responsive">
            <table class="table vr-table vr-feed mb-0">
                <thead><tr><th>সময়</th><th>ভিজিটর</th><th>পেজ</th><th>ডিভাইস</th><th>উৎস</th></tr></thead>
                <tbody id="vrFeed"><tr><td colspan="5" class="text-center text-muted py-4">লোড হচ্ছে…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

{{-- IP details --}}
<div class="modal fade" id="vrIpModal" tabindex="-1" aria-labelledby="vrIpTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header"><h6 class="modal-title fw-bold font-monospace" id="vrIpTitle">IP</h6><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body" id="vrIpBody"></div>
            <div class="modal-footer" id="vrIpFoot"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>window.vrInitial = @json($data);</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/admin-visitors.js') }}?v={{ @filemtime(public_path('js/admin-visitors.js')) ?: 1 }}"></script>
@endpush
