{{-- ══ SECTION: জনপ্রিয় প্রকাশনীসমূহ (POPULAR PUBLISHERS DYNAMIC SLIDER) ═══════════════ --}}
@php
    $sidebarPublishers = $sidebarPublishers ?? collect();
@endphp
@if($sidebarPublishers->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="idea-publishers-section">
            
            {{-- Header with Dynamic Controls --}}
            <div class="idea-publishers-header">
                <div>
                    <h3 class="idea-publishers-title">
                        জনপ্রিয় প্রকাশনীসমূহ
                    </h3>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Slider Prev / Next Arrows --}}
                    <button type="button" 
                            class="publisher-nav-btn" 
                            id="pubSliderPrevBtn" 
                            onclick="scrollPublisherSlider(-1)" 
                            aria-label="পূর্ববর্তী প্রকাশক">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" 
                            class="publisher-nav-btn" 
                            id="pubSliderNextBtn" 
                            onclick="scrollPublisherSlider(1)" 
                            aria-label="পরবর্তী প্রকাশক">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>

                    {{-- All Publishers Link --}}
                    <a href="{{ route('publishers.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-bold ms-1" style="font-size: 0.82rem;">
                        সকল প্রকাশক <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Dynamic Slider Track --}}
            <div class="publisher-slider-wrapper">
                <div class="publisher-slider-track" id="publisherSliderTrack">
                    @php
                        $fallbackGradients = [
                            'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                            'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)',
                            'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)',
                            'linear-gradient(135deg, #059669 0%, #047857 100%)',
                            'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)',
                            'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                            'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                            'linear-gradient(135deg, #334155 0%, #1e293b 100%)',
                        ];
                    @endphp

                    @foreach($sidebarPublishers as $idx => $pub)
                        @php
                            $initials = !empty($pub->initials) ? $pub->initials : mb_substr(trim($pub->name), 0, 2);
                            $bgGradient = !empty($pub->logo_bg_color) ? $pub->logo_bg_color : $fallbackGradients[$idx % count($fallbackGradients)];
                        @endphp
                        <a href="{{ route('publishers.show', $pub->slug ?? $pub->id) }}" class="publisher-slide-card" title="{{ $pub->name }}">
                            <div class="publisher-monogram-circle" style="background: {{ $bgGradient }};">
                                {{ $initials }}
                            </div>
                            <div class="publisher-card-name">
                                {{ $pub->name }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>
@endif
