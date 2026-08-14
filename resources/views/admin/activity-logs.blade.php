@extends('layouts.admin')

@section('title', 'অ্যাডমিন অ্যাক্টিভিটি লগ')
@section('heading', 'অ্যাডমিন অ্যাক্টিভিটি হিস্ট্রি')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন</a></li>
    <li class="breadcrumb-item active">অ্যাক্টিভিটি লগ</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="adm-card">
            <div class="adm-card__head flex-wrap gap-2">
                <div>
                    <h6 class="mb-0"><i class="fas fa-clock-rotate-left me-2 text-primary"></i> সিস্টেম অ্যাক্টিভিটি অডিট ট্রেইল</h6>
                    <small class="text-muted">অ্যাডমিন ও সাব-অ্যাডমিনদের সম্পাদিত সকল কার্যক্রমের বিস্তারিত রিয়েল-টাইম লগ</small>
                </div>
                <form class="d-flex gap-2" action="{{ route('admin.activity-logs') }}" method="GET">
                    <input type="search" name="search" class="form-control form-control-sm" placeholder="খুঁজুন (আইপি, বিবরণ...)" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i> সার্চ</button>
                </form>
            </div>

            <div class="adm-card__body p-0">
                @if($logs->isEmpty())
                    <div class="empty-state py-5">
                        <i class="fas fa-history text-muted fs-1 mb-2"></i>
                        <p class="fw-semibold">এখনো কোনো অ্যাক্টিভিটি রেকর্ড পাওয়া যায়নি</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table adm-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">ব্যবহারকারী</th>
                                    <th>একশন টাইপ</th>
                                    <th>কার্যক্রমের বিবরণ</th>
                                    <th>আইপি এড্রেস</th>
                                    <th class="pe-3">সময়</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="adm-avatar adm-avatar--sm bg-primary text-white">
                                                    {{ mb_substr($log->user->name ?? 'A', 0, 1) }}
                                                </span>
                                                <div>
                                                    <span class="fw-semibold small d-block">{{ $log->user->name ?? 'সিস্টেম' }}</span>
                                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $log->user->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="pill pill--info text-uppercase fw-bold" style="font-size: 0.7rem;">
                                                {{ $log->action_type }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small">{{ $log->description }}</span>
                                        </td>
                                        <td>
                                            <code class="small text-muted">{{ $log->ip_address ?? '—' }}</code>
                                        </td>
                                        <td class="pe-3 text-nowrap">
                                            <small class="text-muted"><i class="far fa-clock me-1"></i>@bnDate($log->created_at) {{ $log->created_at->format('h:i A') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if($logs->hasPages())
                <div class="adm-card__foot">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
