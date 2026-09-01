@extends('layouts.admin')

@section('title', 'Ticket #' . $ticket->ticket_number)
@section('heading', 'Support Ticket #' . $ticket->ticket_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">Support Tickets</a></li>
    <li class="breadcrumb-item active">#{{ $ticket->ticket_number }}</li>
@endsection

@section('content')
<div class="row g-4">

    <!-- Left Column: Messages Thread & Reply Form -->
    <div class="col-12 col-xl-8">
        <div class="adm-card bg-white mb-4">
            <div class="adm-card__head d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $ticket->subject }}</h5>
                    <small class="text-muted">Department: <strong class="text-uppercase">{{ $ticket->department }}</strong> | Opened {{ $ticket->created_at->diffForHumans() }}</small>
                </div>
                <span class="pill {{ $ticket->status === 'open' ? 'pill--pending' : ($ticket->status === 'resolved' ? 'pill--ok' : 'pill--info') }}">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
            </div>
            
            <div class="adm-card__body p-4 d-flex flex-column gap-3">
                @forelse($ticket->messages as $msg)
                    <div class="p-3.5 rounded-4 border {{ $msg->is_admin_reply ? 'bg-primary-subtle border-primary-subtle ms-4' : 'bg-light me-4' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small {{ $msg->is_admin_reply ? 'text-primary' : 'text-dark' }}">
                                <i class="fas {{ $msg->is_admin_reply ? 'fa-user-shield me-1' : 'fa-user me-1' }}"></i>
                                {{ $msg->user->name ?? ($msg->is_admin_reply ? 'Support Staff' : $ticket->customer_name) }}
                            </span>
                            <small class="text-muted">{{ $msg->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                        <div class="text-dark" style="white-space: pre-wrap;">{{ $msg->message }}</div>
                    </div>
                @empty
                    <div class="text-muted small">No message thread history.</div>
                @endforelse
            </div>
        </div>

        <!-- Reply Form -->
        <div class="adm-card bg-white">
            <div class="adm-card__head">
                <h6 class="mb-0 fw-bold"><i class="fas fa-reply me-1.5 text-primary"></i> Send Reply to Customer</h6>
            </div>
            <div class="adm-card__body p-4">
                <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Your Response</label>
                        <textarea name="message" rows="4" class="form-control" placeholder="Type official response to customer..." required></textarea>
                    </div>
                    <div class="row g-2 align-items-center justify-content-between">
                        <div class="col-12 col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <label class="small fw-semibold text-muted text-nowrap">Update Status:</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fas fa-paper-plane me-1"></i> Send Reply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Customer 360 Information -->
    <div class="col-12 col-xl-4">
        <div class="adm-card bg-white">
            <div class="adm-card__head">
                <h6 class="mb-0 fw-bold"><i class="fas fa-address-card me-1.5 text-primary"></i> Customer Information</h6>
            </div>
            <div class="adm-card__body p-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 small">
                    <li>
                        <span class="text-muted d-block">Name:</span>
                        <strong class="text-dark fs-6">{{ $ticket->customer_name }}</strong>
                    </li>
                    <li>
                        <span class="text-muted d-block">Email:</span>
                        <a href="mailto:{{ $ticket->customer_email }}" class="text-decoration-none fw-semibold">{{ $ticket->customer_email }}</a>
                    </li>
                    <li>
                        <span class="text-muted d-block">Phone:</span>
                        <span class="fw-semibold text-dark">{{ $ticket->customer_phone ?? '—' }}</span>
                    </li>
                    <li class="pt-2 border-top">
                        <span class="text-muted d-block">Priority:</span>
                        <span class="badge {{ $ticket->priority === 'urgent' ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
