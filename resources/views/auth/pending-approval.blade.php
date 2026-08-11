@extends('layouts.app')
@section('title', 'অনুমোদনের অপেক্ষায় - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card border-0 shadow-sm p-5">
                <div class="mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;background:#FFF3CD">
                        <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                    </div>
                    <h3 class="fw-bold">রেজিস্ট্রেশন জমা হয়েছে!</h3>
                    <p class="text-muted">আপনার রেজিস্ট্রেশন অনুরোধ সফলভাবে জমা দেওয়া হয়েছে।</p>
                </div>

                <div class="alert alert-warning text-start">
                    <strong>পরবর্তী ধাপ:</strong>
                    <ul class="mb-0 mt-2">
                        <li>আমাদের অ্যাডমিন দল আপনার তথ্য যাচাই করবেন</li>
                        <li>সাধারণত ২৪-৪৮ ঘণ্টার মধ্যে সিদ্ধান্ত নেওয়া হয়</li>
                        <li>অনুমোদন বা প্রত্যাখ্যান সম্পর্কে ইমেইলে জানানো হবে</li>
                    </ul>
                </div>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">হোমপেজে যান</a>
                    <a href="mailto:support@ideaabd.com" class="btn btn-primary">সাপোর্টে যোগাযোগ করুন</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
