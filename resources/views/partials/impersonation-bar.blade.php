@if (session()->has(\App\Http\Controllers\Admin\ImpersonationController::SESSION_KEY) && auth()->check())
    <div id="impersonationBar" role="alert" style="position: sticky; top: 0; z-index: 3000; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: .75rem; padding: .5rem 1rem; background: #b45309; color: #fff; font-size: 15px; font-weight: 600;">
        <span>অ্যাডমিন ভিউ: আপনি এখন "{{ auth()->user()->name }}" ({{ auth()->user()->getRoleDisplayName() }}) এর ড্যাশবোর্ড দেখছেন — শুধু দেখা, পরিবর্তন বন্ধ</span>
        <form method="POST" action="{{ route('impersonate.leave') }}" class="m-0">
            @csrf
            <button type="submit" style="border: 0; border-radius: 999px; padding: .25rem 1rem; background: #fff; color: #92400e; font-weight: 700; cursor: pointer;">অ্যাডমিনে ফিরুন</button>
        </form>
    </div>
@endif
