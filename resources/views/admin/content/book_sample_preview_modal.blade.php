{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- SAMPLE LOOK INSIDE READER PREVIEW MODAL                                    --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="sampleLookInsidePreviewModal" tabindex="-1" aria-labelledby="sampleLookInsidePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- Modal Header --}}
            <div class="modal-header bg-dark text-white px-4 py-3 border-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-25 text-info p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-book-open-reader fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="sampleLookInsidePreviewModalLabel">
                            Sample Preview
                        </h5>
                        <p class="text-white-50 small mb-0" id="sampleModalSubTitle" style="font-size: 11.5px;">
                            Live Reader Preview
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary px-2.5 py-1 text-uppercase font-monospace" id="sampleModalFormatBadge" style="font-size: 11px;">PDF</span>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-0 bg-secondary bg-opacity-10 d-flex flex-column align-items-center justify-content-center" style="min-height: 72vh;">
                {{-- PDF Viewer Container --}}
                <div id="samplePdfViewerContainer" class="w-100 h-100 d-none" style="min-height: 72vh;">
                    <iframe id="samplePdfIframe" src="" class="w-100 border-0" style="height: 72vh; display: block;" title="PDF Preview"></iframe>
                </div>

                {{-- Multi-Images Viewer Container --}}
                <div id="sampleImagesViewerContainer" class="w-100 p-4 d-none overflow-y-auto" style="max-height: 72vh;">
                    <div class="container" style="max-width: 820px;">
                        <div id="sampleImagesCarouselList" class="d-flex flex-column gap-3 align-items-center">
                            {{-- Dynamically injected image pages --}}
                        </div>
                    </div>
                </div>

                {{-- Empty State Placeholder --}}
                <div id="sampleEmptyStateContainer" class="text-center p-5">
                    <div class="rounded-circle bg-white text-muted p-3.5 d-inline-flex align-items-center justify-content-center shadow-xs mb-3" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-file-pdf fs-2 text-info opacity-75"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No Sample File Available</h6>
                    <p class="text-muted small mb-3" style="max-width: 380px;">
                        Please upload a sample PDF or look inside page images to preview.
                    </p>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold" data-bs-dismiss="modal">
                        Back to Form
                    </button>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer bg-white px-4 py-2.5 border-top d-flex align-items-center justify-content-between">
                <div class="text-muted small" style="font-size: 11.5px;">
                    <i class="fa-solid fa-circle-check text-success me-1"></i>
                    <span>Live reader preview for mobile and desktop</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>