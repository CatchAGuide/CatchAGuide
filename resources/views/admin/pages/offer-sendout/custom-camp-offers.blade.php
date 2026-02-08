@extends('admin.layouts.app')

@section('title', 'Custom Camp Offers')

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="page-title mb-1">@yield('title')</h1>
                <p class="text-muted small mb-0">Manage sent offers, follow up, and accept plan requests</p>
            </div>
            <a href="{{ route('admin.offer-sendout.create') }}" class="btn btn-primary">
                <i class="fe fe-plus"></i> Create New Offer
            </a>
            <ol class="breadcrumb mb-0 w-100">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Custom Camp Offers</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- Row -->
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom" id="custom-camp-offers-datatable">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0">Name</th>
                                        <th class="border-bottom-0">Status</th>
                                        <th class="border-bottom-0">Recipient</th>
                                        <th class="border-bottom-0">Details</th>
                                        <th class="border-bottom-0">Offer</th>
                                        <th class="border-bottom-0">Date Range</th>
                                        <th class="border-bottom-0">Sent At</th>
                                        <th class="border-bottom-0">Created By</th>
                                        <th class="border-bottom-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customCampOffers as $offer)
                                        <tr data-id="{{ $offer->id }}" data-recipient-email="{{ $offer->recipient_email }}">
                                            <td><strong>{{ $offer->name }}</strong></td>
                                            <td>
                                                @php
                                                    $statusBadge = match($offer->status ?? 'sent') {
                                                        'accepted' => 'bg-success',
                                                        'rejected' => 'bg-danger',
                                                        'follow_up' => 'bg-warning text-dark',
                                                        'pending' => 'bg-info',
                                                        default => 'bg-secondary',
                                                    };
                                                    $statusLabel = ucfirst(str_replace('_', ' ', $offer->status ?? 'sent'));
                                                @endphp
                                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $offer->recipient_email }}</small>
                                                @if($offer->recipient_phone)
                                                    <br><small class="text-muted">{{ $offer->recipient_phone }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $ro = $offer->resolvedOffers;
                                                    $details = [];
                                                    foreach ($ro as $r) {
                                                        $p = $r['price'] ?? null;
                                                        $n = $r['number_of_persons'] ?? null;
                                                        $t = null;
                                                        if (is_numeric($p) && is_numeric($n) && $n > 0) $t = (float)$p * (int)$n;
                                                        $details[] = ($p ?: '-') . ' | ' . ($n ?: '-') . ($t !== null ? ' | ' . number_format($t, 0) : '');
                                                    }
                                                @endphp
                                                <small>{{ count($details) > 0 ? implode('<br>', $details) : '-' }}</small>
                                            </td>
                                            <td>
                                                <ul class="list-unstyled mb-0 small">
                                                    @forelse($offer->resolvedOffers as $idx => $ro)
                                                        @if($idx > 0)<li class="border-top pt-1 mt-1"></li>@endif
                                                        @if($ro['camp'])
                                                            <li><span class="badge bg-primary">{{ $ro['camp']->title }}</span></li>
                                                        @endif
                                                        @foreach($ro['accommodations'] as $acc)
                                                            <li><span class="badge bg-secondary">{{ $acc->title }}</span></li>
                                                        @endforeach
                                                        @foreach($ro['boats'] as $boat)
                                                            <li><span class="badge bg-info">{{ $boat->title }}</span></li>
                                                        @endforeach
                                                        @foreach($ro['guidings'] as $guiding)
                                                            <li><span class="badge bg-success">{{ $guiding->title }}</span></li>
                                                        @endforeach
                                                        @if($ro['date_from'] || $ro['date_to'])
                                                            <li class="mt-1">
                                                                <span class="text-muted">
                                                                    {{ $ro['date_from'] ? \Carbon\Carbon::parse($ro['date_from'])->format('d.m.Y') : ($ro['date_to'] ? \Carbon\Carbon::parse($ro['date_to'])->format('d.m.Y') : '-') }}
                                                                    @if($ro['date_from'] && $ro['date_to'])
                                                                        – {{ \Carbon\Carbon::parse($ro['date_to'])->format('d.m.Y') }}
                                                                    @endif
                                                                </span>
                                                            </li>
                                                        @endif
                                                    @empty
                                                        <li class="text-muted">-</li>
                                                    @endforelse
                                                </ul>
                                            </td>
                                            <td>
                                                @php
                                                    $ro = $offer->resolvedOffers;
                                                    $ranges = [];
                                                    foreach ($ro as $r) {
                                                        $df = $r['date_from'] ?? null;
                                                        $dt = $r['date_to'] ?? null;
                                                        if ($df || $dt) {
                                                            $ranges[] = ($df ? \Carbon\Carbon::parse($df)->format('d.m.Y') : '-') . ($df && $dt ? ' – ' . \Carbon\Carbon::parse($dt)->format('d.m.Y') : '');
                                                        }
                                                    }
                                                @endphp
                                                <small>{{ count($ranges) > 0 ? implode('<br>', $ranges) : '-' }}</small>
                                            </td>
                                            <td><small>{{ $offer->sent_at ? $offer->sent_at->format('d.m.Y H:i') : '-' }}</small></td>
                                            <td><small>{{ $offer->creator ? $offer->creator->firstname . ' ' . $offer->creator->lastname : '-' }}</small></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" onclick="openFollowUpConfirm({{ $offer->id }})" title="Send follow-up email (CC CEO)"><i class="fe fe-bell"></i></button>
                                                    @if(($offer->status ?? 'sent') !== 'accepted')
                                                        <button type="button" class="btn btn-success" onclick="updateOfferStatus({{ $offer->id }}, 'accepted')" title="Approve"><i class="fe fe-check"></i></button>
                                                    @endif
                                                    @if(($offer->status ?? 'sent') !== 'rejected')
                                                        <button type="button" class="btn btn-danger" onclick="updateOfferStatus({{ $offer->id }}, 'rejected')" title="Cancel"><i class="fe fe-x"></i></button>
                                                    @endif
                                                    <button type="button" class="btn btn-info" onclick="viewOfferDetails({{ $offer->id }})" title="View"><i class="fe fe-eye"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <p class="text-muted mb-0">No custom camp offers found.</p>
                                                <a href="{{ route('admin.offer-sendout.create') }}" class="btn btn-sm btn-primary mt-2">
                                                    <i class="fe fe-plus me-1"></i>Create Your First Custom Camp Offer
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->

    </div>
</div>

<div id="offer-followup-toast" class="toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1060;">
    <div class="d-flex">
        <div class="toast-body" id="offer-followup-toast-body">Follow-up email sent.</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

{{-- Follow-up email confirmation modal --}}
<div class="modal fade" id="followUpConfirmModal" tabindex="-1" aria-labelledby="followUpConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="followUpConfirmLabel">
                    <i class="fe fe-bell me-2"></i>Send follow-up email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Send follow-up email to <strong id="followUpConfirmEmail"></strong>?</p>
                <p class="text-muted small mb-0 mt-2">A copy will be sent to the CEO (CC).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="followUpConfirmBtn">
                    <i class="fe fe-bell me-2"></i>Send follow-up email
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Offer Details Modal -->
<div class="modal fade" id="offerDetailsModal" tabindex="-1" role="dialog" aria-labelledby="offerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offerDetailsModalLabel">Custom Camp Offer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="offerDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewOfferDetails(offerId) {
    const modal = new bootstrap.Modal(document.getElementById('offerDetailsModal'));
    const content = document.getElementById('offerDetailsContent');
    
    // Show loading
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();
    
    // Fetch offer details
    fetch(`/admin/offer-sendout/custom-camp-offers/${offerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="offer-details">';
                
                // Basic Info
                html += '<div class="mb-4"><h5>Basic Information</h5>';
                html += '<table class="table table-sm table-bordered">';
                html += `<tr><th class="w-25">Name:</th><td>${data.offer.name || 'N/A'}</td></tr>`;
                html += `<tr><th>Recipient:</th><td>${data.offer.recipient_name || 'N/A'} (${data.offer.recipient_email})</td></tr>`;
                if (data.offer.recipient_phone) {
                    html += `<tr><th>Phone:</th><td>${data.offer.recipient_phone}</td></tr>`;
                }
                html += `<tr><th>Type:</th><td><span class="badge ${data.offer.recipient_type === 'customer' ? 'bg-info' : 'bg-secondary'}">${data.offer.recipient_type === 'customer' ? 'Customer' : 'Manual Entry'}</span></td></tr>`;
                html += `<tr><th>Status:</th><td><span class="badge">${(data.offer.status || 'sent').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}</span></td></tr>`;
                html += `<tr><th>Sent At:</th><td>${data.offer.sent_at || 'N/A'}</td></tr>`;
                html += '</table></div>';
                
                // Camp Info
                if (data.offer.camp) {
                    html += '<div class="mb-4"><h5>Camp</h5>';
                    html += `<p><strong>${data.offer.camp.title}</strong></p>`;
                    html += '</div>';
                }
                
                // Dates & Pricing
                html += '<div class="mb-4"><h5>Dates & Pricing</h5>';
                html += '<table class="table table-sm table-bordered">';
                if (data.offer.date_from) {
                    html += `<tr><th class="w-25">Date From:</th><td>${data.offer.date_from}</td></tr>`;
                }
                if (data.offer.date_to) {
                    html += `<tr><th>Date To:</th><td>${data.offer.date_to}</td></tr>`;
                }
                if (data.offer.number_of_persons) {
                    html += `<tr><th>Number of Persons:</th><td>${data.offer.number_of_persons}</td></tr>`;
                }
                if (data.offer.price) {
                    html += `<tr><th>Price:</th><td>${data.offer.price}</td></tr>`;
                }
                html += '</table></div>';
                
                // Offers (from resolved_offers - each offer has camp, accommodations, boats, guidings)
                const resolvedOffers = data.offer.resolved_offers || [];
                if (resolvedOffers.length > 0) {
                    html += '<div class="mb-4"><h5>Offers</h5>';
                    resolvedOffers.forEach((ro, idx) => {
                        html += `<div class="mb-3 p-2 border rounded"><strong>Offer ${idx + 1}</strong>`;
                        if (ro.camp) html += `<p class="mb-1"><span class="badge bg-primary">${ro.camp.title}</span></p>`;
                        if (ro.accommodations && ro.accommodations.length > 0) {
                            html += '<p class="mb-1"><strong>Accommodations:</strong> ';
                            html += ro.accommodations.map(a => `<span class="badge bg-secondary">${a.title}</span>`).join(' ') + '</p>';
                        }
                        if (ro.boats && ro.boats.length > 0) {
                            html += '<p class="mb-1"><strong>Boats:</strong> ';
                            html += ro.boats.map(b => `<span class="badge bg-info">${b.title}</span>`).join(' ') + '</p>';
                        }
                        if (ro.guidings && ro.guidings.length > 0) {
                            html += '<p class="mb-1"><strong>Guidings:</strong> ';
                            html += ro.guidings.map(g => `<span class="badge bg-success">${g.title}</span>`).join(' ') + '</p>';
                        }
                        if (ro.date_from || ro.date_to) {
                            html += `<p class="mb-1"><strong>Dates:</strong> ${ro.date_from || '-'} – ${ro.date_to || '-'}</p>`;
                        }
                        if (ro.price || ro.number_of_persons) {
                            html += `<p class="mb-1"><strong>Price:</strong> ${ro.price || '-'} | <strong>Persons:</strong> ${ro.number_of_persons || '-'}</p>`;
                        }
                        if (ro.additional_info) html += `<p class="mb-1"><strong>Additional:</strong> ${ro.additional_info}</p>`;
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                // Additional Info (top-level)
                if (data.offer.additional_info) {
                    html += '<div class="mb-4"><h5>Additional Information</h5>';
                    html += `<p>${data.offer.additional_info}</p></div>`;
                }
                
                // Free Text
                if (data.offer.free_text) {
                    html += '<div class="mb-4"><h5>Free Text Message</h5>';
                    html += `<p>${data.offer.free_text}</p></div>`;
                }
                
                html += '</div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<div class="alert alert-danger">Failed to load offer details.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Error loading offer details.</div>';
        });
}

function openFollowUpConfirm(offerId) {
    const row = document.querySelector('tr[data-id="' + offerId + '"]');
    const email = row ? (row.getAttribute('data-recipient-email') || '') : '';
    document.getElementById('followUpConfirmEmail').textContent = email || '(no email)';
    const modal = document.getElementById('followUpConfirmModal');
    const confirmBtn = document.getElementById('followUpConfirmBtn');
    confirmBtn.dataset.offerId = offerId;
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}

document.getElementById('followUpConfirmBtn').addEventListener('click', function() {
    const offerId = this.dataset.offerId;
    if (!offerId) return;
    sendFollowUpEmail(offerId);
    bootstrap.Modal.getInstance(document.getElementById('followUpConfirmModal'))?.hide();
});

function sendFollowUpEmail(offerId) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrf) return;
    const confirmBtn = document.getElementById('followUpConfirmBtn');
    const originalHtml = confirmBtn ? confirmBtn.innerHTML : '';
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
    }
    fetch('{{ route("admin.offer-sendout.custom-camp-offers.follow-up", ["customCampOffer" => "__ID__"]) }}'.replace('__ID__', offerId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        const toastEl = document.getElementById('offer-followup-toast');
        const bodyEl = document.getElementById('offer-followup-toast-body');
        if (toastEl && bodyEl) {
            bodyEl.textContent = data.success ? (data.message || 'Follow-up email sent.') : (data.message || 'Failed to send.');
            toastEl.classList.remove('text-bg-success', 'text-bg-danger');
            toastEl.classList.add(data.success ? 'text-bg-success' : 'text-bg-danger');
            new bootstrap.Toast(toastEl).show();
        }
    })
    .catch(err => {
        const toastEl = document.getElementById('offer-followup-toast');
        const bodyEl = document.getElementById('offer-followup-toast-body');
        if (toastEl && bodyEl) {
            bodyEl.textContent = 'Error sending follow-up email.';
            toastEl.classList.remove('text-bg-success', 'text-bg-danger');
            toastEl.classList.add('text-bg-danger');
            new bootstrap.Toast(toastEl).show();
        }
    })
    .finally(() => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalHtml || '<i class="fe fe-bell me-2"></i>Send follow-up email';
        }
    });
}

function updateOfferStatus(offerId, status) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrf) return;
    fetch(`/admin/offer-sendout/custom-camp-offers/${offerId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-id="${offerId}"]`);
            if (row) {
                const badge = row.querySelector('.badge.bg-success, .badge.bg-danger, .badge.bg-warning, .badge.bg-info, .badge.bg-secondary');
                if (badge) {
                    const map = { accepted: ['bg-success','Accepted'], follow_up: ['bg-warning text-dark','Follow up'], rejected: ['bg-danger','Rejected'] };
                    const [cls, lbl] = map[status] || ['bg-secondary', status];
                    badge.className = 'badge ' + cls;
                    badge.textContent = lbl;
                }
            }
        }
    })
    .catch(console.error);
}

$(document).ready(function() {
    var table = $('#custom-camp-offers-datatable');
    if (table.find('tbody tr').length > 0 && !table.find('tbody tr').first().find('td[colspan]').length) {
        table.DataTable({
            order: [[6, 'desc']],
            pageLength: 25,
            responsive: true
        });
    }
});
</script>
@endsection
