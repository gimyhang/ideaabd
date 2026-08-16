@extends('layouts.app')

@section('title', 'আমার একাউন্ট - ideaabd')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="site-user__avatar mx-auto mb-3 text-white bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold">{{ $user->name }}</h5>
                    <p class="text-muted small">{{ $user->email }}</p>
                    
                    <hr>
                    
                    <div class="nav flex-column nav-pills text-start" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active text-start mb-2" id="v-pills-dashboard-tab" data-bs-toggle="pill" data-bs-target="#v-pills-dashboard" type="button" role="tab" aria-controls="v-pills-dashboard" aria-selected="true">
                            <i class="fas fa-home me-2"></i> ড্যাশবোর্ড
                        </button>
                        <button class="nav-link text-start mb-2" id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#v-pills-orders" type="button" role="tab" aria-controls="v-pills-orders" aria-selected="false">
                            <i class="fas fa-shopping-bag me-2"></i> আমার অর্ডার
                        </button>
                        <button class="nav-link text-start mb-2" id="v-pills-affiliate-tab" data-bs-toggle="pill" data-bs-target="#v-pills-affiliate" type="button" role="tab" aria-controls="v-pills-affiliate" aria-selected="false">
                            <i class="fas fa-link me-2"></i> অ্যাফিলিয়েট হাব
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                
                <!-- Dashboard Tab -->
                <div class="tab-pane fade show active" id="v-pills-dashboard" role="tabpanel" aria-labelledby="v-pills-dashboard-tab">
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm bg-primary text-white h-100 p-3" style="background: linear-gradient(135deg, #0099ff 0%, #0066cc 100%);">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white-50 mb-1">রিডার্স পয়েন্ট (Loyalty Points)</h6>
                                            <h2 class="fw-bold mb-0">{{ $user->loyalty_points }} <small class="fs-6 fw-normal text-white-50">পয়েন্ট</small></h2>
                                        </div>
                                        <div class="fs-1 text-white-50">
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                    <p class="small mt-3 mb-0">প্রতি ১০০ টাকার কেনাকাটায় ৫ পয়েন্ট জিতুন!</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100 p-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white-50 mb-1">অ্যাফিলিয়েট ব্যালেন্স</h6>
                                            <h2 class="fw-bold mb-0">৳{{ number_format($user->affiliate_balance, 2) }}</h2>
                                        </div>
                                        <div class="fs-1 text-white-50">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                    </div>
                                    <p class="small mt-3 mb-0">আপনার অ্যাফিলিয়েট লিংক থেকে আসা বিক্রির কমিশন।</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">সাম্প্রতিক অর্ডারগুলো</h5>
                            @if($myOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>অর্ডার আইডি</th>
                                                <th>বই</th>
                                                <th>মোট টাকা</th>
                                                <th>অর্জিত পয়েন্ট</th>
                                                <th>তারিখ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myOrders as $order)
                                                <tr>
                                                    <td>#{{ $order->id }}</td>
                                                    <td>{{ $order->book ? $order->book->title : 'N/A' }}</td>
                                                    <td>৳{{ $order->total_amount }}</td>
                                                    <td class="text-success fw-bold">+{{ $order->points_earned }}</td>
                                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">আপনি এখনো কোনো অর্ডার করেননি।</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Orders Tab -->
                <div class="tab-pane fade" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">আমার সব অর্ডার</h5>
                            @if($myOrders->count() > 0)
                                <!-- Using same data for demo, in production we would paginate -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>অর্ডার আইডি</th>
                                                <th>বই</th>
                                                <th>ডেলিভারি জেলা</th>
                                                <th>মোট টাকা</th>
                                                <th>তারিখ</th>
                                                <th>স্ট্যাটাস</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myOrders as $order)
                                                <tr>
                                                    <td><span class="badge bg-secondary">#{{ $order->id }}</span></td>
                                                    <td>{{ $order->book ? $order->book->title : 'N/A' }}</td>
                                                    <td>{{ ucfirst($order->district) }}</td>
                                                    <td class="fw-bold">৳{{ $order->total_amount }}</td>
                                                    <td>{{ $order->created_at->format('d M, Y h:i A') }}</td>
                                                    <td><span class="badge bg-success">সফল</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="text-muted mb-3" style="font-size: 3rem;"><i class="fas fa-box-open"></i></div>
                                    <p class="text-muted">আপনার কোনো অর্ডার নেই।</p>
                                    <a href="{{ route('book.index') }}" class="btn btn-primary mt-2">বই কেনাকাটা শুরু করুন</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Affiliate Tab -->
                <div class="tab-pane fade" id="v-pills-affiliate" role="tabpanel" aria-labelledby="v-pills-affiliate-tab">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-link text-primary me-2"></i> আমার অ্যাফিলিয়েট লিংক</h5>
                            <p class="text-muted">আপনার অ্যাফিলিয়েট লিংক ব্যবহার করে যে কেউ বই কিনলে আপনি পাবেন ৫% কমিশন!</p>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-light border-end-0" id="basic-addon3"><i class="fas fa-globe text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 bg-white" id="affiliateUrl" value="{{ url('/?ref=' . $user->id) }}" readonly>
                                <button class="btn btn-primary px-4" type="button" onclick="copyAffiliateLink()">কপি করুন</button>
                            </div>
                            <small class="text-success d-none" id="copy-success-msg">লিংক কপি করা হয়েছে!</small>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <h6 class="text-muted mb-2">সর্বমোট বিক্রি (অ্যাফিলিয়েট)</h6>
                                    <h2 class="fw-bold text-dark">{{ $affiliateOrders->count() }} <small class="fs-6 fw-normal">টি বই</small></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <h6 class="text-muted mb-2">সর্বমোট কমিশন আয়</h6>
                                    <h2 class="fw-bold text-success">৳{{ number_format($totalCommissionEarned, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">আমার লিংক থেকে কেনা অর্ডারসমূহ</h6>
                            @if($affiliateOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>অর্ডার আইডি</th>
                                                <th>ক্রেতার নাম</th>
                                                <th>মোট টাকা</th>
                                                <th>আমার কমিশন</th>
                                                <th>তারিখ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($affiliateOrders as $order)
                                                <tr>
                                                    <td>#{{ $order->id }}</td>
                                                    <td>{{ $order->customer_name }}</td>
                                                    <td>৳{{ $order->total_amount }}</td>
                                                    <td class="text-success fw-bold">+৳{{ $order->commission_amount }}</td>
                                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0 text-center py-4">এখনো আপনার লিংক থেকে কোনো বিক্রি হয়নি।</p>
                            @endif
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
    function copyAffiliateLink() {
        var copyText = document.getElementById("affiliateUrl");
        copyText.select();
        copyText.setSelectionRange(0, 99999); /* For mobile devices */
        navigator.clipboard.writeText(copyText.value);
        
        var msg = document.getElementById("copy-success-msg");
        msg.classList.remove("d-none");
        setTimeout(() => msg.classList.add("d-none"), 3000);
    }
</script>
@endsection
