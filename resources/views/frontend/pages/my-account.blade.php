@extends('layouts.app')

@section('title', 'আমার একাউন্ট - ideaabd')

@section('content')
<div class="container py-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3 d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-check-circle fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-exclamation-triangle fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body text-center p-4">
                    <div class="site-user__avatar mx-auto mb-3 text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 80px; height: 80px; font-size: 2rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-1"><i class="fas fa-phone me-1 text-success"></i>{{ $user->phone }}</p>
                    @if($user->email && !str_contains($user->email, '@buyer.ideaabd.com') && !str_contains($user->email, '@author.ideaabd.com'))
                        <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i>{{ $user->email }}</p>
                    @endif
                    
                    <span class="badge rounded-pill {{ $user->role === 'author' ? 'bg-success' : 'bg-primary' }} px-3 py-1.5 mb-3">
                        <i class="fas {{ $user->role === 'author' ? 'fa-pen-fancy' : 'fa-user' }} me-1"></i>
                        {{ $user->role === 'author' ? 'নিবন্ধিত লেখক' : 'সম্মানিত গ্রাহক' }}
                    </span>
                    
                    <hr>
                    
                    <div class="nav flex-column nav-pills text-start gap-1" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link {{ request('tab') === 'author-blogs' || $editPost ? '' : 'active' }} text-start rounded-3" id="v-pills-dashboard-tab" data-bs-toggle="pill" data-bs-target="#v-pills-dashboard" type="button" role="tab">
                            <i class="fas fa-home me-2"></i> ড্যাশবোর্ড
                        </button>
                        
                        @if($user->role === 'author' || $user->reg_type === 'author' || $authorPosts->count() > 0)
                            <button class="nav-link {{ request('tab') === 'author-blogs' || $editPost ? 'active' : '' }} text-start rounded-3" id="v-pills-author-tab" data-bs-toggle="pill" data-bs-target="#v-pills-author" type="button" role="tab">
                                <i class="fas fa-pen-nib me-2 text-success"></i> আমার ব্লগ ও লেখা
                                @if($authorPosts->count() > 0)
                                    <span class="badge bg-success float-end rounded-pill">@bn($authorPosts->count())</span>
                                @endif
                            </button>
                        @endif

                        <button class="nav-link text-start rounded-3" id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#v-pills-orders" type="button" role="tab">
                            <i class="fas fa-shopping-bag me-2"></i> আমার অর্ডার
                        </button>
                        <button class="nav-link text-start rounded-3" id="v-pills-affiliate-tab" data-bs-toggle="pill" data-bs-target="#v-pills-affiliate" type="button" role="tab">
                            <i class="fas fa-link me-2"></i> অ্যাফিলিয়েট হাব
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div class="tab-content" id="v-pills-tabContent">
                
                <!-- Dashboard Tab -->
                <div class="tab-pane fade {{ request('tab') === 'author-blogs' || $editPost ? '' : 'show active' }}" id="v-pills-dashboard" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm text-white h-100 p-3 rounded-4" style="background: linear-gradient(135deg, #0099ff 0%, #0066cc 100%);">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white-50 mb-1">রিডার্স পয়েন্ট (Loyalty Points)</h6>
                                            <h2 class="fw-bold mb-0">@bn($user->loyalty_points ?? 0) <small class="fs-6 fw-normal text-white-50">পয়েন্ট</small></h2>
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
                            <div class="card border-0 shadow-sm h-100 p-3 rounded-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white-50 mb-1">অ্যাফিলিয়েট ব্যালেন্স</h6>
                                            <h2 class="fw-bold mb-0">@taka($user->affiliate_balance ?? 0)</h2>
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
                    
                    @if($user->role === 'author' || $user->reg_type === 'author')
                        <div class="card shadow-sm border-0 mb-4 rounded-4 bg-light">
                            <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-feather-pointed text-success me-2"></i>লেখক পোর্টাল</h5>
                                    <p class="text-muted small mb-0">আপনার লেখা গল্প, কবিতা, প্রবন্ধ বা আর্টিকেল পোস্ট করুন ও পরিচালনা করুন।</p>
                                </div>
                                <button type="button" class="btn btn-success px-4 py-2 rounded-pill fw-semibold shadow-sm" onclick="openAuthorTab()">
                                    <i class="fas fa-plus me-1"></i> নতুন ব্লগ লিখুন
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="card shadow-sm border-0 mb-4 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">সাম্প্রতিক অর্ডারসমূহ</h5>
                            @if($myOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
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
                                                    <td><span class="badge bg-light text-dark border">#{{ $order->id }}</span></td>
                                                    <td>{{ $order->book ? $order->book->title : 'N/A' }}</td>
                                                    <td class="fw-bold">@taka($order->total_amount)</td>
                                                    <td class="text-success fw-bold">+@bn($order->points_earned ?? 0)</td>
                                                    <td>@bnDate($order->created_at)</td>
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

                <!-- Author Blogs Tab -->
                @if($user->role === 'author' || $user->reg_type === 'author' || $authorPosts->count() > 0)
                <div class="tab-pane fade {{ request('tab') === 'author-blogs' || $editPost ? 'show active' : '' }}" id="v-pills-author" role="tabpanel">
                    
                    <!-- Write / Edit Blog Form Card -->
                    <div class="card shadow-sm border-0 mb-4 rounded-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-success">
                                <i class="fas {{ $editPost ? 'fa-pen-to-square' : 'fa-pen-nib' }} me-2"></i>
                                {{ $editPost ? 'খসড়া ব্লগ সম্পাদনা' : 'নতুন ব্লগ বা লেখা পোস্ট করুন' }}
                            </h5>
                            @if($editPost)
                                <a href="{{ route('my-account', ['tab' => 'author-blogs']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="fas fa-times me-1"></i> নতুন লেখায় ফিরুন
                                </a>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="{{ $editPost ? route('author.blog.update', $editPost->id) : route('author.blog.store') }}" enctype="multipart/form-data">
                                @csrf
                                @if($editPost)
                                    @method('PUT')
                                @endif

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-semibold">ব্লগ বা লেখার শিরোনাম <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $editPost->title ?? '') }}" required placeholder="এখানে আকর্ষণীয় শিরোনাম দিন...">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">ক্যাটাগরি</label>
                                        <select name="category_id" class="form-select rounded-3">
                                            <option value="">ক্যাটাগরি বেছে নিন (ঐচ্ছিক)</option>
                                            @foreach($blogCategories as $cat)
                                                <option value="{{ $cat->id }}" @selected(old('category_id', $editPost->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">সংক্ষিপ্ত সারসংক্ষেপ (Excerpt) <span class="text-muted small">ঐচ্ছিক</span></label>
                                    <textarea name="excerpt" rows="2" class="form-control rounded-3" placeholder="লেখার মূল ভাব বা সংক্ষেপ ১-২ বাক্যে...">{{ old('excerpt', $editPost->excerpt ?? '') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">মূল লেখা / কনটেন্ট <span class="text-danger">*</span></label>
                                    <textarea name="content" rows="8" class="form-control rounded-3" required placeholder="আপনার প্রবন্ধ, গল্প, কবিতা বা মতামত এখানে বিস্তারিত লিখুন...">{{ old('content', $editPost->content ?? '') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">ফিচার্ড ছবি (Featured Image) <span class="text-muted small">ঐচ্ছিক</span></label>
                                    <input type="file" name="featured_image" class="form-control rounded-3" accept="image/*">
                                    @if($editPost && $editPost->featured_image)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $editPost->featured_image) }}" alt="Preview" class="rounded-3 border" style="max-height: 80px;">
                                        </div>
                                    @endif
                                </div>

                                <div class="p-3 bg-light rounded-3 mb-3 small text-muted">
                                    <i class="fas fa-info-circle text-primary me-1"></i>
                                    <strong>নিয়মাবলী:</strong> আপনি লেখাটি <strong>"খসড়া (Draft)"</strong> হিসেবে রেখে পরবর্তীতে এডিট করতে পারবেন। তবে <strong>"অনুমোদনের জন্য জমা দিন"</strong> বাটনে ক্লিক করলে তা অ্যাডমিনের পর্যালোচনায় যাবে এবং এরপর আর এডিট করা যাবে না।
                                </div>

                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <button type="submit" name="action_type" value="draft" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold">
                                        <i class="fas fa-bookmark me-1"></i> খসড়া সংরক্ষণ করুন (Save Draft)
                                    </button>
                                    <button type="submit" name="action_type" value="submit" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm">
                                        <i class="fas fa-paper-plane me-1"></i> অনুমোদনের জন্য জমা দিন (Submit for Review)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- My Blog Posts List -->
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check text-primary me-2"></i>আমার পূর্ববর্তী লেখাসমূহ</h5>
                        </div>
                        <div class="card-body p-4">
                            @if($authorPosts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>শিরোনাম</th>
                                                <th>ক্যাটাগরি</th>
                                                <th>অবস্থা (Status)</th>
                                                <th>তারিখ</th>
                                                <th class="text-end">অ্যাকশন</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($authorPosts as $post)
                                                @php
                                                    $isLocked = ($post->status === 'pending' || $post->status === 'published' || $post->mod_status === 'approved');
                                                    $isDraft = ($post->status === 'draft');
                                                    $isRejected = ($post->status === 'rejected' || $post->mod_status === 'rejected');
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-dark">{{ $post->title }}</div>
                                                        @if($isRejected && $post->rejection_reason)
                                                            <div class="small text-danger mt-1">
                                                                <i class="fas fa-circle-exclamation me-1"></i>বাতিলের কারণ: {{ $post->rejection_reason }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $post->category ? $post->category->name : 'সাধারণ' }}</td>
                                                    <td>
                                                        @if($post->status === 'published' || $post->mod_status === 'approved')
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                                                <i class="fas fa-circle-check me-1"></i> প্রকাশিত
                                                            </span>
                                                        @elseif($post->status === 'pending' || $post->mod_status === 'pending')
                                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                                                <i class="fas fa-clock me-1"></i> পর্যালোচনায় অপেক্ষমাণ
                                                            </span>
                                                        @elseif($isRejected)
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                                                <i class="fas fa-times-circle me-1"></i> প্রত্যাখ্যাত
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">
                                                                <i class="fas fa-pencil me-1"></i> খসড়া (Draft)
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted small">@bnDate($post->created_at)</td>
                                                    <td class="text-end">
                                                        @if($isDraft || $isRejected)
                                                            <div class="d-inline-flex gap-1">
                                                                <a href="{{ route('my-account', ['tab' => 'author-blogs', 'edit_post_id' => $post->id]) }}" 
                                                                   class="btn btn-sm btn-outline-primary rounded-pill px-3" title="সম্পাদনা করুন">
                                                                    <i class="fas fa-pen me-1"></i> এডিট
                                                                </a>
                                                                <form action="{{ route('author.blog.destroy', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই খসড়াটি মুছে ফেলতে চান?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="মুছে ফেলুন">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <span class="badge bg-light text-muted border px-2.5 py-1.5 rounded-pill" title="জমা বা প্রকাশের পর এডিট লক করা হয়েছে">
                                                                <i class="fas fa-lock text-warning me-1"></i> এডিট লক
                                                            </span>
                                                            @if($post->status === 'published' || $post->mod_status === 'approved')
                                                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill ms-1" title="সাইটে দেখুন">
                                                                    <i class="fas fa-eye text-primary"></i>
                                                                </a>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-feather fs-1 mb-2 text-muted opacity-50"></i>
                                    <p class="mb-0">আপনি এখনো কোনো ব্লগ বা লেখা তৈরি করেননি। উপরের ফর্ম থেকে আপনার প্রথম লেখাটি লিখুন!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Orders Tab -->
                <div class="tab-pane fade" id="v-pills-orders" role="tabpanel">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">আমার সব অর্ডার</h5>
                            @if($myOrders->count() > 0)
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
                                                    <td class="fw-bold">@taka($order->total_amount)</td>
                                                    <td>@bnDate($order->created_at)</td>
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
                                    <a href="{{ route('book.index') }}" class="btn btn-primary mt-2 rounded-pill px-4">বই কেনাকাটা শুরু করুন</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Affiliate Tab -->
                <div class="tab-pane fade" id="v-pills-affiliate" role="tabpanel">
                    <div class="card shadow-sm border-0 mb-4 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-link text-primary me-2"></i> আমার অ্যাফিলিয়েট লিংক</h5>
                            <p class="text-muted">আপনার অ্যাফিলিয়েট লিংক ব্যবহার করে যে কেউ বই কিনলে আপনি পাবেন ৫% কমিশন!</p>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-globe text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 bg-white" id="affiliateUrl" value="{{ url('/?ref=' . $user->id) }}" readonly>
                                <button class="btn btn-primary px-4" type="button" onclick="copyAffiliateLink()">কপি করুন</button>
                            </div>
                            <small class="text-success d-none" id="copy-success-msg">লিংক কপি করা হয়েছে!</small>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body text-center p-4">
                                    <h6 class="text-muted mb-2">সর্বমোট বিক্রি (অ্যাফিলিয়েট)</h6>
                                    <h2 class="fw-bold text-dark">@bn($affiliateOrders->count()) <small class="fs-6 fw-normal">টি বই</small></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body text-center p-4">
                                    <h6 class="text-muted mb-2">সর্বমোট কমিশন আয়</h6>
                                    <h2 class="fw-bold text-success">@taka($totalCommissionEarned)</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">আমার লিংক থেকে কেনা অর্ডারসমূহ</h6>
                            @if($affiliateOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
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
                                                    <td><span class="badge bg-light text-dark border">#{{ $order->id }}</span></td>
                                                    <td>{{ $order->customer_name }}</td>
                                                    <td>@taka($order->total_amount)</td>
                                                    <td class="text-success fw-bold">+@taka($order->commission_amount)</td>
                                                    <td>@bnDate($order->created_at)</td>
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
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        
        var msg = document.getElementById("copy-success-msg");
        msg.classList.remove("d-none");
        setTimeout(() => msg.classList.add("d-none"), 3000);
    }

    function openAuthorTab() {
        var tabBtn = document.getElementById('v-pills-author-tab');
        if (tabBtn) {
            var tab = new bootstrap.Tab(tabBtn);
            tab.show();
        }
    }
</script>
@endsection
