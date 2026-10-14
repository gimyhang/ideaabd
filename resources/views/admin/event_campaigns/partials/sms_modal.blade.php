{{-- Dynamic SMS Dispatch Modal Partial --}}
@php
    $modalPrefix = $prefix ?? '';
    $modalId = "smsModal{$modalPrefix}{$item->id}";
    $cleanPhone = preg_replace('/[^0-9]/', '', $item->phone);
    $cardUrl = url('/card/' . $item->registration_number);
    $welcomeApproveSms = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলায় আপনাকে স্বাগত। Download your card: {$cardUrl} — আইডিয়া প্রকাশন";
    $cancellationSms = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলা ২০২৬-এ আপনার নিবন্ধনটি বাতিল করা হয়েছে। অনুসন্ধানে: 01726976982 — আইডিয়া প্রকাশন";
@endphp

<div class="modal fade sms-participant-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered text-start" style="max-width: 540px;">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form class="participant-sms-form" action="{{ route('admin.event-campaigns.registrations.send-sms', $item->id) }}" method="POST">
                @csrf
                <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #065f46 0%, #047857 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-paper-plane text-white fs-6"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0">এসএমএস পাঠান (Send SMS)</h6>
                            <small class="text-white-50 font-monospace">#{{ $item->registration_number }} &bull; {{ $item->name }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    {{-- Recipient Info Box --}}
                    <div class="p-3 bg-white rounded-3 border mb-3 d-flex align-items-center justify-content-between shadow-xs">
                        <div>
                            <span class="text-muted small d-block mb-0.5">প্রাপকের মোবাইল নম্বর:</span>
                            <span class="fw-bold text-dark font-monospace fs-6">{{ $item->phone }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="tel:{{ $item->phone }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" title="কল করুন">
                                <i class="fa-solid fa-phone"></i>
                            </a>
                            <a href="https://wa.me/{{ str_starts_with($cleanPhone, '0') ? '88'.$cleanPhone : $cleanPhone }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1" title="হোয়াটসঅ্যাপ চ্যাট">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Quick Template Selector --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> টেমপ্লেট নির্বাচন করুন
                        </label>
                        <select class="form-select form-select-sm sms-template-selector" onchange="applySmsTemplate(this, 'smsTextarea_{{ $modalId }}')">
                            <option value="">-- মেসেজ টেমপ্লেট বেছে নিন --</option>
                            <option value="{{ $welcomeApproveSms }}" selected>১. এপ্রুভ ও কার্ড ডাউনলোড লিংক (Welcome & Download Card)</option>
                            <option value="{{ $cancellationSms }}">২. নিবন্ধন বাতিলকরণ নোটিশ (Cancellation Notice)</option>
                            <option value="">৩. কাস্টম বার্তা (খালি বার্তা লিখুন)</option>
                        </select>
                    </div>

                    {{-- Message Textarea --}}
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">মেসেজ কন্টেন্ট <span class="text-danger">*</span></label>
                            <small class="text-muted font-monospace" id="smsCounter_{{ $modalId }}">
                                <span class="sms-char-count">0</span> অক্ষর &bull; <span class="sms-msg-count">1</span> SMS
                            </small>
                        </div>
                        <textarea name="message" id="smsTextarea_{{ $modalId }}" rows="4" class="form-control text-dark font-monospace sms-textarea" style="font-size: 13px; line-height: 1.5;" required oninput="updateSmsCharCount(this, 'smsCounter_{{ $modalId }}')">{{ $welcomeApproveSms }}</textarea>
                    </div>

                    <div class="alert alert-info border-0 py-2 px-3 small rounded-3 mb-0 d-flex align-items-center gap-2" style="background-color: #ecfdf5; color: #065f46;">
                        <i class="fa-solid fa-circle-info fs-5 flex-shrink-0"></i>
                        <div>
                            এই এসএমএসটি বাল্কএসএমএসবিডি গেটওয়ের মাধ্যমে তাৎক্ষণিক প্রেরিত হবে।
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2.5 px-4 bg-white border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 btn-send-sms">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>এসএমএস পাঠান</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
