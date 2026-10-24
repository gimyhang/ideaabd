{{-- Scholarship application: edit (admin). English, one-word labels. Posts to the same update route as every other event. --}}
@php $d = $reg->form_data ?? []; @endphp
<div class="modal fade" id="editRegModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form action="{{ route('admin.event-campaigns.registrations.update', $reg->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header py-3 bg-light border-bottom">
                    <div>
                        <h6 class="modal-title fw-bold mb-0">Edit</h6>
                        <small class="text-muted font-monospace">{{ $reg->registration_number }} &bull; {{ $reg->name }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold mb-1" for="schEditName{{ $reg->id }}">Name</label>
                            <input id="schEditName{{ $reg->id }}" type="text" name="name" class="form-control form-control-sm" value="{{ $reg->name }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Mobile</label>
                            <input type="text" name="phone" class="form-control form-control-sm font-monospace" value="{{ $reg->phone }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ $reg->email }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold mb-1">NID</label>
                            <input type="text" name="form_data[national_id]" class="form-control form-control-sm font-monospace" value="{{ $d['national_id'] ?? '' }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Guardian</label>
                            <input type="text" name="form_data[guardian_phone]" class="form-control form-control-sm font-monospace" value="{{ $d['guardian_phone'] ?? '' }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Institute</label>
                            <input type="text" name="institution_or_org" class="form-control form-control-sm" value="{{ $reg->institution_or_org ?: ($d['college_name'] ?? '') }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm fw-bold">
                                @foreach(['confirmed' => 'Confirmed', 'selected' => 'Selected', 'pending' => 'Pending', 'rejected' => 'Rejected'] as $v => $l)
                                    <option value="{{ $v }}" @selected($reg->status === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold mb-1">Notes</label>
                            <textarea name="admin_notes" rows="2" class="form-control form-control-sm">{{ $reg->admin_notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 btn-delete-reg" data-id="{{ $reg->id }}" data-name="{{ $reg->name }}" data-url="{{ route('admin.event-campaigns.registrations.destroy', $reg->id) }}">Delete</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
